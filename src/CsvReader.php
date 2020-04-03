<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use SplFileObject;

final class CsvReader implements CsvReaderInterface
{
    /**
     * @var SplFileObject
     */
    private $file;

    /**
     * @var string
     */
    private $delimiter = ',';

    /**
     * @var string
     */
    private $nullAs = '\\\\N';

    /**
     * @var int
     */
    private $offset = -1;

    /**
     * @var array
     */
    private $header = [];

    /**
     * @var array
     */
    private $fixed = [];

    /**
     * CsvReader constructor.
     */
    public function __construct(SplFileObject $file, string $delimiter = ',', string $nullAs = '\\\\N')
    {
        $this->file = $file;
        $this->file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);
        $this->file->setCsvControl($delimiter);
        $this->delimiter = $delimiter;
        $this->nullAs = $nullAs;
    }

    public function setHeaderOffset(int $offset) : void
    {
        $this->offset = $offset;
        $this->file->seek($this->offset);
        $this->header = (array) $this->file->current();
    }

    public function setHeader(array $header) : void
    {
        $this->header = $header;
    }

    public function setFixed(array $fixed) : void
    {
        $this->fixed = $fixed;
    }

    public function getHeader() : string
    {
        $header = array_merge($this->header, array_keys($this->fixed));

        return count($header) ? implode($this->delimiter, $header) . PHP_EOL : '';
    }

    public function getDelimiter() : string
    {
        return $this->delimiter;
    }

    public function getNullAs() : string
    {
        return $this->nullAs;
    }

    public function getIterator()
    {
        $pos = $this->offset + 1;
        $this->file->seek($pos);
        while (! $this->file->eof()) {
            $row = array_merge((array) $this->file->current(), $this->fixed);
            yield $this->format($row);
            $this->file->next();
        }
    }

    private function format(array $row) : string
    {
        $row = array_map(function ($value) {
            return str_replace(["\r", "\n", "\t"], ['\\r', '\\n', '\\t'], $value);
        }, $row);

        return implode($this->delimiter, $row) . PHP_EOL;
    }
}
