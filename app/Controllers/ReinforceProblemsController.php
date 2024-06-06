<?php

namespace App\Controllers;

use App\Models\Problem;
use App\Models\ProblemUserReinforce;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;

class ReinforceProblemsController extends Controller
{
    public function index(Request $request): void
    {
        $paginator = Problem::paginate(page: $request->getParam('page', 1), route: 'reinforce.problems.paginate');
        $problems = $paginator->registers();

        $title = 'Todos Problemas';

        $this->render('reinforce_problems/index', compact('paginator', 'problems', 'title'));
    }

    public function supported(): void
    {
        $problems = $this->current_user->reinforced_problems;
        $title = 'Problemas Suportados';

        $this->render('reinforce_problems/supported', compact('problems', 'title'));
    }

    public function support(Request $request): void
    {
        $problem_id = $request->getParam('id');

        $problemReinforce = new ProblemUserReinforce(
            ['user_id' => $this->current_user->id, 'problem_id' => $problem_id]
        );

        if ($problemReinforce->save()) {
            FlashMessage::success('Problema suportado com sucesso.');
        } else {
            $message = $problemReinforce->errors('user_id');
            FlashMessage::danger('Erro ao suportar problema: ' . $message);
        }

        $this->redirectBack();
    }

    public function stoppedSupporting(Request $request): void
    {
        $problem_id = $request->getParam('id');

        $problemReinforce = ProblemUserReinforce::findBy(
            ['user_id' => $this->current_user->id, 'problem_id' => $problem_id]
        );

        if ($problemReinforce == null) {
            FlashMessage::danger('Erro ao parar de suportar problema: você não está suportando este problema.');
        } else {
            $problemReinforce->destroy();
            FlashMessage::success('Você parou de suportar o problema.');
        }

        $this->redirectBack();
    }
}
