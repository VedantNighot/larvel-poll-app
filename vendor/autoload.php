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
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('view')) {
    function view($view, $data = []) {
        // 1. Resolve Path
        $path = __DIR__ . '/../resources/views/' . str_replace('.', '/', $view) . '.blade.php';
        if (!file_exists($path)) {
             $path = __DIR__ . '/../resources/views/' . basename(str_replace('.', '/', $view)) . '.blade.php';
             if (!file_exists($path)) return "View [$view] not found.";
        }

        // 2. Read Content
        $content = file_get_contents($path);

        // 3. Handle Layouts (@extends)
        // Simple regex to find @extends('layout')
        if (preg_match('/@extends\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
            $layoutName = $matches[1];
            $layoutPath = __DIR__ . '/../resources/views/' . str_replace('.', '/', $layoutName) . '.blade.php';
            
            if (file_exists($layoutPath)) {
                $layoutContent = file_get_contents($layoutPath);
                
                // Extract Sections from Child
                preg_match_all('/@section\([\'"]([^\'"]+)[\'"]\)(.*?)@endsection/s', $content, $sectionMatches, PREG_SET_ORDER);
                
                $sections = [];
                foreach ($sectionMatches as $sm) {
                    $sections[$sm[1]] = $sm[2];
                }
                
                // Replace @yield in Layout
                $content = preg_replace_callback('/@yield\([\'"]([^\'"]+)[\'"]\)/', function($m) use ($sections) {
                    return $sections[$m[1]] ?? '';
                }, $layoutContent);
            }
        }

        // 4. Compile Blade Syntax
        // Note: Using greedy ((.+)) for parenthesized expressions to handle simple nested calls like count($x)
        // This assumes one directive per line or distinct separation, which is typical.
        $replacements = [
            '/\{\{\s*(.+?)\s*\}\}/' => '<?= e($1) ?>',           
            '/@if\s*\((.+)\)/' => '<?php if($1): ?>',             
            '/@elseif\s*\((.+)\)/' => '<?php elseif($1): ?>',    
            '/@else/' => '<?php else: ?>',                        
            '/@endif/' => '<?php endif; ?>',                      
            '/@foreach\s*\((.+)\)/' => '<?php foreach($1): ?>',  
            '/@endforeach/' => '<?php endforeach; ?>',            
            '/@auth/' => '<?php if(\Illuminate\Support\Facades\Auth::check()): ?>',
            '/@endauth/' => '<?php endif; ?>',
            '/@guest/' => '<?php if(!\Illuminate\Support\Facades\Auth::check()): ?>',
            '/@endguest/' => '<?php endif; ?>',
             // Minimal error directive support assuming $errors might not exist, checking session
            '/@error\s*\([\'"](.+?)[\'"]\)/' => '<?php if(session("errors") && session("errors")->has("$1")): ?>', 
            '/@enderror/' => '<?php endif; ?>',
            '/@csrf/' => '<?= csrf_field() ?>',                   
            '/@php/' => '<?php',                                  
            '/@endphp/' => '?>',                                  
        ];

        $content = preg_replace(array_keys($replacements), array_values($replacements), $content);

        // 5. Render
        extract($data);
        ob_start();
        eval('?>' . $content);
        return ob_get_clean();
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
        if($name == 'admin.polls.store') return '/admin/polls';
        // Fallback or explicit separate route
        if($name == 'admin.polls.create') return '/admin'; 
        
        // Handle parameters for dynamic routes
        if($name == 'admin.votes.show') {
            $id = $params[0] ?? $params['id'] ?? 1;
            return "/admin/polls/$id/votes";
        }
        if($name == 'admin.polls.toggle') {
            $id = $params[0] ?? $params['id'] ?? 1;
            return "/admin/polls/$id/toggle";
        }
        if($name == 'admin.polls.delete') {
            $id = $params[0] ?? $params['id'] ?? 1;
            return "/admin/polls/$id/delete";
        }
        
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

if (!function_exists('session')) {
    function session($key = null, $default = null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (is_null($key)) return null;
        
        if (is_array($key)) {
            foreach($key as $k => $v) {
                $_SESSION[$k] = $v;
            }
            return;
        }
        
        $val = $_SESSION[$key] ?? $default;
        // Clear flash messages after read (simple simulation)
        if($key == 'error' || $key == 'success') unset($_SESSION[$key]);
        
        return $val;
    }
}
