<?php

require '/var/www/app/models/Problem.php';

class ProblemsController
{
    private string $layout = 'application';

    public function index()
    {
        $problems = Problem::all();

        $title = 'Problemas Registrados';

        if ($this->isJsonRequest())
            $this->renderJson('index', compact('problems', 'title'));
        else
            $this->render('index', compact('problems', 'title'));
    }

    public function show()
    {
        $id = intval($_GET['id']);

        $problem = Problem::findById($id);

        $title = "Visualização do Problema #{$id}";
        $this->render('show', compact('problem', 'title'));
    }

    public function new()
    {
        $problem = new Problem();

        $title = 'Novo Problema';
        $this->render('new', compact('problem', 'title'));
    }

    public function create()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method !== 'POST') $this->redirectTo('/pages/problems');

        $params = $_POST['problem'];
        $problem = new Problem(title: $params['title']);

        if ($problem->save()) {
            $this->redirectTo('/pages/problems');
        } else {
            $title = 'Novo Problema';
            $this->render('new', compact('problem', 'title'));
        }
    }

    public function edit()
    {
        $id = intval($_GET['id']);

        $problem = Problem::findById($id);

        $title = "Editar Problema #{$id}";
        $this->render('edit', compact('problem', 'title'));
    }

    public function update()
    {
        $method = $_REQUEST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        if ($method !== 'PUT') $this->redirectTo('/pages/problems');

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

    public function destroy()
    {
        $method = $_REQUEST['_method'] ?? $_SERVER['REQUEST_METHOD'];
        if ($method !== 'DELETE') $this->redirectTo('/pages/problems');

        $problem = Problem::findById($_POST['problem']['id']);
        $problem->destroy();

        $this->redirectTo('/pages/problems');
    }


    private function render($view, $data = [])
    {
        extract($data);

        $view = '/var/www/app/views/problems/' . $view . '.phtml';
        require '/var/www/app/views/layouts/' . $this->layout . '.phtml';
    }

    private function renderJson($view, $data = [])
    {
        extract($data);

        $view = '/var/www/app/views/problems/' . $view . '.json.php';
        $json = [];

        header('Content-Type: application/json; chartset=utf-8');
        require $view;
        echo json_encode($json);
        return;
    }

    private function redirectTo($location)
    {
        header('Location: ' . $location);
        exit;
    }

    private function isJsonRequest()
    {
        return (isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] === 'application/json');
    }
}
