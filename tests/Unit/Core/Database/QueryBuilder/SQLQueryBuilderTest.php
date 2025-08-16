<?php

namespace Tests\Unit\Core\Database\QueryBuilder;

use Exception;
use PHPUnit\Framework\TestCase;
use Core\Database\QueryBuilder\SQLQueryBuilder;
use ReflectionClass;

class SQLQueryBuilderTest extends TestCase
{
    public function testQueryBuilderGeneratesCorrectSQLAndParams()
    {
        $builder = new SQLQueryBuilder();
        $sql = $builder
            ->select('users', ['name', 'email', 'password'])
            ->where('age', 18, '>')
            ->where('age', 30, '<')
            ->limit(10, 20)
            ->getSQL();
        $params = $builder->getParams();
        $expectedSql = "SELECT name, email, password FROM users WHERE age > :age_1 AND age < :age_2 LIMIT 10, 20;";
        $expectedParams = [':age_1' => 18, ':age_2' => 30];
        $this->assertEquals($expectedSql, $sql);
        $this->assertEquals($expectedParams, $params);
    }

    public function testWhereThrowsExceptionOnInvalidType()
    {
        $this->expectException(Exception::class);
        $builder = new SQLQueryBuilder();
        $reflection = new ReflectionClass($builder);
        $property = $reflection->getProperty('query');
        $property->setAccessible(true);
        $property->setValue($builder, (object)['type' => 'insert']);
        $builder->where('foo', 'bar');
    }

    public function testLimitThrowsExceptionOnInvalidType()
    {
        $this->expectException(Exception::class);
        $builder = new SQLQueryBuilder();
        $reflection = new ReflectionClass($builder);
        $property = $reflection->getProperty('query');
        $property->setAccessible(true);
        $property->setValue($builder, (object)['type' => 'update']);
        $builder->limit(1, 2);
    }

    public function testFluentInterface()
    {
        $builder = new SQLQueryBuilder();
        $result = $builder->select('users', ['id'])->where('id', 1);
        $this->assertInstanceOf(SQLQueryBuilder::class, $result);
    }

    public function testInsertAndUpdateSQLAndParams()
    {
        $builder = new SQLQueryBuilder();
        $sql = $builder->insert('users', ['name' => 'foo', 'email' => 'bar'])->getSQL();
        $params = $builder->getParams();
        $this->assertStringContainsString('INSERT INTO users (name, email) VALUES (:name_1, :email_2)', $sql);
        $this->assertEquals([':name_1' => 'foo', ':email_2' => 'bar'], $params);

        $builder = new SQLQueryBuilder();
        $sql = $builder->update('users', ['name' => 'foo', 'email' => 'bar'])->where('id', 10)->getSQL();
        $params = $builder->getParams();
        $this->assertStringContainsString('UPDATE users SET name = :name_1, email = :email_2 WHERE id = :id_3;', $sql);
        $this->assertEquals([':name_1' => 'foo', ':email_2' => 'bar', ':id_3' => 10], $params);
    }
}
