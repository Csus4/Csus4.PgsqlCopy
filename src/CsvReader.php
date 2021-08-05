<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use Csus4\PgsqlCopy\Exception\CsvRowCountException;
use Csus4\PgsqlCopy\Exception\CsvRowException;
use SplFileObject;

final class CsvReader implements CsvReaderInterface
{
    private int $headerOffset = 0;
    private array $header = [];
    private array $fieldsFlipped = [];
    /** @var callable */
    private $filter;

    public function __construct(
        private SplFileObject $file,
        private array $fields = [],
        private string $delimiter = ',',
        private string $nullAs = '\\\\N'
    ) {
        $this->file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);
        $this->file->setCsvControl($this->delimiter);
    }

    public function setHeaderOffset(int $offset) : void
    {
        $this->headerOffset = $offset;
    }

    public function setFilter(callable $filter) : void
    {
        $this->filter = $filter;
    }

    public function getFieldsLine() : string
    {
        $this->file->seek($this->headerOffset);
        $this->header = (array) $this->file->current();
        if (!empty($this->fields)) {
            return implode($this->delimiter, $this->fields) . PHP_EOL;
        }
        return implode($this->delimiter, $this->header) . PHP_EOL;
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
        $this->file->seek($this->headerOffset);
        $this->header = (array) $this->file->current();
        $this->fieldsFlipped = array_flip($this->fields);

        $filter = $this->filter;
        foreach ($this->file as $i => $row) {
            if ($i <= $this->headerOffset) {
                continue;
            }
            if (!empty($this->fields)) {
                $row = $this->onlyTargetFields($row);
                if (count($row) !== count($this->fields)) {
                    $message = sprintf('%d行目: ヘッダ行と列数が違います。', $i);
                    throw new CsvRowCountException($message);
                }
            }
            if ($filter) {
                $messages = $filter($row);
                if (!empty($messages)) {
                    $message = sprintf('%d行目: %s', $i, implode("\n", $messages));
                    throw new CsvRowException($message);
                }
            }
            yield $this->format($row);
        }
    }

    private function onlyTargetFields(array $row) : array
    {
        $targets = [];
        foreach (array_combine($this->header, $row) as $key => $value) {
            if (array_key_exists($key, $this->fieldsFlipped)) {
                $index = $this->fieldsFlipped[$key];
                $targets[$index] = $value;
            }
        }
        ksort($targets);
        return array_values($targets);
    }

    private function format(array $row) : string
    {
        $row = array_map(function ($value) {
            return str_replace(["\r", "\n", "\t"], ['\\r', '\\n', '\\t'], $value);
        }, $row);

        return implode($this->delimiter, $row) . PHP_EOL;
    }
}
