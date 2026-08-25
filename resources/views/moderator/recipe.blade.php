@extends('layouts.moderator')

@section('content')
<h2 class="text-center text-3xl font-extrabold mb-6">{{ $recipe->name }}</h2>

<div class="bg-white rounded-lg p-4 border flex flex-col gap-2">
    <div class="flex justify-between">
        @include('moderator.partials.status-badge', ['item' => $recipe])
        <span class="text-sm text-gray-500">{{ $recipe->created_at }}</span>
    </div>
    <div><span class="font-medium">Автор:</span> {{ $recipe->user->email ?? '—' }}</div>
    <div><span class="font-medium">Ккал / Б / Ж / У:</span> {{ $recipe->calories }} / {{ $recipe->proteins }} / {{ $recipe->fats }} / {{ $recipe->carbs }}</div>
    <div>
        <div class="font-medium">Инструкция</div>
        <p class="whitespace-pre-line">{{ $recipe->instructions }}</p>
    </div>
    @if ($recipe->moderation_comment)
        <div><span class="font-medium">Комментарий:</span> {{ $recipe->moderation_comment }}</div>
    @endif
    @include('moderator.partials.review-form', ['actionUrl' => route('moderator.recipes.review', $recipe->id)])
</div>

<a href="{{ route('moderator.recipes') }}" class="block text-center mt-4 text-red-600 font-medium">Назад к списку</a>
@endsection
