<div class="flex flex-col gap-2 mt-3">
    <form method="POST" action="{{ $actionUrl }}">
        @csrf
        <input type="hidden" name="action" value="approve">
        <button type="submit" class="rounded w-full py-2 px-4 font-medium text-white bg-green-600">
            Одобрить
        </button>
    </form>
    <form method="POST" action="{{ $actionUrl }}" class="flex gap-2">
        @csrf
        <input type="hidden" name="action" value="reject">
        <input type="text" name="comment" value="{{ old('comment') }}" class="flex-1 rounded px-3 py-2" placeholder="Комментарий (необязательно)">
        <button type="submit" class="rounded py-2 px-4 font-medium text-white bg-red-600">
            Отклонить
        </button>
    </form>
</div>
