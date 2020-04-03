<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

interface PgsqlCopyInterface
{
    public function __invoke() : bool;

    public function init(string $table, CsvReaderInterface $csv) : void;
}
