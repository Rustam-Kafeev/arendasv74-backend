<!DOCTYPE html>
<html>
<head>
    <title>Админка Arendasv74</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Панель управления</a>
            <div class="d-flex">
                <a href="{{ route('admin.cars.index') }}" class="text-white me-3">Автомобили</a>
                <a href="{{ route('admin.users.index') }}" class="text-white me-3">Пользователи</a>
                <a href="{{ route('admin.conversations.index') }}" class="text-white me-3">Беседы</a>
                <a href="{{ route('admin.messages.index') }}" class="text-white me-3">Сообщения</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-light">Выйти</button>
                </form>
            </div>
        </div>
    </nav>
    <main class="py-4">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @yield('content')
        </div>
    </main>
</body>
</html>