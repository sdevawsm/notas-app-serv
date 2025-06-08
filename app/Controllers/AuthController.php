<?php

namespace App\Controllers;

use App\Models\User;
use LadyPHP\Http\Request;
use LadyPHP\Http\Response;
use LadyPHP\Auth\JwtAuth;

class AuthController {
    private JwtAuth $jwtAuth;

    public function __construct()
    {
        $this->jwtAuth = new JwtAuth($_ENV['JWT_SECRET'] ?? 'sua-chave-secreta');
    }

    public function apiLogin(Request $request): Response
    {
        try {
            $data = json_decode($request->getBody(), true);
            
            if (!isset($data['email']) || !isset($data['password'])) {
                return new Response(
                    json_encode([
                        'success' => false,
                        'message' => 'Email e senha são obrigatórios'
                    ]),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }

            // Busca o usuário pelo email
            $users = User::where('email', '=', $data['email']);
            
            if (empty($users)) {
                return new Response(
                    json_encode([
                        'success' => false,
                        'message' => 'Usuário não encontrado'
                    ]),
                    404,
                    ['Content-Type' => 'application/json']
                );
            }

            $user = $users[0];

            // Verifica a senha
            if (!password_verify($data['password'], $user->password)) {
                return new Response(
                    json_encode([
                        'success' => false,
                        'message' => 'Senha incorreta'
                    ]),
                    401,
                    ['Content-Type' => 'application/json']
                );
            }

            // Gera o token
            $token = $this->jwtAuth->login($user->toArray());

            return new Response(
                json_encode([
                    'success' => true,
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ]
                ]),
                200,
                ['Content-Type' => 'application/json']
            );

        } catch (\Exception $e) {
            return new Response(
                json_encode([
                    'success' => false,
                    'message' => 'Erro ao realizar login: ' . $e->getMessage()
                ]),
                500,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function register(Request $request): Response
    {
        try {
            $data = json_decode($request->getBody(), true);
            
            if (!isset($data['email']) || !isset($data['password']) || !isset($data['name'])) {
                return new Response(
                    json_encode([
                        'success' => false,
                        'message' => 'Nome, email e senha são obrigatórios'
                    ]),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }

            // Verifica se o email já existe
            $existingUser = User::where('email', '=', $data['email']);
            if (!empty($existingUser)) {
                return new Response(
                    json_encode([
                        'success' => false,
                        'message' => 'Email já cadastrado'
                    ]),
                    400,
                    ['Content-Type' => 'application/json']
                );
            }

            // Cria o usuário
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
            $user->save();

            // Gera o token
            $token = $this->jwtAuth->login($user->toArray());

            return new Response(
                json_encode([
                    'success' => true,
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ]
                ]),
                201,
                ['Content-Type' => 'application/json']
            );

        } catch (\Exception $e) {
            return new Response(
                json_encode([
                    'success' => false,
                    'message' => 'Erro ao registrar usuário: ' . $e->getMessage()
                ]),
                500,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function logout(Request $request): Response
    {
        try {
            $token = $request->getHeader('Authorization');
            if ($token && preg_match('/^Bearer\s+(.+)$/i', $token, $matches)) {
                $this->jwtAuth->logout($matches[1]);
            }
            
            return new Response(
                json_encode([
                    'success' => true,
                    'message' => 'Logout realizado com sucesso'
                ]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new Response(
                json_encode([
                    'success' => false,
                    'message' => 'Erro ao realizar logout: ' . $e->getMessage()
                ]),
                500,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function refresh(Request $request): Response
    {
        try {
            $token = $request->getHeader('Authorization');
            if ($token && preg_match('/^Bearer\s+(.+)$/i', $token, $matches)) {
                $newToken = $this->jwtAuth->refresh($matches[1]);
                if ($newToken) {
                    return new Response(
                        json_encode([
                            'success' => true,
                            'token' => $newToken
                        ]),
                        200,
                        ['Content-Type' => 'application/json']
                    );
                }
            }
            
            return new Response(
                json_encode([
                    'success' => false,
                    'message' => 'Token inválido ou expirado'
                ]),
                401,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new Response(
                json_encode([
                    'success' => false,
                    'message' => 'Erro ao atualizar token: ' . $e->getMessage()
                ]),
                500,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function user(Request $request): Response
    {
        try {
            $user = $request->getAttribute('user');
            $userId = $request->getAttribute('user_id');
            
            return new Response(
                json_encode([
                    'success' => true,
                    'user' => $user,
                    'user_id' => $userId
                ]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new Response(
                json_encode([
                    'success' => false,
                    'message' => 'Erro ao obter dados do usuário: ' . $e->getMessage()
                ]),
                500,
                ['Content-Type' => 'application/json']
            );
        }
    }
}
