<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use Generator;

interface ChunkBuilderInterface
{
    public function __invoke(iterable $rows) : Generator;
}
