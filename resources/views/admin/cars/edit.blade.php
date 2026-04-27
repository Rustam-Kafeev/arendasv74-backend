@extends('layouts.admin')
@section('content')
<h1>Редактировать: {{ $car->brand }} {{ $car->model }}</h1>
<form method="POST" action="{{ route('admin.cars.update', $car) }}">
    @csrf @method('PUT')
    <!-- Аналогичные поля, value="{{ old('brand', $car->brand) }}" -->
    <button class="btn btn-primary">Обновить</button>
</form>
@endsection