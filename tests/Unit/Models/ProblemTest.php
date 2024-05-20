<?php

namespace Tests\Unit\Models;

use App\Models\Problem;
use Tests\TestCase;

class ProblemTest extends TestCase
{
    public function test_should_create_new_problem(): void
    {
        $problem = new Problem(title: 'Problem 1');

        $this->assertTrue($problem->save());
        $this->assertCount(1, Problem::all());
    }

    public function test_all_should_return_all_problems(): void
    {
        $problems[] = new Problem(title: 'Problem 1');
        $problems[] = new Problem(title: 'Problem 2');

        foreach ($problems as $problem) {
            $problem->save();
        }

        $all = Problem::all();
        $this->assertCount(2, $all);
        $this->assertEquals($problems, $all);
    }

    public function test_destroy_should_remove_the_problem(): void
    {
        $problem1 = new Problem(title: 'Problem 1');
        $problem2 = new Problem(title: 'Problem 2');

        $problem1->save();
        $problem2->save();
        $problem2->destroy();

        $this->assertCount(1, Problem::all());
    }

    public function test_set_title(): void
    {
        $problem = new Problem(title: 'Problem 1');
        $this->assertEquals('Problem 1', $problem->getTitle());
    }

    public function test_set_id(): void
    {
        $problem = new Problem(title: 'Problem Test');
        $problem->setId(7);

        $this->assertEquals(7, $problem->getId());
    }

    public function test_errors_should_return_title_error(): void
    {
        $problem = new Problem(title: 'Problem 1');
        $problem->setTitle('');

        $this->assertFalse($problem->isValid());
        $this->assertFalse($problem->save());
        $this->assertFalse($problem->hasErrors());

        $this->assertEquals('não pode ser vazio!', $problem->errors('title'));
    }

    public function test_find_by_id_should_return_the_problem(): void
    {
        $problem2 = new Problem(title: 'Problem 2');
        $problem1 = new Problem(title: 'Problem 1');
        $problem3 = new Problem(title: 'Problem 3');

        $problem1->save();
        $problem2->save();
        $problem3->save();

        $this->assertEquals($problem1, Problem::findById($problem1->getId()));
    }

    public function test_find_by_id_should_return_null(): void
    {
        $problem = new Problem(title: 'Problem 1');
        $problem->save();

        $this->assertNull(Problem::findById(7));
    }
}
