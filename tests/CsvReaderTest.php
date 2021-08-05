<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use Csus4\PgsqlCopy\Exception\CsvRowCountException;
use Csus4\PgsqlCopy\Exception\CsvRowException;
use PHPUnit\Framework\TestCase;
use SplFileObject;

class CsvReaderTest extends TestCase
{
    public function testHeader0() : void
    {
        $csvReader = new CsvReader(
            new SplFileObject(__DIR__ . '/var/data/header_0.csv', 'r'),
            ['code', 'name', 'price'],
        );
        $fields = $csvReader->getFieldsLine();
        $expect = 'code,name,price' . PHP_EOL;
        $this->assertSame($expect, $fields);
        $i = 0;
        foreach ($csvReader as $row) {
            if ($i === 0) {
                $expect = '000100,あいうえお,1000' . PHP_EOL;
                $this->assertSame($expect, $row);
            }
            $i++;
        }
        $this->assertSame(10, $i);
    }

    public function testHeader1() : void
    {
        $csvReader = new CsvReader(
            new SplFileObject(__DIR__ . '/var/data/header_1.csv', 'r'),
            ['code', 'name', 'price'],
        );
        $csvReader->setHeaderOffset(1);
        $fields = $csvReader->getFieldsLine();
        $expect = 'code,name,price' . PHP_EOL;
        $this->assertSame($expect, $fields);
        $i = 0;
        foreach ($csvReader as $row) {
            if ($i === 0) {
                $expect = '000100,あいうえお,1000'.PHP_EOL;
                $this->assertSame($expect, $row);
            }
            $i++;
        }
        $this->assertSame(10, $i);
    }

    public function testExtras() : void
    {
        $csvReader = new CsvReader(
            new SplFileObject(__DIR__ . '/var/data/header_0.csv', 'r'),
            ['code', 'name', 'price'],
        );
        $fields = $csvReader->getFieldsLine();
        $expect = 'code,name,price' . PHP_EOL;
        $this->assertSame($expect, $fields);

        $rows = [];
        foreach ($csvReader as $row) {
            $rows[] = $row;
        }

        $expect = [
            '000100,あいうえお,1000' . PHP_EOL,
            '000200,かきくけこ,2000' . PHP_EOL,
            '000300,さしすせそ,3000' . PHP_EOL,
            '000400,たちつてと,4000' . PHP_EOL,
            '000500,なにぬねの,5000' . PHP_EOL,
            '000600,はひふへほ,6000' . PHP_EOL,
            '000700,まみむめも,7000' . PHP_EOL,
            '000800,やいゆえよ,8000' . PHP_EOL,
            '000900,らりるれろ,9000' . PHP_EOL,
            '001000,わいうえを,10000' . PHP_EOL,
        ];
        $this->assertSame($expect, $rows);
    }

    public function testFields() : void
    {
        $fields = ['code', 'name', 'price'];
        $csvReader = new CsvReader(
            new SplFileObject(__DIR__ . '/var/data/header_fields.csv', 'r'),
            $fields,
        );
        $fields = $csvReader->getFieldsLine();
        $expect = 'code,name,price' . PHP_EOL;
        $this->assertSame($expect, $fields);

        $rows = [];
        foreach ($csvReader as $row) {
            $rows[] = $row;
        }

        $expect = [
            '000100,あいうえお,1000' . PHP_EOL,
            '000200,かきくけこ,2000' . PHP_EOL,
            '000300,さしすせそ,3000' . PHP_EOL,
            '000400,たちつてと,4000' . PHP_EOL,
            '000500,なにぬねの,5000' . PHP_EOL,
            '000600,はひふへほ,6000' . PHP_EOL,
            '000700,まみむめも,7000' . PHP_EOL,
            '000800,やいゆえよ,8000' . PHP_EOL,
            '000900,らりるれろ,9000' . PHP_EOL,
            '001000,わいうえを,10000' . PHP_EOL,
        ];
        $this->assertSame($expect, $rows);
    }

    public function testFieldsDifferentOrder() : void
    {
        $csvReader = new CsvReader(
            new SplFileObject(__DIR__ . '/var/data/header_fields.csv', 'r'),
            ['name', 'code', 'price'],
        );
        $fields = $csvReader->getFieldsLine();
        $expect = 'name,code,price' . PHP_EOL;
        $this->assertSame($expect, $fields);

        $rows = [];
        foreach ($csvReader as $row) {
            $rows[] = $row;
        }

        $expect = [
            'あいうえお,000100,1000' . PHP_EOL,
            'かきくけこ,000200,2000' . PHP_EOL,
            'さしすせそ,000300,3000' . PHP_EOL,
            'たちつてと,000400,4000' . PHP_EOL,
            'なにぬねの,000500,5000' . PHP_EOL,
            'はひふへほ,000600,6000' . PHP_EOL,
            'まみむめも,000700,7000' . PHP_EOL,
            'やいゆえよ,000800,8000' . PHP_EOL,
            'らりるれろ,000900,9000' . PHP_EOL,
            'わいうえを,001000,10000' . PHP_EOL,
        ];
        $this->assertSame($expect, $rows);
    }

    public function testGetter() : void
    {
        $csvReader = new CsvReader(
            new SplFileObject(__DIR__ . '/var/data/header_0.csv', 'r'),
            []
        );
        $delimiter = $csvReader->getDelimiter();
        $nullAs = $csvReader->getNullAs();
        $this->assertSame(',', $delimiter);
        $this->assertSame('\\\\N', $nullAs);
    }

    public function testFilter() : void
    {
        $csvReader = new CsvReader(
            new SplFileObject(__DIR__ . '/var/data/header_0.csv', 'r'),
            ['name', 'code', 'price'],
        );
        $csvReader->setFilter(function (array $row) : array {
            $messages = [];
            if (strlen($row[0]) !== 7) {
                $messages[] = 'コードは7文字で入力してください。';
            }
            return $messages;
        });

        $this->expectException(CsvRowException::class);
        $this->expectExceptionMessage('1行目: コードは7文字で入力してください。');
        foreach ($csvReader as $row) {
            assert(is_array($row));
        }
    }

    public function testCsvRowCount() : void
    {
        $fields = ['code', 'name', 'price', 'updated_at', 'hoge', 'fuga'];
        $csvReader = new CsvReader(new SplFileObject(__DIR__ . '/var/data/header_0.csv', 'r'), $fields);
        $this->expectException(CsvRowCountException::class);
        $this->expectExceptionMessage('1行目: ヘッダ行と列数が違います。');
        foreach ($csvReader as $row) {
            assert(is_array($row));
        }
    }
}
