<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Модерация — INZOJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-5xl mx-auto py-8 px-4">
        <div class="flex justify-between items-center mb-6 gap-4">
            <a href="{{ route('moderator.queue') }}" class="text-2xl font-extrabold">Модерация</a>
            <form method="POST" action="{{ route('moderator.logout') }}">
                @csrf
                <button type="submit" class="font-medium text-red-600">Выйти</button>
            </form>
        </div>

        <nav class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('moderator.queue') }}" class="rounded px-3 py-2 font-medium bg-white border {{ request()->routeIs('moderator.queue') ? 'border-indigo-600 text-indigo-600' : '' }}">Очередь</a>
            <a href="{{ route('moderator.recipes') }}" class="rounded px-3 py-2 font-medium bg-white border {{ request()->routeIs('moderator.recipes*') ? 'border-indigo-600 text-indigo-600' : '' }}">Рецепты</a>
            <a href="{{ route('moderator.restaurants') }}" class="rounded px-3 py-2 font-medium bg-white border {{ request()->routeIs('moderator.restaurants*') ? 'border-indigo-600 text-indigo-600' : '' }}">Рестораны</a>
            <a href="{{ route('moderator.trainers') }}" class="rounded px-3 py-2 font-medium bg-white border {{ request()->routeIs('moderator.trainers*') ? 'border-indigo-600 text-indigo-600' : '' }}">Тренеры</a>
        </nav>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
