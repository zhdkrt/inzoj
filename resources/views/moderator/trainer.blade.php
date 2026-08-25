@extends('layouts.moderator')

@section('content')
<h2 class="text-center text-3xl font-extrabold mb-6">
    {{ trim(($trainer->name ?? '').' '.($trainer->surname ?? '')) ?: $trainer->email }}
</h2>

<div class="bg-white rounded-lg p-4 border flex flex-col gap-2">
    <div class="flex justify-between">
        @include('moderator.partials.status-badge', ['item' => $trainer])
        <span class="text-sm text-gray-500">{{ $trainer->created_at }}</span>
    </div>
    <div><span class="font-medium">Email:</span> {{ $trainer->email }}</div>
    <div><span class="font-medium">Опыт:</span> {{ $trainer->experience ?? '—' }}</div>
    <div><span class="font-medium">Достижения:</span> {{ $trainer->achievements ?? '—' }}</div>
    @if ($trainer->moderation_comment)
        <div><span class="font-medium">Комментарий:</span> {{ $trainer->moderation_comment }}</div>
    @endif

    <div class="font-medium mt-2">Тренировки</div>
    @forelse ($trainer->trainings as $training)
        <div class="text-sm">{{ $training->name }} · {{ $training->date }}</div>
    @empty
        <div class="text-gray-500">Тренировок пока нет</div>
    @endforelse

    @include('moderator.partials.review-form', ['actionUrl' => route('moderator.trainers.review', $trainer->id)])
</div>

<a href="{{ route('moderator.trainers') }}" class="block text-center mt-4 text-red-600 font-medium">Назад к списку</a>
@endsection
