<?php

namespace Tests\Unit\Models\Users;

use App\Models\Problem;
use App\Models\ProblemUserReinforce;
use App\Models\User;
use Tests\TestCase;

class UserReinforceProblemTest extends TestCase
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

    public function test_count_reinforced_problems(): void
    {
        $problemUserReforce = new ProblemUserReinforce([
            'problem_id' => $this->problem->id,
            'user_id' => $this->user->id
        ]);
        $problemUserReforce->save();

        $this->assertEquals(1, $this->user->reinforcedProblems()->count());
    }

    public function test_get_all_reinforced_problems(): void
    {
        $problemUserReforce = new ProblemUserReinforce([
            'problem_id' => $this->problem->id,
            'user_id' => $this->user->id
        ]);
        $problemUserReforce->save();

        $user = new User([
            'name' => 'User 2',
            'email' => 'fulano2@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        $otherProblem = new Problem(['title' => 'Problem 1', 'user_id' => $user->id]);
        $otherProblem->save();

        $problemUserReforceByOtherUser = new ProblemUserReinforce([
            'problem_id' => $otherProblem->id,
            'user_id' => $user->id
        ]);
        $problemUserReforceByOtherUser->save();

        $this->assertCount(2, ProblemUserReinforce::all());
        $this->assertEquals(1, $this->user->reinforcedProblems()->count());

        $this->assertTrue(in_array($this->problem, $this->user->reinforced_problems));
        $this->assertFalse(in_array($otherProblem, $this->user->reinforced_problems));
    }
}
