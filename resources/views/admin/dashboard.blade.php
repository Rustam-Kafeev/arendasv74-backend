@extends('layouts.admin')
@section('content')
<h1>Главная панель</h1>
<p>Добро пожаловать, {{ Auth::user()->name }}!</p>
<hr>
<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Пользователи</h5>
                <p class="card-text display-6">{{ \App\Models\User::count() }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Автомобили</h5>
                <p class="card-text display-6">{{ \App\Models\Car::count() }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">Беседы</h5>
                <p class="card-text display-6">{{ \App\Models\Conversation::count() }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Сообщения</h5>
                <p class="card-text display-6">{{ \App\Models\Message::count() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection