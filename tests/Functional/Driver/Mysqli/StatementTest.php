<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Tests\Functional\Driver\Mysqli;

use Doctrine\DBAL\Driver\Mysqli\Statement;
use Doctrine\DBAL\Statement as WrapperStatement;
use Doctrine\DBAL\Tests\FunctionalTestCase;
use Doctrine\DBAL\Tests\TestUtil;
use Error;
use ReflectionProperty;

use const PHP_VERSION_ID;

/** @requires extension mysqli */
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

        $mysqliProperty = new ReflectionProperty(Statement::class, 'stmt');
        $mysqliProperty->setAccessible(true);

        $mysqliStatement = $mysqliProperty->getValue($driverStatement);

        unset($statement, $driverStatement);

        $this->expectException(Error::class);

        if (PHP_VERSION_ID < 80000) {
            $this->expectExceptionMessage('Couldn\'t fetch mysqli_stmt');
        } else {
            $this->expectExceptionMessage('mysqli_stmt object is already closed');
        }

        $mysqliStatement->execute();
    }
}
