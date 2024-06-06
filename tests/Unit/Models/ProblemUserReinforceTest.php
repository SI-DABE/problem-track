<?php

namespace Tests\Unit\Models;

use App\Models\Problem;
use App\Models\ProblemUserReinforce;
use App\Models\User;
use Tests\TestCase;

class ProblemUserReinforceTest extends TestCase
{
    private User $user;
    private Problem $problem;

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

        $this->problem = new Problem(['title' => 'Problem 1', 'user_id' => $this->user->id]);
        $this->problem->save();
    }

    public function test_save_problem_user_reinforce(): void
    {
        $problemUserReforce = new ProblemUserReinforce([
            'problem_id' => $this->problem->id,
            'user_id' => $this->user->id
        ]);

        $this->assertTrue($problemUserReforce->save());
        $this->assertCount(1, ProblemUserReinforce::all());
    }

    public function test_save_problem_user_reinforce_with_invalid_data(): void
    {
        $problemUserReforce = new ProblemUserReinforce([
            'problem_id' => $this->problem->id,
            'user_id' => $this->user->id
        ]);

        $problemUserReforce->save();

        $problemUserReforce = new ProblemUserReinforce([
            'problem_id' => $this->problem->id,
            'user_id' => $this->user->id
        ]);

        $this->assertFalse($problemUserReforce->save());
        $this->assertCount(1, ProblemUserReinforce::all());
    }
}
