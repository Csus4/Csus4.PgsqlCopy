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
    public function newCsvReader($file, string $delimiter, string $nullAs) : CsvReaderInterface;
}
