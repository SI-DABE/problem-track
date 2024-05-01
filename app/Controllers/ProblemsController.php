<?php

namespace App\Controllers;

use App\Models\Problem;
use Core\Http\Request;

class ProblemsController
{
    private string $layout = 'application';

    public function index(): void
    {
        $problems = Problem::all();

        $title = 'Problemas Registrados';

        if ($this->isJsonRequest()) {
            $this->renderJson('index', compact('problems', 'title'));
        } else {
            $this->render('index', compact('problems', 'title'));
        }
    }

    public function show(): void
    {
        $id = intval($_GET['id']);

        $problem = Problem::findById($id);

        $title = "Visualização do Problema #{$id}";
        $this->render('show', compact('problem', 'title'));
    }

    public function new(): void
    {
        $problem = new Problem();

        $title = 'Novo Problema';
        $this->render('new', compact('problem', 'title'));
    }

    public function create(Request $request): void
    {
        $params = $request->getParams();
        $problem = new Problem(title: $params['problem']['title']);

        if ($problem->save()) {
            $this->redirectTo(route('problems.index'));
        } else {
            $title = 'Novo Problema';
            $this->render('new', compact('problem', 'title'));
        }
    }

    public function edit(): void
    {
        $id = intval($_GET['id']);

        $problem = Problem::findById($id);

        $title = "Editar Problema #{$id}";
        $this->render('edit', compact('problem', 'title'));
    }

    public function update(): void
    {
        $method = $_REQUEST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        if ($method !== 'PUT') {
            $this->redirectTo('/pages/problems');
        }

        $params = $_POST['problem'];

        $problem = Problem::findById($params['id']);
        $problem->setTitle($params['title']);

        if ($problem->save()) {
            $this->redirectTo('/pages/problems');
        } else {
            $title = "Editar Problema #{$problem->getId()}";
            $this->render('edit', compact('problem', 'title'));
        }
    }

    public function destroy(): void
    {
        $method = $_REQUEST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        if ($method !== 'DELETE') {
            $this->redirectTo('/pages/problems');
        }

        $problem = Problem::findById($_POST['problem']['id']);
        $problem->destroy();

        $this->redirectTo('/pages/problems');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);

        $view = '/var/www/app/views/problems/' . $view . '.phtml';
        require '/var/www/app/views/layouts/' . $this->layout . '.phtml';
    }


    /**
     * @param array<string, mixed> $data
     */
    private function renderJson(string $view, array $data = []): void
    {
        extract($data);

        $view = '/var/www/app/views/problems/' . $view . '.json.php';
        $json = [];

        header('Content-Type: application/json; chartset=utf-8');
        require $view;
        echo json_encode($json);
        return;
    }

    private function redirectTo(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }

    private function isJsonRequest(): bool
    {
        return (isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] === 'application/json');
    }
}
