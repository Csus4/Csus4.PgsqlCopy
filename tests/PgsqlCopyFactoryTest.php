<?php
declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use PHPUnit\Framework\TestCase;
use SplTempFileObject;

class PgsqlCopyFactoryTest extends TestCase
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

    public function testNewCsvReaderFromPath() : void
    {
        $actual = $this->pgsqlCopyFactory->newCsvReader(__FILE__, ['code', 'name', 'price']);
        $this->assertInstanceOf(CsvReaderInterface::class, $actual);
    }

    public function testNewCsvReaderFromFileObject() : void
    {
        $actual = $this->pgsqlCopyFactory->newCsvReader(new SplTempFileObject(), ['code', 'name', 'price']);
        $this->assertInstanceOf(CsvReaderInterface::class, $actual);
    }

    public function testNewInstance() : void
    {
        $actual = $this->pgsqlCopyFactory->newInstance(new FakePdo(), 'items', new CsvReader(new SplTempFileObject(), ['code', 'name', 'price']));
        $this->assertInstanceOf(PgsqlCopyInterface::class, $actual);
    }
}
