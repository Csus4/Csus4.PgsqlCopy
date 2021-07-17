<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use PDO;

interface PgsqlCopyInterface
{
    public function __invoke() : bool;

    public function init(PDO $pdo, string $table, CsvReaderInterface $csv) : void;
}
