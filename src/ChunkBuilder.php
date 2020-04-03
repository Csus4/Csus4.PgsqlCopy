<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use Generator;

final class ChunkBuilder implements ChunkBuilderInterface
{
    /**
     * @var int
     */
    private $limit;

    /**
     * ChunkBuilder constructor.
     */
    public function __construct(int $limit = 2000)
    {
        $this->limit = $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(iterable $rows) : Generator
    {
        $chunk = [];
        foreach ($rows as $row) {
            $chunk[] = $row;
            if (count($chunk) === $this->limit) {
                yield $rows;
                $chunk = [];
            }
        }
        if (! empty($chunk)) {
            yield $chunk;
        }
    }
}
