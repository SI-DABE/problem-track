<?php

namespace Tests\Acceptance\Problems;

use App\Models\Problem;
use App\Models\User;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class ProblemCest extends BaseAcceptanceCest
{
    public function seeMyProblems(AcceptanceTester $page): void
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

        $page->login($user->email, $user->password);

        $page->amOnPage('/problems');

        $tableSelector = 'table';

        $page->see('#1', '//table//tr[1]//td[1]');
        $page->see('Problem 1', '//table//tr[1]//td[2]');
    }
}
