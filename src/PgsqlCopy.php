<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use PDO;

final class PgsqlCopy implements PgsqlCopyInterface
{
    /**
     * @var ChunkBuilderInterface
     */
    private $chunkBuilder;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $table;

    /**
     * @var CsvReaderInterface
     */
    private $csvReader;

    /**
     * PgsqlCopy constructor.
     */
    public function __construct(ChunkBuilderInterface $chunkBuilder, PDO $pdo)
    {
        $this->chunkBuilder = $chunkBuilder;
        $this->pdo = $pdo;
    }

    public function __invoke() : bool
    {
        $header = $this->csvReader->getHeader();
        $delimiter = $this->csvReader->getDelimiter();
        $nullAs = $this->csvReader->getNullAs();

        foreach ($this->chunkBuilder->__invoke($this->csvReader) as $rows) {
            $result = $this->pdo->pgsqlCopyFromArray($this->table, $rows, $delimiter, $nullAs, $header);
            if (! $result) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
        }

        return true;
    }

    public function init(string $table, CsvReaderInterface $csvReader) : void
    {
        $this->table = $table;
        $this->csvReader = $csvReader;
    }
}
