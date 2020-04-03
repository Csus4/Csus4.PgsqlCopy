<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use SplFileObject;

final class PgsqlCopyFactory implements PgsqlCopyFactoryInterface
{
    /**
     * @var PgsqlCopyInterface
     */
    private $pgsqlCopy;

    /**
     * Factory constructor.
     */
    public function __construct(PgsqlCopyInterface $pgsqlCopy)
    {
        $this->pgsqlCopy = $pgsqlCopy;
    }

    public function newInstance(string $table, CsvReaderInterface $csvReader) : PgsqlCopyInterface
    {
        $pgsqlCopy = clone $this->pgsqlCopy;
        $pgsqlCopy->init($table, $csvReader);

        return $pgsqlCopy;
    }

    public function newCsvReader($file, string $delimiter = ',', string $nullAs = '\\\\N') : CsvReaderInterface
    {
        if (is_string($file)) {
            $file = new SplFileObject($file, 'r');
        }

        return new CsvReader($file, $delimiter, $nullAs);
    }
}
