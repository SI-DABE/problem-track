<?php

namespace Tests\Unit\Core\Database\QueryBuilder;

use Exception;
use PHPUnit\Framework\TestCase;
use Core\Database\QueryBuilder\SQLQueryBuilder;

class SQLQueryBuilderTest extends TestCase
{
    public function testTableMethodSetsTableName()
    {
        $builder = new SQLQueryBuilder();
        $result = $builder->table('users');
        
        $this->assertInstanceOf(SQLQueryBuilder::class, $result);
        $this->assertStringContainsString('users', $builder->getSQL());
    }

    public function testSelectColumnsMethod()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')->selectColumns(['name', 'email']);
        
        $sql = $builder->getSQL();
        $this->assertStringContainsString('SELECT name, email', $sql);
        $this->assertStringContainsString('FROM users', $sql);
    }

    public function testWhereMethodGeneratesCorrectSQL()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')->where('id', 1);
        
        $sql = $builder->getSQL();
        $params = $builder->getParams();
        
        $this->assertStringContainsString('WHERE id =', $sql);
        $this->assertArrayHasKey(':id_1', $params);
        $this->assertEquals(1, $params[':id_1']);
    }

    public function testWhereWithOperator()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')->where('age', '>', 18);
        
        $sql = $builder->getSQL();
        $params = $builder->getParams();
        
        $this->assertStringContainsString('WHERE age >', $sql);
        $this->assertEquals(18, $params[':age_1']);
    }

    public function testOrWhereMethod()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')
            ->where('age', '>', 18)
            ->orWhere('name', 'admin');
        
        $sql = $builder->getSQL();
        
        $this->assertStringContainsString('WHERE age >', $sql);
        $this->assertStringContainsString('OR name =', $sql);
    }

    public function testOrderByMethod()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')->orderBy('name', 'DESC');
        
        $sql = $builder->getSQL();
        $this->assertStringContainsString('ORDER BY name DESC', $sql);
    }

    public function testTakeMethod()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')->take(10);
        
        $sql = $builder->getSQL();
        $this->assertStringContainsString('LIMIT 10', $sql);
    }

    public function testSkipMethod()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')->skip(5);
        
        $sql = $builder->getSQL();
        $this->assertStringContainsString('OFFSET 5', $sql);
    }

    public function testLatestMethod()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')->latest();
        
        $sql = $builder->getSQL();
        $this->assertStringContainsString('ORDER BY created_at DESC', $sql);
    }

    public function testOldestMethod()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')->oldest();
        
        $sql = $builder->getSQL();
        $this->assertStringContainsString('ORDER BY created_at ASC', $sql);
    }

    public function testJoinMethod()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')->join('profiles', 'users.id', '=', 'profiles.user_id');
        
        $sql = $builder->getSQL();
        $this->assertStringContainsString('INNER JOIN profiles ON users.id = profiles.user_id', $sql);
    }

    public function testLeftJoinMethod()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')->leftJoin('profiles', 'users.id', '=', 'profiles.user_id');
        
        $sql = $builder->getSQL();
        $this->assertStringContainsString('LEFT JOIN profiles ON users.id = profiles.user_id', $sql);
    }

    public function testWhereInMethod()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')->whereIn('id', [1, 2, 3]);
        
        $sql = $builder->getSQL();
        $params = $builder->getParams();
        
        $this->assertStringContainsString('WHERE id IN', $sql);
        $this->assertCount(3, $params);
    }

    public function testComplexQueryChaining()
    {
        $builder = new SQLQueryBuilder();
        $sql = $builder->table('users')
            ->selectColumns(['name', 'email'])
            ->where('active', 1)
            ->where('age', '>', 18)
            ->orderBy('name')
            ->take(10)
            ->getSQL();
        
        $this->assertStringContainsString('SELECT name, email', $sql);
        $this->assertStringContainsString('FROM users', $sql);
        $this->assertStringContainsString('WHERE active =', $sql);
        $this->assertStringContainsString('AND age >', $sql);
        $this->assertStringContainsString('ORDER BY name ASC', $sql);
        $this->assertStringContainsString('LIMIT 10', $sql);
    }

    public function testFluentInterface()
    {
        $builder = new SQLQueryBuilder();
        $result = $builder->table('users')
            ->selectColumns(['name'])
            ->where('id', 1)
            ->orderBy('name');
        
        $this->assertInstanceOf(SQLQueryBuilder::class, $result);
    }

    public function testMethodsExist()
    {
        $builder = new SQLQueryBuilder();
        
        $this->assertTrue(method_exists($builder, 'table'));
        $this->assertTrue(method_exists($builder, 'selectColumns'));
        $this->assertTrue(method_exists($builder, 'where'));
        $this->assertTrue(method_exists($builder, 'orWhere'));
        $this->assertTrue(method_exists($builder, 'whereIn'));
        $this->assertTrue(method_exists($builder, 'join'));
        $this->assertTrue(method_exists($builder, 'leftJoin'));
        $this->assertTrue(method_exists($builder, 'orderBy'));
        $this->assertTrue(method_exists($builder, 'latest'));
        $this->assertTrue(method_exists($builder, 'oldest'));
        $this->assertTrue(method_exists($builder, 'take'));
        $this->assertTrue(method_exists($builder, 'skip'));
        $this->assertTrue(method_exists($builder, 'insertData'));
        $this->assertTrue(method_exists($builder, 'updateData'));
        $this->assertTrue(method_exists($builder, 'deleteData'));
    }
}