<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

class SimpleXlsx
{
    public static function available(): bool
    {
        return class_exists(ZipArchive::class);
    }

    public static function write(
        array $rows,
        string $sheetName = 'Laporan',
        array $widths = []
    ): string {
        if (! self::available()) {
            throw new RuntimeException(
                'Ekstensi PHP ZIP belum tersedia.'
            );
        }

        $path = tempnam(sys_get_temp_dir(), 'xlsx');

        if ($path === false) {
            throw new RuntimeException(
                'Gagal membuat file sementara.'
            );
        }

        $zip = new ZipArchive;
        $result = $zip->open($path, ZipArchive::OVERWRITE);

        if ($result !== true) {
            @unlink($path);

            throw new RuntimeException(
                'Gagal membuat file Excel.'
            );
        }

        $sheetName = mb_substr(
            preg_replace('/[\\\\\/?*\[\]:]/u', '', $sheetName) ?: 'Laporan',
            0,
            31
        );

        $zip->addFromString(
            '[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>'
        );

        $zip->addFromString(
            '_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>'
        );

        $zip->addFromString(
            'xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . self::esc($sheetName) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>'
        );

        $zip->addFromString(
            'xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>'
        );

        $zip->addFromString(
            'xl/styles.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF176B55"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFD9E2DF"/></left><right style="thin"><color rgb="FFD9E2DF"/></right><top style="thin"><color rgb="FFD9E2DF"/></top><bottom style="thin"><color rgb="FFD9E2DF"/></bottom><diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="4">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="4" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>'
        );

        $columnCount = max(
            count($widths),
            collect($rows)->map(fn ($row) => count($row))->max() ?? 0
        );

        $cols = '';

        for ($i = 0; $i < $columnCount; $i++) {
            $width = max(8, min(60, (int) ($widths[$i] ?? 16)));
            $n = $i + 1;

            $cols .= '<col min="' . $n . '" max="' . $n
                . '" width="' . $width . '" customWidth="1"/>';
        }

        $lastColumn = self::col(max(0, $columnCount - 1));
        $lastRow = max(1, count($rows));

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . ($cols ? '<cols>' . $cols . '</cols>' : '')
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="20"/>'
            . '<autoFilter ref="A1:' . $lastColumn . $lastRow . '"/>'
            . '<sheetData>';

        foreach ($rows as $r => $row) {
            $rowNumber = $r + 1;
            $height = $r === 0 ? ' ht="32" customHeight="1"' : '';
            $xml .= '<row r="' . $rowNumber . '"' . $height . '>';

            foreach (array_values($row) as $c => $value) {
                $ref = self::col($c) . $rowNumber;
                $style = $r === 0 ? 1 : 2;

                if (is_int($value) || is_float($value)) {
                    $xml .= '<c r="' . $ref . '" s="' . $style . '"><v>'
                        . (is_finite((float) $value) ? $value : 0)
                        . '</v></c>';
                } elseif ($value === null || $value === '') {
                    $xml .= '<c r="' . $ref . '" s="' . $style . '"/>';
                } else {
                    $xml .= '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">'
                        . self::esc((string) $value)
                        . '</t></is></c>';
                }
            }

            $xml .= '</row>';
        }

        $xml .= '</sheetData>'
            . '<pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
            . '</worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
        $zip->close();

        return $path;
    }

    private static function col(int $index): string
    {
        $name = '';

        for ($index++; $index > 0; $index = intdiv($index - 1, 26)) {
            $name = chr(65 + ($index - 1) % 26) . $name;
        }

        return $name;
    }

    private static function esc(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_XML1 | ENT_QUOTES,
            'UTF-8'
        );
    }
}
