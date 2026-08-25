@extends('layouts.app')

@section('content')
<div class="gap-4 w-96 flex flex-col justify-between">
    <h2 class="text-center text-3xl font-extrabold">Заполните анкету</h2>

    <!-- Показываем ошибки -->
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('questionnaire.update') }}">
        @csrf
        <input type="hidden" name="step" value="{{ $step }}">
        
        @foreach($data as $key => $value)
            @if($key !== $step && $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <!-- Шаг 1: Имя -->
        @if($step === 'name')
        <div class="space-y-4">
            <h3 class="font-medium">Как вас зовут?</h3>
            <input type="text" name="name" value="{{ old('name', $data['name'] ?? '') }}" required 
                   class="w-full rounded px-3 py-2" 
                   placeholder="Ваше имя">
        </div>
        @endif

        <!-- Шаг 2: Цель -->
        @if($step === 'goal')
        <div class="space-y-4">
            <h3 class="font-medium">Какова ваша цель?</h3>
            <select name="goal" required class="w-full rounded px-3 py-2">
                <option value="">Выберите цель</option>
                <option value="lose_weight" {{ (old('goal', $data['goal'] ?? '') == 'lose_weight') ? 'selected' : '' }}>Похудеть</option>
                <option value="gain_muscle" {{ (old('goal', $data['goal'] ?? '') == 'gain_muscle') ? 'selected' : '' }}>Набрать мышечную массу</option>
                <option value="maintain" {{ (old('goal', $data['goal'] ?? '') == 'maintain') ? 'selected' : '' }}>Поддерживать вес</option>
                <option value="other" {{ (old('goal', $data['goal'] ?? '') == 'other') ? 'selected' : '' }}>Другое</option>
            </select>        
        </div>
        @endif

        <!-- Шаг 3: Вес -->
        @if($step === 'weight')
        <div class="space-y-4">
            <h3 class="font-medium">Текущий вес</h3>
            <input type="number" step="1" name="current_weight" value="{{ old('current_weight', $data['current_weight'] ?? '') }}" required class="w-full rounded px-3 py-2">
            <h3 class="font-medium">Желаемый вес (необязательно)</h3>
            <input type="number" step="1" name="target_weight" value="{{ old('target_weight', $data['target_weight'] ?? '') }}" class="w-full rounded px-3 py-2">
        </div>
        @endif

        <!-- Шаг 4: Рост -->
        @if($step === 'height')
        <div class="space-y-4">
            <h3 class="font-medium">Ваш рост</h3>
            <input type="number" name="height" value="{{ old('height', $data['height'] ?? '') }}" required class="w-full rounded px-3 py-2">
        </div>
        @endif

        <!-- Шаг 5: Возраст -->
        @if($step === 'age')
        <div class="space-y-4">
            <h3 class="font-medium">Ваш возраст</h3>
            <input type="number" name="age" value="{{ old('age', $data['age'] ?? '') }}" required class="w-full rounded px-3 py-2">
        </div>
        @endif

        <!-- Шаг 6: Активность -->
        @if($step === 'activity')
        <div class="space-y-4">
            <h3 class="font-medium">Уровень активности</h3>
            <select name="activity_level" required class="w-full rounded px-3 py-2">
                <option value="">Выберите уровень активности</option>
                <option value="low" {{ (old('activity_level', $data['activity_level'] ?? '') == 'low') ? 'selected' : '' }}>Низкий (сидячий образ жизни)</option>
                <option value="medium" {{ (old('activity_level', $data['activity_level'] ?? '') == 'medium') ? 'selected' : '' }}>Средний (легкие тренировки 1-3 раза в неделю)</option>
                <option value="high" {{ (old('activity_level', $data['activity_level'] ?? '') == 'high') ? 'selected' : '' }}>Высокий (интенсивные тренировки 3-5 раз в неделю)</option>
                <option value="expert" {{ (old('activity_level', $data['activity_level'] ?? '') == 'expert') ? 'selected' : '' }}>Эксперт (ежедневные интенсивные тренировки)</option>
            </select>
        </div>
        @endif
        
        <!-- Кнопка "Далее" -->
        <div class="flex gap-2 mt-6">
            <button type="submit" name="action" value="next" class="flex-1 py-2 px-4 bg-indigo-600 text-white rounded">
                Далее
            </button>
        </div>
    </form>
</div>
@endsection