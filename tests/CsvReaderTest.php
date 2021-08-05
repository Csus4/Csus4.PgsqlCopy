<?php

declare(strict_types=1);

namespace Csus4\PgsqlCopy;

use Csus4\PgsqlCopy\Exception\CsvRowCountException;
use Csus4\PgsqlCopy\Exception\CsvRowException;
use Csus4\PgsqlCopy\Exception\FieldException;
use PHPUnit\Framework\TestCase;
use SplFileObject;
use SplTempFileObject;

class CsvReaderTest extends TestCase
{
    public function testHeader0() : void
    {
        $temp = new SplTempFileObject();
        $temp->setFlags(SplFileObject::READ_AHEAD|SplFileObject::READ_CSV);
        $temp->fputcsv(['code', 'name', 'price']);
        $temp->fputcsv(['000100', 'あいうえお', '1000']);

        $csvReader = new CsvReader($temp, ['code', 'name', 'price']);
        $fields = $csvReader->getFieldsLine();
        $expect = 'code,name,price' . PHP_EOL;
        $this->assertSame($expect, $fields);
        foreach ($csvReader as $row) {
            $expect = '000100,あいうえお,1000' . PHP_EOL;
            $this->assertSame($expect, $row);
        }
    }

    public function testHeader1() : void
    {
        $temp = new SplTempFileObject();
        $temp->setFlags(SplFileObject::READ_AHEAD|SplFileObject::READ_CSV);
        $temp->fputcsv(['コード', '名称', '価格']);
        $temp->fputcsv(['code', 'name', 'price']);
        $temp->fputcsv(['000100', 'あいうえお', '1000']);

        $csvReader = new CsvReader($temp, ['code', 'name', 'price']);
        $csvReader->setHeaderOffset(1);
        $fields = $csvReader->getFieldsLine();
        $expect = 'code,name,price' . PHP_EOL;
        $this->assertSame($expect, $fields);
        foreach ($csvReader as $row) {
            $expect = '000100,あいうえお,1000'.PHP_EOL;
            $this->assertSame($expect, $row);
        }
    }

    public function testFieldsDifferentOrder() : void
    {
        $temp = new SplTempFileObject();
        $temp->setFlags(SplFileObject::READ_AHEAD|SplFileObject::READ_CSV);
        $temp->fputcsv(['code', 'name', 'price']);
        $temp->fputcsv(['000100', 'あいうえお', '1000']);

        $csvReader = new CsvReader($temp, ['name', 'code', 'price']);
        $fields = $csvReader->getFieldsLine();
        $expect = 'name,code,price' . PHP_EOL;
        $this->assertSame($expect, $fields);

        foreach ($csvReader as $row) {
            $expect = 'あいうえお,000100,1000'.PHP_EOL;
            $this->assertSame($expect, $row);
        }
    }

    public function testIgnoreExtraCols() : void
    {
        $temp = new SplTempFileObject();
        $temp->setFlags(SplFileObject::READ_AHEAD|SplFileObject::READ_CSV);
        $temp->fputcsv(['code', 'name', 'kana', 'price', 'created']);
        $temp->fputcsv(['000100', 'あいうえお', 'アイウエオ', '1000', '2021-01-01']);

        $csvReader = new CsvReader($temp, ['code', 'name', 'price']);
        $fields = $csvReader->getFieldsLine();
        $expect = 'code,name,price' . PHP_EOL;
        $this->assertSame($expect, $fields);

        foreach ($csvReader as $row) {
            $expect = '000100,あいうえお,1000'.PHP_EOL;
            $this->assertSame($expect, $row);
        }
    }

    public function testGetter() : void
    {
        $temp = new SplTempFileObject();
        $temp->setFlags(SplFileObject::READ_AHEAD|SplFileObject::READ_CSV);
        $temp->fputcsv(['code', 'name', 'price']);
        $temp->fputcsv(['000100', 'あいうえお', '1000']);

        $csvReader = new CsvReader($temp, ['code', 'name', 'price']);
        $delimiter = $csvReader->getDelimiter();
        $nullAs = $csvReader->getNullAs();
        $this->assertSame(',', $delimiter);
        $this->assertSame('\\\\N', $nullAs);
    }

    public function testFieldException() : void
    {
        $temp = new SplTempFileObject();
        $temp->setFlags(SplFileObject::READ_AHEAD|SplFileObject::READ_CSV);
        $temp->fputcsv(['code', 'kana', 'price']);
        $temp->fputcsv(['000100', 'アイウエオ', '1000']);

        $csvReader = new CsvReader($temp, ['code', 'name', 'price']);
        $this->expectException(FieldException::class);
        $this->expectExceptionMessage('CSVファイルに必要な列がありません(name)。');
        foreach ($csvReader as $row) {
            assert(is_array($row));
        }
    }

    public function testCsvRowCount() : void
    {
        $temp = new SplTempFileObject();
        $temp->setFlags(SplFileObject::READ_AHEAD|SplFileObject::READ_CSV);
        $temp->fputcsv(['code', 'name', 'price']);
        $temp->fputcsv(['000100', 'あいうえお']);

        $csvReader = new CsvReader($temp, ['code', 'name', 'price']);
        $this->expectException(CsvRowCountException::class);
        $this->expectExceptionMessage('1行目: ヘッダ行と列数が違います。');
        foreach ($csvReader as $row) {
            assert(is_array($row));
        }
    }

    public function testFilter() : void
    {
        $temp = new SplTempFileObject();
        $temp->setFlags(SplFileObject::READ_AHEAD|SplFileObject::READ_CSV);
        $temp->fputcsv(['code', 'name', 'price']);
        $temp->fputcsv(['000100', 'あいうえお', '1000']);

        $csvReader = new CsvReader($temp, ['code', 'name', 'price']);
        $csvReader->setFilter(function (array $row) : array {
            $messages = [];
            if (strlen($row['code']) !== 7) {
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
}
