<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Golek Tenant' }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body data-theme="light" class="min-h-screen flex flex-col">
    <div class="navbar bg-base-100 shadow">
        <div class="container mx-auto">
            <div class="flex-1">
                <a href="{{ route('home') }}" class="btn btn-ghost normal-case text-xl">Golek Tenant</a>
            </div>
            <div class="flex-none">
                <ul class="menu menu-horizontal px-1">
                    <li><a href="{{ route('client.events.index') }}">Events</a></li>
                </ul>
            </div>
        </div>
    </div>

    <main class="container mx-auto flex-1 p-4 md:p-6 lg:p-8">
        @yield('content')
    </main>

    <footer class="footer footer-center p-4 bg-base-200 text-base-content">
        <aside>
            <p>© {{ date('Y') }} Golek Tenant. All rights reserved.</p>
        </aside>
    </footer>
</body>
</html>