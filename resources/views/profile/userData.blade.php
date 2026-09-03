@extends('layouts.app')

@section('content')
<div class="gap-4 w-96 flex flex-col justify-between">
    <h2 class="text-center text-3xl font-extrabold">Личные данные</h2>

    <!-- Просмотр данных -->
    @if(!$editing)
    <div class="flex flex-col gap-1">
        @foreach($fields as $field => $label)

        @php
        $currentField = $user->$field;
        $genderConvertor = [
            'male' => 'Мужской',
            'female' => 'Женский'    
        ];
        if($field == 'gender' && $user->$field){
            $currentField = $genderConvertor[$currentField];
        }
        @endphp

        <form method="GET" action="{{ route('userData.edit') }}">
            @csrf
            <input type="hidden" name="field" value="{{ $field }}">
            <button type="submit" class="rounded w-full flex justify-between py-3 px-4 font-medium bg-white border">
                <label class="text-md">{{ $label }}</label>
                {{ $currentField ?? 'Не указано' }}
            </button>
        </form>
        @endforeach

        <div class="flex flex-col gap-1">
            <p>Индекс массы тела: {{ $calculated_data['imt'] ?? '—' }} ({{ $calculated_data['imt_classification'] ?? 'недостаточно данных' }})</p>
            <p>Базовый метаболизм: {{ $calculated_data['bmr'] ?? '—' }} ккал</p>
            <p>Суточная норма калорий: {{ $calculated_data['calories_norm'] ?? '—' }} ккал</p>
        </div>
        
        <form method="GET" action="{{ route('profile') }}">
            @csrf
            <button type="submit" class="rounded w-full flex justify-center py-2 px-4 font-medium text-red-600">
                Назад в профиль
            </button>
        </form>
    </div>

    <!-- Режим редактирования -->
    @else
    <form method="POST" action="{{ route('userData.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="field" value="{{ $editingField }}">

        <div>
            <label class="text-md font-medium">
                {{ $fields[$editingField] }}
            </label>
            
            @if($editingField === 'gender')
            <select name="value" class="w-full rounded py-3 px-4">
                <option value="">Выберите пол</option>
                <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Мужской</option>
                <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Женский</option>
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
            <a href="{{ route('userData') }}" class="bg-gray-300 px-4 py-2 rounded">
                Отменить
            </a>
        </div>
    </form>
    @endif
</div>
@endsection