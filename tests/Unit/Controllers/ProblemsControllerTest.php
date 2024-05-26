<?php

namespace Tests\Unit\Controllers;

use App\Models\Problem;
use App\Models\User;

class ProblemsControllerTest extends ControllerTestCase
{
    public function test_list_all_problems(): void
    {
        $user = new User([
            'name' => 'User 1',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();
        $_SESSION['user']['id'] = $user->id;

        $problems[] = new Problem(['title' => 'Problem 1', 'user_id' => $user->id]);
        $problems[] = new Problem(['title' => 'Problem 2',  'user_id' => $user->id]);

        foreach ($problems as $problem) {
            $problem->save();
        }

        $response = $this->get(action: 'index', controller: 'App\Controllers\ProblemsController');

        foreach ($problems as $problem) {
            $this->assertMatchesRegularExpression("/{$problem->title}/", $response);
        }
    }
}
