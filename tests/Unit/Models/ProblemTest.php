<?php

namespace Tests\Unit\Models;

use App\Models\Problem;
use Lib\Paginator;
use Tests\TestCase;

class ProblemTest extends TestCase
{
    public function test_can_set_title(): void
    {
        $problem = new Problem(title: 'Problem 1');

        $this->assertEquals('Problem 1', $problem->getTitle());
    }

    public function test_should_create_new_problem(): void
    {
        $problem = new Problem(title: 'Problem 1');

        $this->assertTrue($problem->save());
        $this->assertCount(1, Problem::all());
    }

    public function test_paginate_should_return_a_paginator(): void
    {
        $this->assertInstanceOf(Paginator::class, Problem::paginate());
    }
}
