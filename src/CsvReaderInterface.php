<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use IteratorAggregate;

interface CsvReaderInterface extends IteratorAggregate
{
    public function getFieldsLine() : string;

    public function getDelimiter() : string;

    public function getNullAs() : string;
}
