<?php
declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use PDO;

final class FakePdo extends PDO
{
    public function __construct()
    {
    }

    public function pgsqlCopyFromArray() : bool
    {
        return true;
    }
}
