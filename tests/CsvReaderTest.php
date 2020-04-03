<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use PHPUnit\Framework\TestCase;
use SplFileObject;

class CsvReaderTest extends TestCase
{
    public function testHeaderNo() : void
    {
        $csvReader = new CsvReader(new SplFileObject(__DIR__ . '/var/data/header_no.csv', 'r'));
        $csvReader->setHeader(['code', 'name', 'price']);
        $header = $csvReader->getHeader();
        $expect = 'code,name,price' . PHP_EOL;
        $this->assertSame($expect, $header);
        foreach ($csvReader as $row) {
            $expect = '000100,あいうえお,1000' . PHP_EOL;
            $this->assertSame($expect, $row);

            break;
        }
    }

    public function testHeader0() : void
    {
        $csvReader = new CsvReader(new SplFileObject(__DIR__ . '/var/data/header_0.csv', 'r'));
        $csvReader->setHeaderOffset(0);
        $header = $csvReader->getHeader();
        $expect = 'code,name,price' . PHP_EOL;
        $this->assertSame($expect, $header);
        foreach ($csvReader as $row) {
            $expect = '000100,あいうえお,1000' . PHP_EOL;
            $this->assertSame($expect, $row);

            break;
        }
    }

    public function testHeader1() : void
    {
        $csvReader = new CsvReader(new SplFileObject(__DIR__ . '/var/data/header_1.csv', 'r'));
        $csvReader->setHeaderOffset(1);
        $header = $csvReader->getHeader();
        $expect = 'code,name,price' . PHP_EOL;
        $this->assertSame($expect, $header);
        foreach ($csvReader as $row) {
            $expect = '000100,あいうえお,1000' . PHP_EOL;
            $this->assertSame($expect, $row);

            break;
        }
    }

    public function testFixed() : void
    {
        $csvReader = new CsvReader(new SplFileObject(__DIR__ . '/var/data/header_no.csv', 'r'));
        $csvReader->setHeader(['code', 'name', 'price']);
        $csvReader->setFixed(['created_at' => '2020-04-01 00:00:00', 'updated_at' => '2020-04-01 00:00:00']);
        $header = $csvReader->getHeader();
        $expect = 'code,name,price,created_at,updated_at' . PHP_EOL;
        $this->assertSame($expect, $header);

        $rows = [];
        foreach ($csvReader as $row) {
            $rows[] = $row;
        }

        $expect = [
            '000100,あいうえお,1000,2020-04-01 00:00:00,2020-04-01 00:00:00' . PHP_EOL,
            '000200,かきくけこ,2000,2020-04-01 00:00:00,2020-04-01 00:00:00' . PHP_EOL,
            '000300,さしすせそ,3000,2020-04-01 00:00:00,2020-04-01 00:00:00' . PHP_EOL,
            '000400,たちつてと,4000,2020-04-01 00:00:00,2020-04-01 00:00:00' . PHP_EOL,
            '000500,なにぬねの,5000,2020-04-01 00:00:00,2020-04-01 00:00:00' . PHP_EOL,
            '000600,はひふへほ,6000,2020-04-01 00:00:00,2020-04-01 00:00:00' . PHP_EOL,
            '000700,まみむめも,7000,2020-04-01 00:00:00,2020-04-01 00:00:00' . PHP_EOL,
            '000800,やいゆえよ,8000,2020-04-01 00:00:00,2020-04-01 00:00:00' . PHP_EOL,
            '000900,らりるれろ,9000,2020-04-01 00:00:00,2020-04-01 00:00:00' . PHP_EOL,
            '001000,わいうえを,10000,2020-04-01 00:00:00,2020-04-01 00:00:00' . PHP_EOL,
        ];
        $this->assertSame($expect, $rows);
    }

    public function testGetter() : void
    {
        $csvReader = new CsvReader(new SplFileObject(__DIR__ . '/var/data/header_no.csv', 'r'));
        $header = $csvReader->getHeader();
        $delimiter = $csvReader->getDelimiter();
        $nullAs = $csvReader->getNullAs();
        $this->assertSame('', $header);
        $this->assertSame(',', $delimiter);
        $this->assertSame('\\\\N', $nullAs);
    }
}
