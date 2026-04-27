@extends('layouts.admin')

@section('content')
<h1>Беседы (Conversations)</h1>

{{-- Таблица с данными --}}
<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Автомобиль</th>
            <th>Арендатор (кто начал)</th>
            <th>Владелец</th>
            <th>Дата создания</th>
            <th>Сообщений</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        @forelse($conversations as $conv)
            <tr>
                <td>{{ $conv->id }}</td>
                <td>
                    @if($conv->car)
                        {{ $conv->car->brand }} {{ $conv->car->model }} ({{ $conv->car->year }})
                    @else
                        <span class="text-muted">Авто удалено</span>
                    @endif
                </td>
                <td>{{ $conv->renter->name ?? '—' }}</td>
                <td>{{ $conv->owner->name ?? '—' }}</td>
                <td>{{ $conv->created_at->format('d.m.Y H:i') }}</td>
                <td>{{ $conv->messages_count ?? $conv->messages->count() }}</td>
                <td>
                    {{-- Кнопка удаления --}}
                    <form action="{{ route('admin.conversations.destroy', $conv) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить беседу и все её сообщения?')">
                            Удалить
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">Нет бесед</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Пагинация --}}
<div class="mt-3">
    {{ $conversations->links() }}
</div>
@endsection