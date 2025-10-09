<?php

namespace Tests\Unit\Core\Database\QueryBuilder;

use Core\Database\Database;
use Core\Database\QueryBuilder\SQLQueryBuilder;
use Tests\TestCase;

class LaravelCompatibilityTest extends TestCase
{
    protected $usesDatabase = true;
    private SQLQueryBuilder $builder;

    public function setUp(): void
    {
        parent::setUp();
        $this->builder = Database::table('users');
    }

    public function test_basic_select_query(): void
    {
        $sql = $this->builder
            ->selectColumns(['name', 'email'])
            ->getSQL();
        
        $this->assertStringContainsString('SELECT name, email FROM users', $sql);
    }

    public function test_where_methods(): void
    {
        $sql = $this->builder
            ->where('name', 'John')
            ->where('age', '>', 18)
            ->orWhere('status', 'active')
            ->getSQL();

        $this->assertStringContainsString('WHERE', $sql);
        $this->assertStringContainsString('OR', $sql);
    }

    public function test_where_in_methods(): void
    {
        $sql = $this->builder
            ->whereIn('id', [1, 2, 3])
            ->whereNotIn('status', ['banned', 'inactive'])
            ->getSQL();

        $this->assertStringContainsString('IN', $sql);
        $this->assertStringContainsString('NOT IN', $sql);
    }

    public function test_where_null_methods(): void
    {
        $sql = $this->builder
            ->whereNull('deleted_at')
            ->whereNotNull('email_verified_at')
            ->getSQL();

        $this->assertStringContainsString('IS NULL', $sql);
        $this->assertStringContainsString('IS NOT NULL', $sql);
    }

    public function test_where_between_methods(): void
    {
        $sql = $this->builder
            ->whereBetween('age', [18, 65])
            ->whereNotBetween('score', [0, 10])
            ->getSQL();

        $this->assertStringContainsString('BETWEEN', $sql);
        $this->assertStringContainsString('NOT BETWEEN', $sql);
    }

    public function test_where_date_method(): void
    {
        $sql = $this->builder
            ->whereDate('created_at', '2023-01-01')
            ->getSQL();

        $this->assertStringContainsString('DATE(', $sql);
    }

    public function test_ordering_methods(): void
    {
        $sql = $this->builder
            ->orderBy('name')
            ->orderByDesc('created_at')
            ->latest('updated_at')
            ->oldest('id')
            ->getSQL();

        $this->assertStringContainsString('ORDER BY', $sql);
        $this->assertStringContainsString('ASC', $sql);
        $this->assertStringContainsString('DESC', $sql);
    }

    public function test_limit_and_offset(): void
    {
        $sql = $this->builder
            ->limit(10)
            ->offset(20)
            ->getSQL();

        $this->assertStringContainsString('LIMIT 10', $sql);
        $this->assertStringContainsString('OFFSET 20', $sql);
    }

    public function test_distinct_query(): void
    {
        $sql = $this->builder
            ->distinct()
            ->selectColumns(['email'])
            ->getSQL();

        $this->assertStringContainsString('SELECT DISTINCT', $sql);
    }

    public function test_join_methods(): void
    {
        $sql = $this->builder
            ->join('problems', 'users.id', '=', 'problems.user_id')
            ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
            ->getSQL();

        $this->assertStringContainsString('INNER JOIN', $sql);
        $this->assertStringContainsString('LEFT JOIN', $sql);
    }

    public function test_conditional_when(): void
    {
        $condition = true;
        $sql = $this->builder
            ->when($condition, function($query) {
                $query->where('active', 1);
            })
            ->getSQL();

        $this->assertStringContainsString('active', $sql);
    }

    public function test_aggregate_methods(): void
    {
        // Test count - should work even with empty table
        $this->assertIsInt($this->builder->count());
        
        // Create a test user first to ensure we have data
        Database::table('users')->insertData([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'encrypted_password' => 'hashed_password'
        ]);
        
        // Now test aggregates - they should work with real data
        $this->assertIsNumeric($this->builder->max('id'));
        $this->assertIsNumeric($this->builder->min('id'));
        $this->assertIsNumeric($this->builder->avg('id'));
        $this->assertIsNumeric($this->builder->sum('id'));
    }

    public function test_existence_methods(): void
    {
        $this->assertIsBool($this->builder->exists());
        $this->assertIsBool($this->builder->doesntExist());
    }

    public function test_laravel_style_take_and_skip(): void
    {
        $sql = $this->builder
            ->take(5)
            ->skip(10)
            ->getSQL();

        $this->assertStringContainsString('LIMIT 5', $sql);
        $this->assertStringContainsString('OFFSET 10', $sql);
    }

    public function test_random_ordering(): void
    {
        $sql = $this->builder
            ->inRandomOrder()
            ->getSQL();

        $this->assertStringContainsString('RAND()', $sql);
    }

    public function test_parameter_binding(): void
    {
        $this->builder
            ->where('name', 'John')
            ->where('email', 'LIKE', '%@example.com')
            ->whereIn('status', ['active', 'pending']);

        $params = $this->builder->getParams();
        
        $this->assertNotEmpty($params);
        $this->assertArrayHasKey(':name_1', $params);
        $this->assertEquals('John', $params[':name_1']);
    }
}