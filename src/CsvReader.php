<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use Csus4\PgsqlCopy\Exception\CsvRowException;
use SplFileObject;

final class CsvReader implements CsvReaderInterface
{
    private array $header = [];
    private array $fieldsFlipped = [];
    /** @var callable */
    private $filter;

    public function __construct(
        private SplFileObject $file,
        private int $headerOffset = 0,
        private array $fields = [],
        private array $extras = [],
        private string $delimiter = ',',
        private string $nullAs = '\\\\N'
    ) {
        $this->file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);
        $this->file->setCsvControl($delimiter);
        $this->file->seek($headerOffset);
        $this->header = array_merge((array) $this->file->current(), array_keys($extras));
        $this->fieldsFlipped = array_flip($fields);
    }

    public function setFilter(callable $filter) : void
    {
        $this->filter = $filter;
    }

    public function getFieldsLine() : string
    {
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
        $filter = $this->filter;
        foreach ($this->file as $i => $row) {
            if ($i <= $this->headerOffset) {
                continue;
            }
            $row = array_merge((array) $row, $this->extras);
            if (!empty($this->fields)) {
                $row = $this->onlyTargetFields($row);
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
