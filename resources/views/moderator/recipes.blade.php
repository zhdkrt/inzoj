@extends('layouts.moderator')

@section('content')
<h2 class="text-center text-3xl font-extrabold mb-6">Рецепты</h2>
@include('moderator.partials.status-tabs', ['routeName' => 'moderator.recipes', 'status' => $status])

<div class="flex flex-col gap-3">
    @forelse ($recipes as $recipe)
        <div class="bg-white rounded-lg p-4 border">
            <div class="flex justify-between gap-2 mb-2">
                <a href="{{ route('moderator.recipes.show', $recipe->id) }}" class="font-medium text-indigo-600">{{ $recipe->name }}</a>
                @include('moderator.partials.status-badge', ['item' => $recipe])
            </div>
            <div class="text-sm text-gray-600">{{ $recipe->user->email ?? '—' }} · {{ $recipe->calories }} ккал</div>
            @include('moderator.partials.review-form', ['actionUrl' => route('moderator.recipes.review', $recipe->id)])
        </div>
    @empty
        <div class="bg-white rounded-lg p-4 border text-gray-500">Нет рецептов в этом статусе</div>
    @endforelse
</div>
@endsection
