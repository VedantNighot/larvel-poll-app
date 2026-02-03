<?php

// Start Session for Auth Mock
session_start();

// Load Routes
require_once __DIR__ . '/../routes/web.php';


// Return a mock app object that index.php expects
class MockApp {
    public function make($class) { return new MockKernel; }
}
class MockKernel {
    public function handle($req) { return new \Illuminate\Http\ResponseFactory; }
    public function terminate($req, $res) {}
}

return new MockApp;
