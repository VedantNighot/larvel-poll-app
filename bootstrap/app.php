<?php

// Start Session safe check
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Safety: Ensure Runtime is loaded even if Autoloader missed it
if (!class_exists('Illuminate\Support\Facades\Route')) {
    $runtimePath = __DIR__ . '/../vendor/laravel_runtime.php';
    if (file_exists($runtimePath)) {
        require_once $runtimePath;
    }
}

// Load Routes
require_once __DIR__ . '/../routes/web.php';


// Return a mock app object that index.php expects
class MockApp {
    public function make($class) { return new MockKernel; }
}
class MockKernel {
    public function handle($req) { 
        // 1. Capture URI and Method
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // 2. Simple Dispatcher (since Route::register handles execution, we need to invoke it)
        // Actually, Route definitions in web.php execute immediately upon require.
        // But Controller methods often exit() in our mock. 
        // If we reached here, it means no route matched and exited?
        // OR, Route definitions just registered themselves?
        
        // In our current runtime (step 426), Route::get/post calls self::register.
        // self::register checks match and EXITS if matched.
        // So if we are here, NO ROUTE MATCHED.
        
        // Return 404 response
        http_response_code(404);
        echo "404 Not Found";
        exit;
        
        return new \Illuminate\Http\ResponseFactory; 
    }
    public function terminate($req, $res) {}
}

return new MockApp;
