<?php

namespace Illuminate\Http;

class Request {
    public static function capture() { return new static; }
    public function ip() { return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'; }
    public function validate($rules) { return $this->all(); } // Mock validation
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

namespace Illuminate\Routing;

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
        $_SESSION[$key] = $value;
        return $this;
    }

    public function __destruct() {
        if ($this->targetUrl && !headers_sent()) {
            header("Location: " . $this->targetUrl);
            exit;
        }
    }
}

namespace Illuminate\Support\Facades;

class Route {
    public static function get($uri, $action) { self::register('GET', $uri, $action); return new static; }
    public static function post($uri, $action) { self::register('POST', $uri, $action); return new static; }
    public static function prefix($p) { return new static; }
    public static function group($cb) { $cb(); }
    public static function middleware($m) { return new static; }
    public function name($n) { return $this; }

    public static function register($method, $uri, $action) {
        // Very basic router
        $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $currentMethod = $_SERVER['REQUEST_METHOD'];
        
        // Match /polls/{id}/vote pattern vs /polls/1/vote
        $pattern = preg_replace('/\{[a-z]+\}/', '([0-9]+)', $uri);
        $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#';
        
        // Remove trailing slash from current URI if not root
        if(strlen($currentUri) > 1) $currentUri = rtrim($currentUri, '/');

        if ($currentMethod === $method && preg_match($pattern, $currentUri, $matches)) {
            array_shift($matches); // remove full match
            
            if (is_array($action)) {
                $controller = new $action[0];
                $method = $action[1];
                $response = call_user_func_array([$controller, $method], array_merge([new \Illuminate\Http\Request], $matches));
                if(is_string($response)) echo $response;
                exit; // Stop after first match
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

class Schema {
    public static function create($table, $cb) { echo "Schema Create: $table<br>"; }
    public static function dropIfExists($table) { echo "Schema Drop: $table<br>"; }
}

namespace Illuminate\Database\Eloquent;

class Model {
    protected $fillable = [];
    public static function create($data) { 
        return (new static)->saveData($data); 
    }
    public static function where($col, $val) { return new Builder(static::class, $col, $val); }
    public static function with($rel) { return new Builder(static::class); }
    public static function latest() { return new Builder(static::class); }
    public static function findOrFail($id) { return (new Builder(static::class))->find($id); }

    public function saveData($data) {
        $db = \DB::pdo();
        // Assuming table name = plural of class
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
    protected $model;
    protected $where = [];
    
    public function __construct($model, $col=null, $val=null) { 
        $this->model = $model;
        if($col) $this->where[$col] = $val;
    }
    public function where($col, $val) { $this->where[$col] = $val; return $this; }
    public function latest() { return $this; }
    public function get() { 
        // Simple select * from table where ...
        $table = strtolower(basename(str_replace('\\', '/', $this->model))) . 's';
        if(property_exists($this->model, 'table')) {
             $c = new $this->model;
             // Reflection or public access to protected property is tricky in mock.
             // Assume convention for now.
             if(basename(str_replace('\\', '/', $this->model)) == 'PollOption') $table = 'poll_options';
        }

        $sql = "SELECT * FROM $table";
        $params = [];
        if(!empty($this->where)) {
            $clauses = [];
            foreach($this->where as $k=>$v) { $clauses[] = "$k = ?"; $params[] = $v; }
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }
        
        $db = \DB::pdo();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->model);
        return new \Illuminate\Support\Collection($rows);
    }
    public function first() {
        $res = $this->get();
        return $res[0] ?? null;
    }
    public function find($id) {
         $this->where['id'] = $id;
         return $this->first();
    }
    public function withCount($ rel) { return $this; } // Ignored in mock
}

class Factory {} // Stub

namespace Illuminate\Support;
class Collection extends \ArrayObject {
    public function map($cb) { return array_map($cb, (array)$this); }
}

namespace Illuminate\Database\Schema;
class Blueprint {
    public function id() {}
    public function string() {}
    public function boolean() {}
    public function timestamps() {}
}

namespace Illuminate\Database\Migrations;
class Migration {}

// Global DB Facade for Mock
class DB {
    public static $pdo;
    public static function pdo() {
        if(!self::$pdo) {
             require __DIR__ . '/../../config/database.php'; // get config
             // Simplified
             $host = $_ENV['DB_HOST'] ?? 'localhost';
             $db = $_ENV['DB_DATABASE'] ?? 'test';
             $user = $_ENV['DB_USERNAME'] ?? 'root';
             $pass = $_ENV['DB_PASSWORD'] ?? '';
             self::$pdo = new \PDO("mysql:host=$host;dbname=$db", $user, $pass);
             self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }
}
class_alias('Illuminate\Support\Facades\DB', 'DB');

namespace App\Http\Controllers;
class Controller {}

namespace Illuminate\Foundation\Auth;
class User {}

