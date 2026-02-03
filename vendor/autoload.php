<?php

// 1. PSR-4 Autoloader for 'App\\'
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $path = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($path)) {
            require $path;
        }
    }
});

// 2. Load Mini-Laravel Runtime (Mocks Illuminate classes)
require_once __DIR__ . '/laravel_runtime.php';

// 3. Load Helpers
if (!function_exists('view')) {
    function view($view, $data = []) {
        extract($data);
        $path = __DIR__ . '/../resources/views/' . str_replace('.', '/', $view) . '.blade.php';
        if (!file_exists($path)) {
            // Fallback for flat structure refactor if view folder structure varies
            $path = __DIR__ . '/../resources/views/' . basename(str_replace('.', '/', $view)) . '.blade.php';
        }
        if (file_exists($path)) {
            ob_start();
            include $path;
            return ob_get_clean();
        }
        return "View [$view] not found.";
    }
}
if (!function_exists('response')) {
    function response() { return new \Illuminate\Http\ResponseFactory(); }
}
if (!function_exists('redirect')) {
    function redirect($to = null) { 
        $r = new \Illuminate\Routing\Redirector(); 
        if($to) return $r->to($to);
        return $r;
    }
}
if (!function_exists('route')) {
    function route($name, $params = []) { 
        // Simple fallback: return URL based on usage in views
        if($name == 'login') return '/login';
        if($name == 'polls.index') return '/polls';
        if($name == 'admin.index') return '/admin';
        return '/#route-'.$name; 
    }
}
if (!function_exists('asset')) {
    function asset($path) { return '/' . ltrim($path, '/'); }
}
if (!function_exists('env')) {
    function env($key, $default = null) {
        // Simple .env parser (Lazy load)
        static $loaded = false;
        static $vars = [];
        
        if (!$loaded) {
            $path = __DIR__ . '/../.env';
            if (file_exists($path)) {
                $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0) continue;
                    if (strpos($line, '=') !== false) {
                        list($k, $v) = explode('=', $line, 2);
                        $k = trim($k);
                        $v = trim($v);
                        // Remove quotes if present
                        if(substr($v, 0, 1) === '"' && substr($v, -1) === '"') $v = substr($v, 1, -1);
                        $vars[$k] = $v;
                        $_ENV[$k] = $v;
                        $_SERVER[$k] = $v;
                    }
                }
            }
            $loaded = true;
        }
        
        $value = $vars[$key] ?? $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        
        if ($value === false) return $default;
        if ($value === null) return $default;
        
        // Handle booleans
        if (strtolower($value) === 'true') return true;
        if (strtolower($value) === 'false') return false;
        
        return $value;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() { return 'mock-csrf-token'; }
}
if (!function_exists('csrf_field')) {
    function csrf_field() { return '<input type="hidden" name="_token" value="mock-csrf-token">'; }
}
