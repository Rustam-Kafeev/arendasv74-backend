@extends('layouts.admin')
@section('content')
<h1>Пользователи</h1>
<a href="{{ route('admin.users.create') }}" class="btn btn-primary mb-3">Добавить</a>
<table class="table table-bordered">
    <thead><tr><th>ID</th><th>Имя</th><th>Email</th><th>Админ</th><th></th></tr></thead>
    <tbody>
    @foreach($users as $user)
    <tr>
        <td>{{ $user->id }}</td>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td>{{ $user->is_admin ? 'Да' : 'Нет' }}</td>
        <td>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">Ред.</a>
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Уд.</button>
            </form>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
{{ $users->links() }}
@endsection