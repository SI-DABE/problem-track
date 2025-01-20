<?php

namespace Tests\Browser\Problems;

use App\Models\Problem;
use App\Models\User;
use Facebook\WebDriver\WebDriverBy;
use Tests\DuskTestCase;

class SeeCurrentUserProblemsTest extends DuskTestCase
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
    }

    public function test_see_current_user_problems(): void
    {
        $problem = new Problem(['title' => 'Problem 1', 'user_id' => $this->user->id]);
        $problem->save();

        $this->login($this->user);

        $this->browser->get('http://web_test/problems');

        $tableBodyFirstTr = $this->browser->findElements(WebDriverBy::cssSelector('table tbody tr:nth-child(1)'));
        $this->assertEquals("#1 Problem 1", $tableBodyFirstTr[0]->getText());
    }

    private function login(User $user): void
    {
        $this->browser->get('http://web_test/login');
        $this->assertEquals('Rastreador de Problemas', $this->browser->getTitle());

        $emailField = $this->browser->findElement(WebDriverBy::id('user_email'));
        $emailField->sendKeys($this->user->email);

        $passwordField = $this->browser->findElement(WebDriverBy::id('user_password'));
        $passwordField->sendKeys('123456');

        $loginButton = $this->browser->findElement(WebDriverBy::cssSelector('input[type="submit"]'));
        $loginButton->click();
    }
}
