@extends('layouts.app')

@section('title', isset($user) ? 'Editar Usuário' : 'Novo Usuário')

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>{{ isset($user) ? 'Editar Usuário' : 'Novo Usuário' }}</h2>
        </div>
        <div class="card-body">
            @if(isset($errors) && count($errors) > 0)
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors as $field => $messages)
                            @foreach($messages as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ isset($user) ? '/users/' . $user->id : '/users' }}" method="POST">
                @if(isset($user))
                    <input type="hidden" name="_method" value="PUT">
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $user->name ?? '' }}" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ $user->email ?? '' }}" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" {{ !isset($user) ? 'required' : '' }}>
                    @if(isset($user))
                        <small class="text-muted">Deixe em branco para manter a senha atual</small>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" value="1" {{ (isset($user) && $user->is_admin) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_admin">Administrador</label>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/users" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
@endsection 