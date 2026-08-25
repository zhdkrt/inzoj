@php
    $tabs = [
        'pending' => 'На проверке',
        'approved' => 'Одобренные',
        'rejected' => 'Отклонённые',
        'all' => 'Все',
    ];
@endphp
<div class="flex flex-wrap gap-2 mb-4">
    @foreach ($tabs as $key => $label)
        <a href="{{ route($routeName, ['status' => $key]) }}"
           class="rounded px-3 py-1 font-medium {{ $status === $key ? 'bg-indigo-600 text-white' : 'bg-white border' }}">
            {{ $label }}
        </a>
    @endforeach
</div>
