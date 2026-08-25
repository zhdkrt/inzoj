@extends('layouts.moderator')

@section('content')
<h2 class="text-center text-3xl font-extrabold mb-6">{{ $restaurant->name }}</h2>

<div class="bg-white rounded-lg p-4 border flex flex-col gap-2">
    <div class="flex justify-between">
        @include('moderator.partials.status-badge', ['item' => $restaurant])
        <span class="text-sm text-gray-500">{{ $restaurant->created_at }}</span>
    </div>
    <div><span class="font-medium">Тип:</span> {{ $restaurant->restaurant_type }}</div>
    <div><span class="font-medium">Владелец:</span> {{ $restaurant->restaurantUser->email ?? '—' }}</div>
    @if ($restaurant->moderation_comment)
        <div><span class="font-medium">Комментарий:</span> {{ $restaurant->moderation_comment }}</div>
    @endif

    <div class="font-medium mt-2">Блюда</div>
    @forelse ($restaurant->dishes as $dish)
        <div class="text-sm">{{ $dish->name }} · {{ $dish->calories }} ккал</div>
    @empty
        <div class="text-gray-500">Меню пока пустое</div>
    @endforelse

    @include('moderator.partials.review-form', ['actionUrl' => route('moderator.restaurants.review', $restaurant->id)])
</div>

<a href="{{ route('moderator.restaurants') }}" class="block text-center mt-4 text-red-600 font-medium">Назад к списку</a>
@endsection
