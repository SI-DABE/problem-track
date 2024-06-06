<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;

class ProblemsController extends Controller
{
    public function index(Request $request): void
    {
        $paginator = $this->current_user->problems()->paginate(page: $request->getParam('page', 1));
        $problems = $paginator->registers();

        $title = 'Problemas Registrados';

        if ($request->acceptJson()) {
            $this->renderJson('problems/index', compact('paginator', 'problems', 'title'));
        } else {
            $this->render('problems/index', compact('paginator', 'problems', 'title'));
        }
    }

    public function show(Request $request): void
    {
        $params = $request->getParams();

        $problem = $this->current_user->problems()->findById($params['id']);

        $title = "Visualização do Problema #{$problem->id}";
        $this->render('problems/show', compact('problem', 'title'));
    }

    public function new(): void
    {
        $problem = $this->current_user->problems()->new();

        $title = 'Novo Problema';
        $this->render('problems/new', compact('problem', 'title'));
    }

    public function create(Request $request): void
    {
        $params = $request->getParams();
        $problem = $this->current_user->problems()->new($params['problem']);

        if ($problem->save()) {
            FlashMessage::success('Problema registrado com sucesso!');
            $this->redirectTo(route('problems.index'));
        } else {
            FlashMessage::danger('Existem dados incorretos! Por verifique!');
            $title = 'Novo Problema';
            $this->render('problems/new', compact('problem', 'title'));
        }
    }

    public function edit(Request $request): void
    {
        $params = $request->getParams();
        $problem = $this->current_user->problems()->findById($params['id']);

        $title = "Editar Problema #{$problem->id}";
        $this->render('problems/edit', compact('problem', 'title'));
    }

    public function update(Request $request): void
    {
        $id = $request->getParam('id');
        $params = $request->getParam('problem');

        $problem = $this->current_user->problems()->findById($id);
        $problem->title = $params['title'];

        if ($problem->save()) {
            FlashMessage::success('Problema atualizado com sucesso!');
            $this->redirectTo(route('problems.index'));
        } else {
            FlashMessage::danger('Existem dados incorretos! Por verifique!');
            $title = "Editar Problema #{$problem->id}";
            $this->render('problems/edit', compact('problem', 'title'));
        }
    }

    public function destroy(Request $request): void
    {
        $params = $request->getParams();

        $problem = $this->current_user->problems()->findById($params['id']);
        $problem->destroy();

        FlashMessage::success('Problema removido com sucesso!');
        $this->redirectTo(route('problems.index'));
    }
}
