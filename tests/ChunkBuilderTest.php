<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use PHPUnit\Framework\TestCase;

class ChunkBuilderTest extends TestCase
{
    public function invokeDataProvider() : array
    {
        return [
            [1, 100],
            [2, 50],
            [3, 34],
            [4, 25],
            [5, 20],
            [6, 17],
            [10, 10],
            [20, 5],
            [30, 4],
            [40, 3],
            [50, 2],
            [100, 1],
        ];
    }

    /**
     * @dataProvider invokeDataProvider
     */
    public function testInvoke(int $limit, int $expect) : void
    {
        $rows = range(1, 100);
        $chunkBuilder = new ChunkBuilder($limit);
        $chunks = [];
        foreach ($chunkBuilder->__invoke($rows) as $chunk) {
            $chunks[] = $chunk;
        }
        $actual = count($chunks);
        $this->assertSame($expect, $actual);
    }
}
