<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use Csus4\PgsqlCopy\Exception\CsvRowCountException;
use Csus4\PgsqlCopy\Exception\CsvRowException;
use Csus4\PgsqlCopy\Exception\FieldException;
use SplFileObject;

final class CsvReader implements CsvReaderInterface
{
    private int $headerOffset = 0;
    private string $delimiter;
    private array $header = [];
    private array $fieldsFlipped = [];
    /** @var callable */
    private $filter;

    public function __construct(
        private SplFileObject $file,
        private array $fields,
        private string $nullAs = '\\\\N'
    ) {
        $this->file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);
        $this->delimiter = $this->file->getCsvControl()[0];
    }

    public function setHeaderOffset(int $offset) : void
    {
        $this->headerOffset = $offset;
        $this->file->seek($this->headerOffset);
        $this->header = (array) $this->file->current();
    }

    public function setFilter(callable $filter) : void
    {
        $this->filter = $filter;
    }

    public function getFieldsLine() : string
    {
        return implode($this->delimiter, $this->fields) . PHP_EOL;
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

        if ($diff = array_diff($this->fields, $this->header)) {
            $message = sprintf('CSVファイルに必要な列がありません(%s)。', implode(',', $diff));
            throw new FieldException($message);
        }

        $filter = $this->filter;
        foreach ($this->file as $i => $row) {
            if ($i <= $this->headerOffset) {
                continue;
            }
            if (count($row) !== count($this->header)) {
                $message = sprintf('%d行目: ヘッダ行と列数が違います。', $i);
                throw new CsvRowCountException($message);
            }
            $row = $this->onlyTargetFields($row);
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
        return array_combine($this->fields, array_values($targets));
    }

    private function format(array $row) : string
    {
        $row = array_map(function ($value) {
            return str_replace(["\r", "\n", "\t"], ['\\r', '\\n', '\\t'], $value);
        }, $row);

        return implode($this->delimiter, $row) . PHP_EOL;
    }
}
