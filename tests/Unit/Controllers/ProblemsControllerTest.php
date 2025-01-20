<?php

namespace Tests\Unit\Controllers;

use App\Models\Problem;
use App\Models\User;

class ProblemsControllerTest extends ControllerTestCase
{
    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = new User([
            'name' => 'User 1',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $this->user->save();
        $_SESSION['user']['id'] = $this->user->id;
    }

    public function test_list_all_problems(): void
    {
        $problems[] = new Problem(['title' => 'Problem 1', 'user_id' => $this->user->id]);
        $problems[] = new Problem(['title' => 'Problem 2',  'user_id' => $this->user->id]);

        foreach ($problems as $problem) {
            $problem->save();
        }

        $response = $this->get(action: 'index', controllerName: 'App\Controllers\ProblemsController');

        foreach ($problems as $problem) {
            $this->assertMatchesRegularExpression("/{$problem->title}/", $response);
        }
    }

    public function test_show_problem(): void
    {
        $problem = new Problem(['title' => 'Problem 1', 'user_id' => $this->user->id]);
        $problem->save();

        $response = $this->get(
            action: 'show',
            controllerName: 'App\Controllers\ProblemsController',
            params: ['id' => $problem->id]
        );

        $this->assertMatchesRegularExpression("/Visualização do Problema #{$problem->id}/", $response);
        $this->assertMatchesRegularExpression("/{$problem->title}/", $response);
    }

    public function test_successfully_create_problem(): void
    {
        $params = ['problem' => ['title' => 'Problema test']];

        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\ProblemsController',
            params: $params
        );

        $this->assertMatchesRegularExpression("/Location: \/problems/", $response);
    }

    public function test_unsuccessfully_create_problem(): void
    {
        $params = ['problem' => ['title' => '']];

        $response = $this->post(
            action: 'create',
            controllerName: 'App\Controllers\ProblemsController',
            params: $params
        );

        $this->assertMatchesRegularExpression("/não pode ser vazio!/", $response);
    }

    public function test_edit_problem(): void
    {
        $problem = new Problem(['title' => 'Problem 1', 'user_id' => $this->user->id]);
        $problem->save();

        $response = $this->get(
            action: 'edit',
            controllerName: 'App\Controllers\ProblemsController',
            params: ['id' => $problem->id]
        );

        $this->assertMatchesRegularExpression("/Editar Problema #{$problem->id}/", $response);

        $regex = '/<input\s[^>]*type=[\'"]text[\'"][^>]*name=[\'"]problem\[title\][\'"][^>]*value=[\'"]Problem 1[\'"][^>]*>/i';
        $this->assertMatchesRegularExpression($regex, $response);
    }


    public function test_successfully_update_problem(): void
    {
        $problem = new Problem(['title' => 'Problem 1', 'user_id' => $this->user->id]);
        $problem->save();
        $params = ['id' => $problem->id, 'problem' => ['title' => 'Problem updated']];

        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\ProblemsController',
            params: $params
        );

        $this->assertMatchesRegularExpression("/Location: \/problems/", $response);
    }

    public function test_unsuccessfully_update_problem(): void
    {
        $problem = new Problem(['title' => 'Problem 1', 'user_id' => $this->user->id]);
        $problem->save();
        $params = ['id' => $problem->id, 'problem' => ['title' => '']];

        $response = $this->put(
            action: 'update',
            controllerName: 'App\Controllers\ProblemsController',
            params: $params
        );

        $this->assertMatchesRegularExpression("/não pode ser vazio!/", $response);
    }
}
