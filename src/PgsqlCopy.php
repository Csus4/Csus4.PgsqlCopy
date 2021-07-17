<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use PDO;

final class PgsqlCopy implements PgsqlCopyInterface
{
    private PDO $pdo;
    private string $table;
    private CsvReaderInterface $csvReader;

    public function __construct(
        private ChunkBuilderInterface $chunkBuilder,
    ) {
    }

    public function __invoke() : bool
    {
        $delimiter = $this->csvReader->getDelimiter();
        $nullAs = $this->csvReader->getNullAs();
        $fields = $this->csvReader->getFieldsLine();

        foreach ($this->chunkBuilder->__invoke($this->csvReader) as $rows) {
            $result = $this->pdo->pgsqlCopyFromArray($this->table, $rows, $delimiter, $nullAs, $fields);
            if (! $result) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
        }
        return true;
    }

    public function init(PDO $pdo, string $table, CsvReaderInterface $csvReader) : void
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->csvReader = $csvReader;
    }
}
