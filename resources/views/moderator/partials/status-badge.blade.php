@php
    $colors = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
    ];
    $labels = [
        'pending' => 'На проверке',
        'approved' => 'Одобрено',
        'rejected' => 'Отклонено',
    ];
    $itemStatus = $item->moderation_status;
@endphp
<span class="rounded px-2 py-1 text-sm font-medium {{ $colors[$itemStatus] ?? 'bg-gray-100' }}">
    {{ $labels[$itemStatus] ?? $itemStatus }}
</span>
