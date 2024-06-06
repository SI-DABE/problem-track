<?php

namespace Core\Http\Controllers;

use App\Models\User;
use Core\Constants\Constants;
use Lib\Authentication\Auth;

class Controller
{
    protected string $layout = 'application';

    protected ?User $current_user = null;

    public function __construct()
    {
        $this->current_user = Auth::user();
    }

    public function currentUser(): ?User
    {
        if ($this->current_user === null) {
            $this->current_user = Auth::user();
        }

        return $this->current_user;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);

        $view = Constants::rootPath()->join('app/views/' . $view . '.phtml');
        require Constants::rootPath()->join('app/views/layouts/' . $this->layout . '.phtml');
    }


    /**
     * @param array<string, mixed> $data
     */
    protected function renderJson(string $view, array $data = []): void
    {
        extract($data);

        $view = Constants::rootPath()->join('app/views/' . $view . '.json.php');
        $json = [];

        header('Content-Type: application/json; chartset=utf-8');
        require $view;
        echo json_encode($json);
        return;
    }

    protected function redirectTo(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }

    protected function redirectBack(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirectTo($referer);
    }
}
