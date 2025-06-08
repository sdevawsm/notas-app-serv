@extends('layouts.app')

@section('title', 'Lista de Usuários')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Lista de Usuários</h1>
        <a href="/users/create" class="btn btn-primary">Novo Usuário</a>
    </div>

    @if(isset($users) && count($users) > 0)
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Admin</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->is_admin ? 'Sim' : 'Não' }}</td>
                            <td>{{ $user->created_at }}</td>
                            <td>
                                <a href="/users/{{ $user->id }}/edit" class="btn btn-sm btn-warning">Editar</a>
                                <form action="/users/{{ $user->id }}" method="POST" class="d-inline">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info">
            Nenhum usuário encontrado.
        </div>
    @endif
@endsection 