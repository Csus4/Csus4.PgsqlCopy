<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use SplFileObject;

final class PgsqlCopyFactory implements PgsqlCopyFactoryInterface
{
    public function __construct(
        private PgsqlCopyInterface $pgsqlCopy
    ) {
    }

    public function newInstance(\PDO $pdo, string $table, CsvReaderInterface $csvReader) : PgsqlCopyInterface
    {
        $pgsqlCopy = clone $this->pgsqlCopy;
        $pgsqlCopy->init($pdo, $table, $csvReader);

        return $pgsqlCopy;
    }

    public function newCsvReader(
        $file,
        int $headerOffset = 0,
        array $fields = [],
        array $extras = [],
        string $delimiter = ',',
        string $nullAs = '\\\\N'
    ) : CsvReaderInterface {
        if (is_string($file)) {
            $file = new SplFileObject($file, 'r');
        }
        return new CsvReader($file, $headerOffset, $fields, $extras, $delimiter, $nullAs);
    }
}
