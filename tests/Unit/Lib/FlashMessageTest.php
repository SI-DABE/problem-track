<?php

namespace Tests\Unit\Lib;

use Lib\FlashMessage;
use PHPUnit\Framework\TestCase;

class FlashMessageTest extends TestCase
{
    public function test_success(): void
    {
        FlashMessage::success('Success message');
        $flash = FlashMessage::get();

        $this->assertArrayHasKey('success', $flash);
        $this->assertEquals('Success message', $flash['success']);
    }

    public function test_danger(): void
    {
        FlashMessage::danger('Danger message');
        $flash = FlashMessage::get();

        $this->assertArrayHasKey('danger', $flash);
        $this->assertEquals('Danger message', $flash['danger']);
    }

    public function test_get(): void
    {
        FlashMessage::success('Success message');
        FlashMessage::danger('Danger message');

        $flash = FlashMessage::get();
        $this->assertEmpty(FlashMessage::get());

        $this->assertArrayHasKey('success', $flash);
        $this->assertEquals('Success message', $flash['success']);

        $this->assertArrayHasKey('danger', $flash);
        $this->assertEquals('Danger message', $flash['danger']);
    }
}
