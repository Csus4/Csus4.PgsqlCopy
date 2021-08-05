<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use PDO;
use SplFileObject;

interface PgsqlCopyFactoryInterface
{
    public function newInstance(PDO $pdo, string $table, CsvReaderInterface $csvReader) : PgsqlCopyInterface;

    /**
     * @param  SplFileObject|string  $file
     */
    public function newCsvReader(
        $file,
        int $headerOffset = 0,
        array $fields = [],
        string $delimiter = ',',
        string $nullAs = '\\\\N'
    ) : CsvReaderInterface;
}
