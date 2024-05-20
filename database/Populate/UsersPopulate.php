<?php

namespace Database\Populate;

use App\Models\User;

class UsersPopulate
{
    public static function populate()
    {
        $user = new User(
            name: 'Fulano',
            email: 'fulano@example.com',
            password: '123456',
            password_confirmation: '123456'
        );
        $user->save();

        $numberOfUsers = 10;

        for ($i = 1; $i < $numberOfUsers; $i++) {
            $user = new User(
                name: 'Fulano ' . $i,
                email: 'fulano' . $i . '@example.com',
                password: '123456',
                password_confirmation: '123456'
            );
            $user->save();
        }


        echo "Users populated with $numberOfUsers registers\n";
    }
}
