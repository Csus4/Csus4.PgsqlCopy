<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use IteratorAggregate;

interface CsvReaderInterface extends IteratorAggregate
{
    public function setHeaderOffset(int $offset) : void;

    /**
     * @param string[] $header
     */
    public function setHeader(array $header) : void;

    /**
     * @param string[] $fixed
     */
    public function setFixed(array $fixed) : void;

    public function getHeader() : string;

    public function getDelimiter() : string;

    public function getNullAs() : string;
}
