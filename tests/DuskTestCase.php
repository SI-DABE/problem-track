<?php

namespace Tests;

use Core\Database\Database;
use Core\Env\EnvLoader;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;

abstract class DuskTestCase extends TestCase
{
    /**
     * @var RemoteWebDriver
     */
    protected $browser;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        Database::create();
        Database::migrate();
        parent::setUp();

        // Create a RemoteWebDriver instance
        $this->browser = RemoteWebDriver::create(
            'http://selenium:4444/wd/hub', // URL of the ChromeDriver or Selenium server
            DesiredCapabilities::chrome()
        );
    }

    /**
     * Tear down the test environment.
     */
    protected function tearDown(): void
    {
        Database::drop();

        $this->browser->quit();

        parent::tearDown();
    }
}
