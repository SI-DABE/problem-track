<?php

namespace Database\Populate;

use App\Models\Problem;
use App\Models\User;

class ProblemsPopulate
{
    public static function populate()
    {
        $user = User::findBy(['email' => 'fulano@example.com']);

        $numberOfProblems = 100;

        for ($i = 0; $i < $numberOfProblems; $i++) {
            $problem = new Problem(['title' => 'Problem ' . $i, 'user_id' => $user->id]);
            $problem->save();
        }

        echo "Problems populated with $numberOfProblems registers\n";
    }
}
