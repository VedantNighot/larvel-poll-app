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
if (!function_exists('csrf_token')) {
    function csrf_token() { return 'mock-csrf-token'; }
}
if (!function_exists('csrf_field')) {
    function csrf_field() { return '<input type="hidden" name="_token" value="mock-csrf-token">'; }
}
