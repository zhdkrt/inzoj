@extends('layouts.app')

@section('content')
<div class="gap-4 w-96 flex flex-col justify-between">
    <h2 class="text-center text-3xl font-extrabold">Профиль</h2>

    <div class="bg-white rounded-lg p-4 gap-2 flex flex-col">
        @if(!Auth::user()->is_premium)
            <a href="{{ route('premium') }}" class="rounded flex justify-start py-1 px-3 font-medium bg-white border">
                Улучшить
            </a>
        @endif
        <span class="font-medium">{{ Auth::user()->name }}</span>
        <div class="flex justify-between">
            <span class="font-medium">Текущий вес</span>
            <span>{{ Auth::user()->current_weight }}</span>
        </div>
        <div class="flex justify-between">
            <span class="font-medium">Желаемый вес</span>
            <span>{{ Auth::user()->target_weight }}</span>
        </div>
        @php
        $goal_convertor = [
            'lose_weight' => 'Похудеть',
            'gain_muscle' => 'Набрать мышечную массу',
            'maintain' => 'Поддерживать вес и питаться правильно',
            'other' => 'Другое'
        ]
        @endphp
        <span>{{ $goal_convertor[Auth::user()->goal] ?? 'Не указано' }}</span>
    </div>

    

    <div>
        <a href="{{ route('userData') }}" class="rounded w-full flex justify-start py-3 px-4 font-medium bg-white border">
            Личные данные
        </a>
        <a href="{{ route('targets') }}" class="rounded w-full flex justify-start py-3 px-4 font-medium bg-white border">
            Мои цели
        </a>
        <a href="{{ route('ration') }}" class="rounded w-full flex justify-start py-3 px-4 font-medium bg-white border">
            Особенности рациона
        </a>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="rounded w-full flex justify-center py-2 px-4 font-medium text-red-600">
            Выйти из аккаунта
        </button>
    </form>
    
    <div class="flex justify-between">
        <a href="{{ route('diary') }}" class="text-gray-600">Дневник</a>
        <a href="{{ route('restaurant') }}" class="text-gray-600">Рестораны</a>
        <a href="{{ route('sport') }}" class="text-gray-600">Тренировки</a>
        <a href="#" class="text-green-600 font-medium">Профиль</a>
    </div>
</div>

@endsection