<?php

namespace App\Support;

use ZipArchive;

/**
 * Penulis .xlsx minimal (tanpa library eksternal) - satu sheet, baris judul tebal.
 * Membutuhkan ekstensi PHP "zip".
 */
class SimpleXlsx
{
    public static function available(): bool
    {
        return class_exists(ZipArchive::class);
    }

    /**
     * @param  array<int,array<int,string|int|float|null>>  $rows  baris pertama = header
     * @param  array<int,int>  $widths  lebar kolom (karakter)
     * @return string path berkas sementara
     */
    public static function write(array $rows, string $sheetName = 'Laporan', array $widths = []): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>');

        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>');

        $zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.self::esc(mb_substr($sheetName, 0, 31)).'" sheetId="1" r:id="rId1"/></sheets></workbook>');

        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>');

        // style 0 = normal, 1 = tebal (header)
        $zip->addFromString('xl/styles.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>');

        $cols = '';
        foreach ($widths as $i => $w) {
            $n = $i + 1;
            $cols .= '<col min="'.$n.'" max="'.$n.'" width="'.$w.'" customWidth="1"/>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .($cols ? '<cols>'.$cols.'</cols>' : '').'<sheetData>';

        foreach ($rows as $r => $row) {
            $xml .= '<row r="'.($r + 1).'">';
            foreach (array_values($row) as $c => $val) {
                $ref = self::col($c).($r + 1);
                $style = $r === 0 ? ' s="1"' : '';
                if (is_int($val) || is_float($val)) {
                    $xml .= '<c r="'.$ref.'"'.$style.'><v>'.$val.'</v></c>';
                } elseif ($val === null || $val === '') {
                    $xml .= '<c r="'.$ref.'"'.$style.'/>';
                } else {
                    $xml .= '<c r="'.$ref.'" t="inlineStr"'.$style.'><is><t xml:space="preserve">'.self::esc((string) $val).'</t></is></c>';
                }
            }
            $xml .= '</row>';
        }

        $zip->addFromString('xl/worksheets/sheet1.xml', $xml.'</sheetData></worksheet>');
        $zip->close();

        return $path;
    }

    private static function col(int $i): string
    {
        $s = '';
        for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
            $s = chr(65 + ($i - 1) % 26).$s;
        }

        return $s;
    }

    private static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
