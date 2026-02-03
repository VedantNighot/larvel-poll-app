<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Live Poll Platform</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .poll-card { cursor: pointer; transition: transform 0.2s; }
        .poll-card:hover { transform: translateY(-5px); border-color: #0d6efd; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">LivePoll</a>
            <div class="d-flex">
                @auth
                    @if(Auth::id() == 1)
                        <a href="{{ route('admin.polls.create') }}" class="btn btn-outline-light btn-sm me-2">Create Poll</a>
                    @endif
                    <span class="text-white align-self-center">User: {{ Auth::id() }}</span>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Login</a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="container">
        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
