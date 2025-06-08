<?php

namespace App\Controllers;

use App\Models\User;
use LadyPHP\View\ElleCompiler;
use LadyPHP\Validation\Validator;


use LadyPHP\Http\Response;

class UserController
{
    private ElleCompiler $view;
    private Validator $validator;

    public function __construct()
    {
        $this->view = new ElleCompiler(
            __DIR__ . '/../../resources/views',
            __DIR__ . '/../../storage/cache/views'
        );
    }

    public function index()
    {
        $users = User::all();
        // $view = $this->view->compile('users/index', ['users' => $users]);
        // require $view;

        $json = User::collectionToJson($users);
        
        return new Response(
            $json,
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public function create()
    {
        $view = $this->view->compile('users/form');
        require $view;
    }

    public function store()
    {
        $data = $_POST;
        $this->validator = new Validator($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6'
        ]);

        if (!$this->validator->validate()) {
            $view = $this->view->compile('users/form', [
                'errors' => $this->validator->getErrors()
            ]);
            require $view;
            return;
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['is_admin'] = isset($data['is_admin']);

        User::create($data);
        header('Location: /users');
    }

    public function edit(int $id)
    {
        $user = User::find($id);
        if (!$user) {
            header('Location: /users');
            return;
        }

        $view = $this->view->compile('users/form', ['user' => $user]);
        require $view;
    }

    public function update(int $id)
    {
        $user = User::find($id);
        if (!$user) {
            header('Location: /users');
            return;
        }

        $data = $_POST;
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id
        ];

        if (!empty($data['password'])) {
            $rules['password'] = 'string|min:6';
        }

        $this->validator = new Validator($data, $rules);

        if (!$this->validator->validate()) {
            $view = $this->view->compile('users/form', [
                'user' => $user,
                'errors' => $this->validator->getErrors()
            ]);
            require $view;
            return;
        }

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        $data['is_admin'] = isset($data['is_admin']);

        $user->fill($data);
        $user->save();

        header('Location: /users');
    }

    public function delete(int $id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
        }
        header('Location: /users');
    }

    public function addUser()
    {
        try {
            $user = new User();
            $user->name = 'Usuário Teste';
            $user->email = 'teste@exemplo.com';
            $user->password = password_hash('123456', PASSWORD_DEFAULT);
            
            if ($user->save()) {
                return new Response(
                    json_encode([
                        'success' => true,
                        'message' => 'Usuário criado com sucesso!',
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email
                        ]
                    ]),
                    200,
                    ['Content-Type' => 'application/json']
                );
            }

            return new Response(
                json_encode([
                    'success' => false,
                    'message' => 'Erro ao criar usuário'
                ]),
                400,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new Response(
                json_encode([
                    'success' => false,
                    'message' => 'Erro: ' . $e->getMessage()
                ]),
                500,
                ['Content-Type' => 'application/json']
            );
        }
    }
}
