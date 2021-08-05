<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use PHPUnit\Framework\TestCase;
use SplFileObject;
use SplTempFileObject;

class PgsqlCopyTest extends TestCase
{
    /**
     * @var PgsqlCopyFactory
     */
    protected $pgsqlCopyFactory;

    protected function setUp() : void
    {
        $pgsqlCopy = new PgsqlCopy(new ChunkBuilder());
        $this->pgsqlCopyFactory = new PgsqlCopyFactory($pgsqlCopy);
    }

    public function testInvoke() : void
    {
        $temp = new SplTempFileObject();
        $temp->setFlags(SplFileObject::READ_AHEAD|SplFileObject::READ_CSV);
        $temp->fputcsv(['code', 'name', 'price']);
        $temp->fputcsv(['000100', 'あいうえお', '1000']);

        $csvReader = $this->pgsqlCopyFactory->newCsvReader($temp, ['code', 'name', 'price']);
        $this->assertInstanceOf(CsvReader::class, $csvReader);

        $pgsqlCopy = $this->pgsqlCopyFactory->newInstance(new FakePdo(), 'items', $csvReader);
        $this->assertInstanceOf(PgsqlCopy::class, $pgsqlCopy);

        $actual = $pgsqlCopy->__invoke();
        $this->assertTrue($actual);
    }
}
