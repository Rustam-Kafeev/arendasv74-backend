@extends('layouts.admin')
@section('content')
<h1>Добавить автомобиль</h1>
<form method="POST" action="{{ route('admin.cars.store') }}">
    @csrf
    <div class="mb-3">
        <label>Марка</label>
        <input name="brand" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Модель</label>
        <input name="model" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Год</label>
        <input type="number" name="year" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Город</label>
        <input name="city" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Описание</label>
        <textarea name="description" rows="3" class="form-control" required></textarea>
    </div>
    <div class="mb-3">
        <label>Цена за день (₽)</label>
        <input type="number" step="0.01" name="price_per_day" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Цена выкупа (₽, опционально)</label>
        <input type="number" step="0.01" name="buyout_price" class="form-control">
    </div>
    <div class="mb-3">
        <label>Владелец</label>
        <select name="user_id" class="form-select" required>
            <option value="">Выберите пользователя</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" name="is_available" value="1" class="form-check-input" checked>
        <label class="form-check-label">Доступен для бронирования</label>
    </div>
    <button class="btn btn-primary">Сохранить</button>
</form>
@endsection