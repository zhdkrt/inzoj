@extends('layouts.moderator')

@section('content')
<h2 class="text-center text-3xl font-extrabold mb-6">Рестораны</h2>
@include('moderator.partials.status-tabs', ['routeName' => 'moderator.restaurants', 'status' => $status])

<div class="flex flex-col gap-3">
    @forelse ($restaurants as $restaurant)
        <div class="bg-white rounded-lg p-4 border">
            <div class="flex justify-between gap-2 mb-2">
                <a href="{{ route('moderator.restaurants.show', $restaurant->id) }}" class="font-medium text-indigo-600">{{ $restaurant->name }}</a>
                @include('moderator.partials.status-badge', ['item' => $restaurant])
            </div>
            <div class="text-sm text-gray-600">{{ $restaurant->restaurant_type }} · {{ $restaurant->restaurantUser->email ?? '—' }}</div>
            @include('moderator.partials.review-form', ['actionUrl' => route('moderator.restaurants.review', $restaurant->id)])
        </div>
    @empty
        <div class="bg-white rounded-lg p-4 border text-gray-500">Нет ресторанов в этом статусе</div>
    @endforelse
</div>
@endsection
