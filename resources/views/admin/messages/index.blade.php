@extends('layouts.admin')

@section('content')
   <h1>Сообщения (Messages)</h1>

   <table class="table table-bordered table-hover">
      <thead class="table-dark">
         <tr>
            <th>ID</th>
            <th>Беседа (автомобиль)</th>
            <th>Отправитель</th>
            <th>Текст сообщения</th>
            <th>Дата</th>
            <th>Прочитано</th>
            <th>Действия</th>
         </tr>
      </thead>
      <tbody>
         @forelse($messages as $msg)
            <tr>
               <td>{{ $msg->id }}</td>
               <td>
                  @if($msg->conversation && $msg->conversation->car)
                     {{ $msg->conversation->car->brand }} {{ $msg->conversation->car->model }}
                  @else
                     <span class="text-muted">Беседа не найдена</span>
                  @endif
               </td>
               <td>{{ $msg->user->name ?? 'Неизвестно' }}</td>
               <td>{{ Str::limit($msg->body, 50) }}</td>
               <td>{{ $msg->created_at->format('d.m.Y H:i') }}</td>
               <td>
                  @if($msg->is_read)
                     <span class="badge bg-success">Да</span>
                  @else
                     <span class="badge bg-warning text-dark">Нет</span>
                  @endif
               </td>
               <td>
                  <form action="{{ route('admin.messages.destroy', $msg) }}" method="POST" class="d-inline">
                     @csrf
                     @method('DELETE')
                     <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить сообщение?')">
                        Удалить
                     </button>
                  </form>
               </td>
            </tr>
         @empty
            <tr>
               <td colspan="7" class="text-center">Сообщений нет</td>
            </tr>
         @endforelse
      </tbody>
   </table>

   <div class="mt-3">
      {{ $messages->links() }}
   </div>
@endsection