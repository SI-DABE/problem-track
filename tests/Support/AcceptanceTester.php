<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\Support;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @method void amOnPage(string $url)
 * @method void fillField(string $field, string $value)
 * @method void click(string $button)
 * @method void see(string $text, string $selector = NULL)
 * @method void seeInCurrentUrl(string $url)
 *
 * @method void login(string $username, $password)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * Define custom actions here
     */
}
