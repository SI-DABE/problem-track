<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverBy;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    public function test_successfully_login(): void
    {
        $this->browser->get('http://web/login');
        $this->assertEquals('Rastreador de Problemas', $this->browser->getTitle());

        $emailField = $this->browser->findElement(WebDriverBy::id('user_email'));
        $emailField->sendKeys('fulano@example.com');

        $passwordField = $this->browser->findElement(WebDriverBy::id('user_password'));
        $passwordField->sendKeys('123456');

        $loginButton = $this->browser->findElement(WebDriverBy::cssSelector('input[type="submit"]'));
        $loginButton->click();

        $this->browser->wait(5);
        $this->assertEquals('http://web/problems', $this->browser->getCurrentURL());
    }
}
