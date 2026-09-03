@extends ('layouts.app')

@section('content')
<div class="gap-4 w-96 flex flex-col justify-between">
    <h2 class="text-center text-3xl font-extrabold">Мои цели</h2>

    <!-- Просмотр данных -->
    @if(!$editing)
    <div class="flex flex-col gap-1">
        @foreach($fields as $field => $label)

        @php
        $current_field = $user->$field;
        $goal_convertor = [
            'lose_weight' => 'Похудеть',
            'gain_muscle' => 'Набрать мышечную массу',
            'maintain' => 'Поддерживать вес',
            'other' => 'Другое'
        ];
        if($field == 'goal' && $user->$field){
            $current_field = $goal_convertor[$current_field];
        }

        $activity_convertor = [
            'low' => 'Низкий',
            'medium' => 'Средний',
            'high' => 'Высокий',
            'expert' => 'Профи'
        ];
        if($field == 'activity_level' && $user->$field){
            $current_field = $activity_convertor[$current_field];
        }
        @endphp

        <form method="GET" action="{{ route('targets.edit') }}">
            @csrf
            <input type="hidden" name="field" value="{{ $field }}">
            <button type="submit" class="rounded w-full flex justify-between py-3 px-4 font-medium bg-white border">
                <label class="text-md">{{ $label }}</label>
                {{ $current_field ?? 'Не указано' }}
                @if (in_array($field, ['calories', 'proteins', 'fats', 'carbs', 'water']))
                    <br><label class="text-md">Недельная цель</label>
                    {{ $current_field !== null ? $current_field * 7 : 'Не указано' }}
                @endif
            </button>
        </form>
        @endforeach
        
        <form method="GET" action="{{ route('profile') }}">
            @csrf
            <button type="submit" class="rounded w-full flex justify-center py-2 px-4 font-medium text-red-600">
                Назад в профиль
            </button>
        </form>
    </div>

    @else
    <form method="POST" action="{{ route('targets.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="field" value="{{ $editingField }}">

        <div>
            <label class="text-md font-medium">
                {{ $fields[$editingField] }}
            </label>
            
            @if($editingField === 'goal')
            <select name="value" class="w-full rounded py-3 px-4">
                <option value="">Выберите цель</option>
                <option value="lose_weight" {{ $user->goal == 'lose_weight' ? 'selected' : '' }}>Похудеть</option>
                <option value="gain_muscle" {{ $user->goal == 'gain_muscle' ? 'selected' : '' }}>Набрать мышечную массу</option>
                <option value="maintain" {{ $user->goal == 'maintain' ? 'selected' : '' }}>Поддерживать вес и питаться правильно</option>
                <option value="other" {{ $user->goal == 'other' ? 'selected' : '' }}>Другое</option>
            </select>
            @elseif($editingField === 'activity_level')
            <select name="value" class="w-full rounded py-3 px-4">
                <option value="">Выберите уровень активности</option>
                <option value="low" {{ $user->goal == 'low' ? 'selected' : '' }}>Низкий</option>
                <option value="medium" {{ $user->goal == 'medium' ? 'selected' : '' }}>Средний</option>
                <option value="high" {{ $user->goal == 'high' ? 'selected' : '' }}>Высокий</option>
                <option value="expert" {{ $user->goal == 'expert' ? 'selected' : '' }}>Профи</option>
            </select>

            @else
            <input type="text" name="value" value="{{ old('value', $user->$editingField) }}" 
                   class="w-full rounded py-3 px-4">
            @endif

            @error('value')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">
                Сохранить
            </button>
            <a href="{{ route('targets') }}" class="bg-gray-300 px-4 py-2 rounded">
                Отменить
            </a>
        </div>
    </form>
    @endif
</div>
@endsection