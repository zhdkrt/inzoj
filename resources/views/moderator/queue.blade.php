@extends('layouts.moderator')

@section('content')
<h2 class="text-center text-3xl font-extrabold mb-6">Очередь на проверке</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <a href="{{ route('moderator.recipes') }}" class="bg-white rounded-lg p-4 border">
        <div class="text-sm text-gray-500">Рецепты</div>
        <div class="text-3xl font-extrabold">{{ $pending['recipes'] }}</div>
    </a>
    <a href="{{ route('moderator.restaurants') }}" class="bg-white rounded-lg p-4 border">
        <div class="text-sm text-gray-500">Рестораны</div>
        <div class="text-3xl font-extrabold">{{ $pending['restaurants'] }}</div>
    </a>
    <a href="{{ route('moderator.trainers') }}" class="bg-white rounded-lg p-4 border">
        <div class="text-sm text-gray-500">Тренеры</div>
        <div class="text-3xl font-extrabold">{{ $pending['trainers'] }}</div>
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white rounded-lg p-4 border flex flex-col gap-2">
        <h3 class="font-medium">Последние рецепты</h3>
        @forelse ($recipes as $recipe)
            <a href="{{ route('moderator.recipes.show', $recipe->id) }}" class="text-indigo-600">{{ $recipe->name }}</a>
        @empty
            <span class="text-gray-500">Нет заявок</span>
        @endforelse
    </div>
    <div class="bg-white rounded-lg p-4 border flex flex-col gap-2">
        <h3 class="font-medium">Последние рестораны</h3>
        @forelse ($restaurants as $restaurant)
            <a href="{{ route('moderator.restaurants.show', $restaurant->id) }}" class="text-indigo-600">{{ $restaurant->name }}</a>
        @empty
            <span class="text-gray-500">Нет заявок</span>
        @endforelse
    </div>
    <div class="bg-white rounded-lg p-4 border flex flex-col gap-2">
        <h3 class="font-medium">Последние тренеры</h3>
        @forelse ($trainers as $trainer)
            <a href="{{ route('moderator.trainers.show', $trainer->id) }}" class="text-indigo-600">{{ $trainer->email }}</a>
        @empty
            <span class="text-gray-500">Нет заявок</span>
        @endforelse
    </div>
</div>
@endsection
