<?php

namespace App\Controllers;

use App\Models\User;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class AuthenticationsController
{
    private string $layout = 'login';

    public function new(): void
    {
        $this->render('new');
    }

    public function authenticate(Request $request): void
    {
        $params = $request->getParam('user');
        $user = User::findByEmail($params['email']);

        if ($user && $user->authenticate($params['password'])) {
            FlashMessage::success('Login realizado com sucesso!');

            $this->redirectTo('/problems');
        } else {
            FlashMessage::danger('Email e/ou senha inválidos!');
            $this->redirectTo('/login');
        }
    }

    public function destroy(): void
    {
        FlashMessage::success('Logout realizado com sucesso!');
        $this->redirectTo('/login');
    }


    /**
     * @param array<string, mixed> $data
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);

        $view = '/var/www/app/views/authentications/' . $view . '.phtml';
        require '/var/www/app/views/layouts/' . $this->layout . '.phtml';
    }

    private function redirectTo(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }
}
