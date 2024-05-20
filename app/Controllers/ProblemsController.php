<?php

namespace App\Controllers;

use App\Models\Problem;
use App\Models\User;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class ProblemsController
{
    private string $layout = 'application';


    public function index(Request $request): void
    {
        $paginator = Problem::paginate(page: $request->getParam('page', 1));
        $problems = $paginator->registers();

        $title = 'Problemas Registrados';

        if ($request->acceptJson()) {
            $this->renderJson('index', compact('paginator', 'problems', 'title'));
        } else {
            $this->render('index', compact('paginator', 'problems', 'title'));
        }
    }

    public function show(Request $request): void
    {
        $params = $request->getParams();

        $problem = Problem::findById($params['id']);

        $title = "Visualização do Problema #{$problem->getId()}";
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
            FlashMessage::success('Problema registrado com sucesso!');
            $this->redirectTo(route('problems.index'));
        } else {
            FlashMessage::danger('Existem dados incorretos! Por verifique!');
            $title = 'Novo Problema';
            $this->render('new', compact('problem', 'title'));
        }
    }

    public function edit(Request $request): void
    {
        $params = $request->getParams();
        $problem = Problem::findById($params['id']);

        $title = "Editar Problema #{$problem->getId()}";
        $this->render('edit', compact('problem', 'title'));
    }

    public function update(Request $request): void
    {
        $params = $request->getParams();

        $problem = Problem::findById($params['id']);
        $problem->setTitle($params['problem']['title']);

        if ($problem->save()) {
            FlashMessage::success('Problema atualizado com sucesso!');
            $this->redirectTo(route('problems.index'));
        } else {
            FlashMessage::danger('Existem dados incorretos! Por verifique!');
            $title = "Editar Problema #{$problem->getId()}";
            $this->render('edit', compact('problem', 'title'));
        }
    }

    public function destroy(Request $request): void
    {
        $params = $request->getParams();

        $problem = Problem::findById($params['id']);
        $problem->destroy();

        FlashMessage::success('Problema removido com sucesso!');
        $this->redirectTo(route('problems.index'));
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
}
