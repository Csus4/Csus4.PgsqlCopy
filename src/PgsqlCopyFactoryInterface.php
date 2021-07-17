<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use SplFileObject;

interface PgsqlCopyFactoryInterface
{
    public function newInstance(string $table, CsvReaderInterface $csvReader) : PgsqlCopyInterface;

    /**
     * @param SplFileObject|string $file
     */
    public function newCsvReader(
        $file,
        int $headerOffset = 0,
        array $fields = [],
        array $extras = [],
        string $delimiter = ',',
        string $nullAs = '\\\\N'
    ) : CsvReaderInterface;
}
