@extends('layouts.moderator')

@section('content')
<h2 class="text-center text-3xl font-extrabold mb-6">Тренеры</h2>
@include('moderator.partials.status-tabs', ['routeName' => 'moderator.trainers', 'status' => $status])

<div class="flex flex-col gap-3">
    @forelse ($trainers as $trainer)
        <div class="bg-white rounded-lg p-4 border">
            <div class="flex justify-between gap-2 mb-2">
                <a href="{{ route('moderator.trainers.show', $trainer->id) }}" class="font-medium text-indigo-600">
                    {{ trim(($trainer->name ?? '').' '.($trainer->surname ?? '')) ?: $trainer->email }}
                </a>
                @include('moderator.partials.status-badge', ['item' => $trainer])
            </div>
            <div class="text-sm text-gray-600">{{ $trainer->email }}</div>
            @include('moderator.partials.review-form', ['actionUrl' => route('moderator.trainers.review', $trainer->id)])
        </div>
    @empty
        <div class="bg-white rounded-lg p-4 border text-gray-500">Нет тренеров в этом статусе</div>
    @endforelse
</div>
@endsection
