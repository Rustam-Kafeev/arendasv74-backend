@extends('layouts.admin')
@section('content')
<h1>Автомобили</h1>
<a href="{{ route('admin.cars.create') }}" class="btn btn-primary mb-3">Добавить</a>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Марка</th>
            <th>Модель</th>
            <th>Год</th>
            <th>Город</th>
            <th>Цена/день</th>
            <th>Владелец</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    @forelse($cars as $car)
        <tr>
            <td>{{ $car->id }}</td>
            <td>{{ $car->brand }}</td>
            <td>{{ $car->model }}</td>
            <td>{{ $car->year }}</td>
            <td>{{ $car->city }}</td>
            <td>{{ $car->price_per_day }} ₽</td>
            <td>{{ $car->user->name }}</td>
            <td>
                <a href="{{ route('admin.cars.edit', $car) }}" class="btn btn-sm btn-warning">Ред.</a>
                <form action="{{ route('admin.cars.destroy', $car) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Уд.</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="8">Нет автомобилей</td></tr>
    @endforelse
    </tbody>
</table>
{{ $cars->links() }}
@endsection