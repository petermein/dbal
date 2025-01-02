<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Tests\Functional\Driver\Mysqli;

use Doctrine\DBAL\Driver\Mysqli\Statement;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Statement as WrapperStatement;
use Doctrine\DBAL\Tests\FunctionalTestCase;
use Doctrine\DBAL\Tests\TestUtil;
use Error;
use mysqli_sql_exception;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use ReflectionProperty;

#[RequiresPhpExtension('mysqli')]
class StatementTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (TestUtil::isDriverOneOf('mysqli')) {
            return;
        }

        self::markTestSkipped('This test requires the mysqli driver.');
    }

    public function testStatementsAreDeallocatedProperly(): void
    {
        $statement = $this->connection->prepare('SELECT 1');

        $property = new ReflectionProperty(WrapperStatement::class, 'stmt');
        $property->setAccessible(true);

        $driverStatement = $property->getValue($statement);

        $mysqliSProperty = new ReflectionProperty(Statement::class, 'stmt');
        $mysqliSProperty->setAccessible(true);

        $mysqliStatement = $mysqliSProperty->getValue($driverStatement);

        unset($statement, $driverStatement);

        $this->expectException(Error::class);
        $this->expectExceptionMessage('mysqli_stmt object is already closed');

        $mysqliStatement->execute();
    }
}
