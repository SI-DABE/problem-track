<?php

namespace Tests\Unit\Core\Database;

use PHPUnit\Framework\TestCase;
use Core\Database\QueryBuilder\SQLQueryBuilder;

class DocumentationValidationTest extends TestCase
{
    public function testLaravelStyleMethodsMatchDocumentation()
    {
        $builder = new SQLQueryBuilder();
        
        $sql = $builder->table('users')
            ->selectColumns(['name', 'email'])
            ->where('active', 1)
            ->where('age', '>', 18)
            ->orWhere('role', 'admin')
            ->whereIn('status', ['active', 'pending'])
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->leftJoin('settings', 'users.id', '=', 'settings.user_id')
            ->orderBy('created_at', 'DESC')
            ->take(10)
            ->skip(20)
            ->getSQL();
        
        $this->assertStringContainsString('SELECT name, email', $sql);
        $this->assertStringContainsString('FROM users', $sql);
        $this->assertStringContainsString('WHERE active =', $sql);
        $this->assertStringContainsString('AND age >', $sql);
        $this->assertStringContainsString('OR role =', $sql);
        $this->assertStringContainsString('AND status IN', $sql);
        $this->assertStringContainsString('INNER JOIN profiles', $sql);
        $this->assertStringContainsString('LEFT JOIN settings', $sql);
        $this->assertStringContainsString('ORDER BY created_at DESC', $sql);
        $this->assertStringContainsString('LIMIT 10', $sql);
        $this->assertStringContainsString('OFFSET 20', $sql);
    }

    public function testLatestAndOldestMethods()
    {
        $builder1 = new SQLQueryBuilder();
        $sql1 = $builder1->table('posts')->latest()->getSQL();
        $this->assertStringContainsString('ORDER BY created_at DESC', $sql1);
        
        $builder2 = new SQLQueryBuilder();
        $sql2 = $builder2->table('posts')->oldest()->getSQL();
        $this->assertStringContainsString('ORDER BY created_at ASC', $sql2);
        
        $builder3 = new SQLQueryBuilder();
        $sql3 = $builder3->table('posts')->latest('updated_at')->getSQL();
        $this->assertStringContainsString('ORDER BY updated_at DESC', $sql3);
    }

    public function testAllDocumentedMethodsExist()
    {
        $builder = new SQLQueryBuilder();
        $documentedMethods = [
            'table', 'selectColumns', 'where', 'orWhere', 'whereIn',
            'join', 'leftJoin', 'orderBy', 'latest', 'oldest',
            'take', 'skip', 'get', 'first', 'find', 'count',
            'insertData', 'updateData', 'deleteData'
        ];
        
        foreach ($documentedMethods as $method) {
            $this->assertTrue(
                method_exists($builder, $method),
                "Method $method is documented but does not exist"
            );
        }
    }

    public function testFluentInterfaceWorksAsDocumented()
    {
        $builder = new SQLQueryBuilder();
        $result = $builder
            ->table('users')
            ->selectColumns(['id', 'name'])
            ->where('active', 1)
            ->orderBy('name');
        
        $this->assertInstanceOf(SQLQueryBuilder::class, $result);
        $this->assertStringContainsString('SELECT id, name FROM users', $result->getSQL());
    }

    public function testParameterBindingWorksCorrectly()
    {
        $builder = new SQLQueryBuilder();
        $builder->table('users')
            ->where('name', 'John')
            ->where('age', '>', 25)
            ->whereIn('role', ['admin', 'user']);
        
        $params = $builder->getParams();
        
        $this->assertArrayHasKey(':name_1', $params);
        $this->assertArrayHasKey(':age_2', $params);
        $this->assertEquals('John', $params[':name_1']);
        $this->assertEquals(25, $params[':age_2']);
        $this->assertGreaterThanOrEqual(4, count($params));
    }

    public function testComplexJoinSyntaxFromDocumentation()
    {
        $builder = new SQLQueryBuilder();
        $sql = $builder->table('users')
            ->join('posts', 'users.id', '=', 'posts.user_id')
            ->leftJoin('comments', 'posts.id', '=', 'comments.post_id')
            ->getSQL();
        
        $this->assertStringContainsString('INNER JOIN posts ON users.id = posts.user_id', $sql);
        $this->assertStringContainsString('LEFT JOIN comments ON posts.id = comments.post_id', $sql);
    }
}