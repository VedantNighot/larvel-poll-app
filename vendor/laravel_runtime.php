<?php

// Runtime Mock for Laravel Components
// Hand-coded for InfinityFree deployment without Composer

namespace {
    // Global Helper Classes if needed
}

namespace Illuminate\Http {
    class Request {
        public static function capture() { return new static; }
        public function ip() { return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'; }
        public function validate($rules) { return $this->all(); }
        public function all() { return $_REQUEST; }
        public function __get($key) { return $_REQUEST[$key] ?? null; }
    }
    class ResponseFactory {
        public function json($data, $status = 200) {
            header('Content-Type: application/json');
            http_response_code($status);
            echo json_encode($data);
            exit;
        }
    }
}

namespace Illuminate\Routing {
    class Redirector {
        protected $targetUrl;
        public function route($name) { 
            $url = '/';
            if(strpos($name, 'index') !== false) $url = '/polls';
            if(strpos($name, 'login') !== false) $url = '/login';
            $this->targetUrl = $url;
            return $this;
        }
        public function to($path) {
            $this->targetUrl = $path;
            return $this;
        }
        public function back() {
            $this->targetUrl = $_SERVER['HTTP_REFERER'] ?? '/login';
            return $this;
        }
        public function with($key, $value) {
            if(function_exists('session')) session([$key => $value]);
            else $_SESSION[$key] = $value;
            return $this;
        }
        public function __destruct() {
            if ($this->targetUrl && !headers_sent()) {
                header("Location: " . $this->targetUrl);
                exit;
            }
        }
    }
    class Controller {} // Base Controller for Illuminate
}

namespace Illuminate\Support\Facades {
    class Route {
        public static function get($uri, $action) { self::register('GET', $uri, $action); return new static; }
        public static function post($uri, $action) { self::register('POST', $uri, $action); return new static; }
        public static function prefix($p) { return new static; }
        public static function group($cb) { $cb(); }
        public static function middleware($m) { return new static; }
        public function name($n) { return $this; }
        public static function register($method, $uri, $action) {
            $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $currentMethod = $_SERVER['REQUEST_METHOD'];
            $pattern = preg_replace('/\{[a-z]+\}/', '([0-9]+)', $uri);
            $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#';
            if(strlen($currentUri) > 1) $currentUri = rtrim($currentUri, '/');

            if ($currentMethod === $method && preg_match($pattern, $currentUri, $matches)) {
                array_shift($matches);
                if (is_array($action)) {
                    $controllerName = $action[0];
                    $method = $action[1];
                    // Autoloader should handle class loading
                    if (!class_exists($controllerName)) {
                        die("Error: Controller class not found: $controllerName");
                    }
                    $controller = new $controllerName;
                    if(!method_exists($controller, $method)) {
                        die("Error: Method $method not found in $controllerName");
                    }
                    $response = call_user_func_array([$controller, $method], array_merge([new \Illuminate\Http\Request], $matches));
                    if(is_string($response)) echo $response;
                    exit;
                }
            }
        }
    }

    class Auth {
        public static function loginUsingId($id) { $_SESSION['user_id'] = $id; }
        public static function logout() { unset($_SESSION['user_id']); }
        public static function id() { return $_SESSION['user_id'] ?? null; }
        public static function check() { return isset($_SESSION['user_id']); }
        public static function user() { return (object)['id' => self::id(), 'name' => 'User']; }
    }

    class DB {
        public static $pdo;
        public static function pdo() {
            if(!self::$pdo) {
                if(!function_exists('env')) {
                     // manual fallback if env helper missing
                     require_once __DIR__ . '/../../config/database.php';
                     $config = include __DIR__ . '/../../config/database.php'; 
                     // This path is tricky in mock, keep simple
                }
                $host = env('DB_HOST', 'localhost');
                $db = env('DB_DATABASE', 'test');
                $user = env('DB_USERNAME', 'root');
                $pass = env('DB_PASSWORD', '');
                try {
                    self::$pdo = new \PDO("mysql:host=$host;dbname=$db", $user, $pass);
                    self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                } catch (\Exception $e) {
                    die("DB Connection Error: " . $e->getMessage());
                }
            }
            return self::$pdo;
        }
    }
}

namespace Illuminate\Database\Eloquent {
    class Model {
        protected $fillable = [];
        public static function create($data) { return (new static)->saveData($data); }
        public static function where($col, $val) { return new Builder(static::class, $col, $val); }
        public static function with($rel) { return new Builder(static::class); }
        public static function latest() { return new Builder(static::class); }
        public static function findOrFail($id) { return (new Builder(static::class))->find($id); }
        public function saveData($data) {
            $db = \Illuminate\Support\Facades\DB::pdo();
            $table = strtolower(basename(str_replace('\\', '/', static::class))) . 's';
            if(isset($this->table)) $table = $this->table;
            $cols = implode(',', array_keys($data));
            $vals = implode(',', array_fill(0, count($data), '?'));
            $stmt = $db->prepare("INSERT INTO $table ($cols) VALUES ($vals)");
            $stmt->execute(array_values($data));
            $id = $db->lastInsertId();
            $obj = new static;
            foreach($data as $k=>$v) $obj->$k = $v;
            $obj->id = $id;
            return $obj;
        }
    }
    class Builder {
        protected $model, $where = [];
        public function __construct($model, $col=null, $val=null) { $this->model = $model; if($col) $this->where[$col] = $val; }
        public function where($col, $val) { $this->where[$col] = $val; return $this; }
        public function latest() { return $this; }
        public function get() { 
            $table = strtolower(basename(str_replace('\\', '/', $this->model))) . 's';
            if(basename(str_replace('\\', '/', $this->model)) == 'PollOption') $table = 'poll_options';
            $sql = "SELECT * FROM $table";
            $params = [];
            if(!empty($this->where)) {
                $clauses = []; foreach($this->where as $k=>$v) { $clauses[] = "$k = ?"; $params[] = $v; }
                $sql .= " WHERE " . implode(' AND ', $clauses);
            }
            $db = \Illuminate\Support\Facades\DB::pdo();
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->model);
            return new \Illuminate\Support\Collection($rows);
        }
        public function first() { $res = $this->get(); return $res[0] ?? null; }
        public function find($id) { $this->where['id'] = $id; return $this->first(); }
        public function withCount($rel) { return $this; }
    }
}

namespace Illuminate\Database\Eloquent\Factories {
    trait HasFactory {
        public static function factory() { return new \Illuminate\Database\Eloquent\Factory; }
    }
    class Factory {}
}

namespace Illuminate\Support {
    class Collection extends \ArrayObject {
        public function map($cb) { return array_map($cb, (array)$this); }
    }
}

namespace Illuminate\Database\Schema {
    class Blueprint {
        public function id() {}
        public function string() {}
        public function boolean() {}
        public function timestamps() {}
    }
}

namespace Illuminate\Database\Migrations {
    class Migration {}
}

namespace Illuminate\Foundation\Auth {
    class User {}
}

// Global Aliases
namespace {
    class_alias('Illuminate\Support\Facades\DB', 'DB');
    class_alias('Illuminate\Support\Facades\Auth', 'Auth');
    class_alias('Illuminate\Support\Facades\Route', 'Route');
    class_alias('Illuminate\Database\Eloquent\Model', 'Eloquent');
    class_alias('Illuminate\Database\Migrations\Migration', 'Migration');
}
