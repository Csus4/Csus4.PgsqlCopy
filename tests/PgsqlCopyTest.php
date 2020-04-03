<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use PHPUnit\Framework\TestCase;

class PgsqlCopyTest extends TestCase
{
    /**
     * @var PgsqlCopyFactory
     */
    protected $pgsqlCopyFactory;

    protected function setUp() : void
    {
        $pgsqlCopy = new PgsqlCopy(new ChunkBuilder(), new FakePdo());
        $this->pgsqlCopyFactory = new PgsqlCopyFactory($pgsqlCopy);
    }

    public function testInvoke() : void
    {
        $csvReader = $this->pgsqlCopyFactory->newCsvReader(__DIR__ . '/var/data/header_0.csv');
        $this->assertInstanceOf(CsvReader::class, $csvReader);

        $pgsqlCopy = $this->pgsqlCopyFactory->newInstance('items', $csvReader);
        $this->assertInstanceOf(PgsqlCopy::class, $pgsqlCopy);

        $actual = $pgsqlCopy->__invoke();
        $this->assertTrue($actual);
    }
}
