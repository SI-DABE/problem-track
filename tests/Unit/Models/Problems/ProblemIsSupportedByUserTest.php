<?php

namespace Tests\Unit\Models\Problems;

use App\Models\Problem;
use App\Models\ProblemUserReinforce;
use App\Models\User;
use Tests\TestCase;

class ProblemIsSupportedByUserTest extends TestCase
{
    public function test_is_supported_by_user(): void
    {
        $user = new User([
            'name' => 'User 1',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        $problem = new Problem(['title' => 'Problem 1', 'user_id' => $user->id]);
        $problem->save();

        $problemTwo = new Problem(['title' => 'Problem 1', 'user_id' => $user->id]);
        $problemTwo->save();

        $problemUserReforce = new ProblemUserReinforce([
            'problem_id' => $problem->id,
            'user_id' => $user->id
        ]);
        $problemUserReforce->save();

        $this->assertTrue($problem->isSupportedByUser($user));
        $this->assertFalse($problemTwo->isSupportedByUser($user));
    }
}
