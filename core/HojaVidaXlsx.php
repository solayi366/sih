<?php
/**
 * SIH — HojaVidaXlsx
 * Genera .xlsx con layout idéntico a HOJA_DE_VIDA_*.xlsx
 * PHP puro con ZipArchive. Sin dependencias externas.
 */
class HojaVidaXlsx
{
    private array $ss     = [];
    private array $sheets = [];

    // ══════════════════════════════════════════════════════════════════
    public function agregarHoja(array $a, array $hijos, array $novedades, array $campos_dinamicos = []): void
    {
        $nombre = substr(preg_replace('/[\\\\\/\?\*\[\]:]+/', '_',
            $a['r_hostname'] ?? $a['r_qr'] ?? 'Activo'), 0, 31);

        $this->sheets[] = ['nombre'=>$nombre, 'xml'=>$this->buildInventario($a, $hijos, $campos_dinamicos)];

        if (!empty($novedades)) {
            $this->sheets[] = [
                'nombre' => 'Nov_'.substr($nombre,0,27),
                'xml'    => $this->buildActualizaciones($novedades),
            ];
        }
    }

    public function guardar(string $path): string
    {
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $this->addRels($zip);
        $this->addContentTypes($zip);
        $this->addWorkbook($zip);
        $this->addStyles($zip);
        // Las hojas se renderizan aquí para que ss esté completo
        foreach ($this->sheets as $i => $sh) {
            $zip->addFromString("xl/worksheets/sheet".($i+1).".xml", $sh['xml']);
        }
        $this->addSharedStrings($zip);
        $zip->close();
        return $path;
    }

    // ══════════════════════════════════════════════════════════════════
    private function buildInventario(array $a, array $hijos, array $campos_dinamicos = []): string
    {
        $tipo   = strtolower($a['r_tipo'] ?? '');
        $tablet = str_contains($tipo,'tablet') || str_contains($tipo,'ipad');
        $laptop = str_contains($tipo,'laptop') || str_contains($tipo,'portatil') || str_contains($tipo,'portátil');

        $get = fn(string $k) => $this->getPeri($hijos, $k);
        $lector  = $get('lector');
        $monitor = $get('monitor')   ?: $get('pantalla');
        $mouse   = $get('mouse')     ?: $get('raton')   ?: $get('ratón');
        $teclado = $get('teclado')   ?: $get('keyboard');
        $ups     = $get('ups');
        $imp     = $get('impresora') ?: $get('printer');
        $tred    = $get('tarjeta')   ?: $get('inalambrica') ?: $get('wifi');
        $tel     = $get('telefono')  ?: $get('teléfono');

        $cols = ['A'=>5.44,'B'=>14.89,'C'=>35.22,'D'=>4.0,'E'=>12.33,'F'=>11.66,'G'=>22.55];
        $rh   = [2=>21,3=>18,4=>9.75,5=>20.25,6=>20.25,7=>20.25,8=>9.75,9=>16.5,10=>14.25];
        for ($r=11;$r<=44;$r++) $rh[$r]=20.25;
        $rh[42]=18.75; $rh[43]=18.75; $rh[44]=15;

        $C = [];

        // Fila 2 título
        $C['B2']=['INVENTARIO DE EQUIPOS',1];

        // Fecha
        $C['E3']=['Fecha:',2];
        $C['F4']=[date('d/m/Y'),2];

        // Datos generales
        $C['B5']=['Usuario:',2];
        $C['C5']=[$a['r_responsable']??'',3];
        $C['B6']=['Nombre equipo: ',2];
        $C['C6']=[$a['r_hostname']??$a['r_qr']??'',3];
        $C['E6']=['Tipo de Equipo',2];
        $C['B7']=['Dependencia:',2];
        $C['C7']=[$a['r_area']??'',6];
        $C['E7']=[$tablet
            ?'Tablet _x_ Portatil __ Escritorio __'
            :($laptop?'Portatil _x_ Escritorio __ Todo en Uno __'
                     :'Portatil __ Escritorio _x_ Todo en Uno __'),3];

        // Sección principal
        $secLabel = $tablet?'TABLET':($laptop?'PORTATIL':'CPU');
        $C['B10']=[$secLabel,5];

        $row=11;
        if ($tablet) {
            $campos=[['MARCA',$a['r_marca']??''],['MODELO',$a['r_modelo']??''],
                     ['REFERENCIA',$a['r_referencia']??''],['SERIAL',$a['r_serial']??''],
                     ['SERIAL ENVIA',''],['SISTEMA OPERATIVO',''],['VERSION',''],
                     ['IP',$a['r_ip']??''],['MAC',$a['r_mac']??''],['PROCESADOR',''],['ALMACENAMIENTO','']];
        } else {
            $campos=[['MARCA',$a['r_marca']??''],['REFERENCIA',$a['r_modelo']??$a['r_referencia']??''],
                     ['SERIAL',$a['r_serial']??''],['WINDOWS',''],['SERVICE PACK',''],['OFFICE',''],
                     ['IP',$a['r_ip']??''],['MAC',$a['r_mac']??''],['PROCESADOR',''],
                     ['RAM',''],['DISCO DURO',''],['CD','']];
        }
        foreach ($campos as [$l,$v]) { $C["B{$row}"]=[$l,4]; $C["C{$row}"]=[$v,3]; $row++; }

        // Teclado
        if (!$tablet) {
            $C["B{$row}"]=[ 'TECLADO',5]; $row++;
            $tc=[['MARCA',$teclado['r_marca']??'NO APLICA'],['REFERENCIA',$teclado['r_modelo']??''],['SERIE',$teclado['r_serial']??'']];
            foreach ($tc as [$l,$v]) { $C["B{$row}"]=[$l,4]; $C["C{$row}"]=[$v,3]; $row++; }
        }

        // Tarjeta red
        $C["B{$row}"]=[ 'TARJETA RED INALAMBRICA',5]; $row++;
        $tr=[['MARCA',$tred['r_marca']??'NO APLICA'],['REFERENCIA',$tred['r_modelo']??''],
             ['SERIE',$tred['r_serial']??''],['MAC ',$tred['r_mac']??'']];
        foreach ($tr as [$l,$v]) { $C["B{$row}"]=[$l,4]; $C["C{$row}"]=[$v,3]; $row++; }

        // Teléfono
        if ($tel) {
            $C["B{$row}"]=[ 'TELEFONO ',5]; $row++;
            foreach ([['REFERENCIA',$tel['r_modelo']??''],['SERIE T',$tel['r_serial']??''],['MAC T','']] as [$l,$v]) {
                $C["B{$row}"]=[$l,4]; $C["C{$row}"]=[$v,3]; $row++;
            }
        }

        $pie = max($row+1, 42);

        // ── Columna derecha ──────────────────────────────────────────
        $C['E10']=['LECTOR',5];
        $C['E11']=['MARCA',4]; $C['F11']=['REFERENCIA',4]; $C['G11']=['SERIE',4];
        $C['E12']=[$lector['r_marca']??'NO APLICA',3];
        $C['F12']=[$lector['r_modelo']??'',3];
        $C['G12']=[$lector['r_serial']??'',3];
        $C['E13']=['SERIE ENVIA',4]; $C['F13']=['N.A',3];

        $C['E14']=['BASE LECTOR',5];
        foreach (['E15'=>'MARCA','E16'=>'REFERENCIA','E17'=>'SERIE','E18'=>'SERIE ENVIA'] as $co=>$lb)
            $C[$co]=[$lb,4];
        foreach (['F15','F16','F17','F18'] as $co) $C[$co]=['',3];

        $C['E20']=['MONITOR',5];
        $C['E21']=['MARCA',4];      $C['F21']=[$monitor['r_marca']??'NO APLICA',3];
        $C['E22']=['REFERENCIA',4]; $C['F22']=[$monitor['r_modelo']??'',3];
        $C['E23']=['SERIE',4];      $C['F23']=[$monitor['r_serial']??'',3];

        $C['E25']=['MOUSE',5];
        $C['E26']=['MARCA',4];      $C['F26']=[$mouse['r_marca']??'NO APLICA',3];
        $C['E27']=['REFERENCIA',4]; $C['F27']=[$mouse['r_modelo']??'',3];
        $C['E28']=['SERIE',4];      $C['F28']=[$mouse['r_serial']??'',3];

        $C['E30']=['UPS',5];
        $C['E31']=['MARCA',4];      $C['F31']=[$ups['r_marca']??'NO APLICA',3];
        $C['E32']=['REFERENCIA',4]; $C['F32']=[$ups['r_modelo']??'',3];
        $C['E33']=['SERIE',4];      $C['F33']=[$ups['r_serial']??'',3];

        $C['E35']=['IMPRESORA',5];
        $C['E36']=['MARCA',4];      $C['F36']=[$imp['r_marca']??'NO APLICA',3];
        $C['E37']=['REFERENCIA',4]; $C['F37']=[$imp['r_modelo']??'',3];
        $C['E38']=['SERIE',4];      $C['F38']=[$imp['r_serial']??'',3];
        $C['E39']=['IP',4];         $C['F39']=['',3];

        $C['E41']=['Observaciones',7]; $C['F41']=['',3];

        $C["B{$pie}"]=[ 'BACKUP',5];
        $C["E{$pie}"]=[ 'Responsable:',2];
        $C["B".($pie+1)]=[ 'ACTUALIZACIONES',5];
        $C["E".($pie+1)]=[$a['r_responsable']??'',3];
        $C["B".($pie+2)]=[ 'ANTIVIRUS',5];

        // ── Campos Dinámicos al final de la hoja ────────────────────────────
        if (!empty($campos_dinamicos)) {
            $dynRow = max($row + 2, $pie + 5);
            $C["B{$dynRow}"] = ['CAMPOS ADICIONALES', 5];
            $dynRow++;
            foreach ($campos_dinamicos as $cd) {
                $C["B{$dynRow}"] = [$cd['etiqueta'] ?? '', 4];
                $val = $cd['valor'] ?? '';
                if (($cd['tipo_dato'] ?? '') === 'booleano') {
                    $val = ($val === '1' || strtolower($val) === 'true') ? 'Sí' : 'No';
                }
                $C["C{$dynRow}"] = [$val, 3];
                $dynRow++;
            }
        }

        $merges=[
            'B2:G2','E3:E4','F4:G4','E6:G6','E7:G7','B10:C10','E10:G10',
            'F12:G12','F13:G13','F15:G15','F16:G16','F17:G17','F18:G18',
            'E20:G20','F21:G21','F22:G22','F23:G23',
            'E25:G25','F26:G26','F27:G27','F28:G28',
            'E30:G30','F31:G31','F32:G32','F33:G33',
            'E35:G35','F36:G36','F37:G37','F38:G38','F39:G39',
            'F41:G41',
            "E{$pie}:G{$pie}",
            "E".($pie+1).":G".($pie+1),
        ];

        return $this->renderSheet($C,$cols,$rh,$merges);
    }

    private function buildActualizaciones(array $novedades): string
    {
        $C=[];
        $C['A1']=['ACTUALIZACIONES / NOVEDADES',5];
        $C['A2']=['Fecha',2]; $C['B2']=['Tipo / Descripción',2];
        $C['C2']=['Reportante',2]; $C['D2']=['Estado',2];
        $r=3;
        foreach ($novedades as $n) {
            $fecha=substr((string)($n['fecha_reporte']??''),0,16);
            $C["A{$r}"]=[$fecha,3];
            $C["B{$r}"]=[($n['tipo_dano']??'').' — '.($n['descripcion']??''),3];
            $C["C{$r}"]=[($n['nombre_reportante']??'').' ('.($n['cedula_reportante']??'').')',3];
            $C["D{$r}"]=[$n['estado_ticket']??'',3];
            $r++;
        }
        return $this->renderSheet($C,['A'=>20,'B'=>50,'C'=>30,'D'=>15],[],[]);
    }

    private function renderSheet(array $cells, array $colWidths, array $rowH, array $merges): string
    {
        $colMap=['A'=>1,'B'=>2,'C'=>3,'D'=>4,'E'=>5,'F'=>6,'G'=>7,'H'=>8];
        $colXml='';
        foreach ($colWidths as $l=>$w) {
            $i=$colMap[$l]??1;
            $colXml.="<col min=\"{$i}\" max=\"{$i}\" width=\"{$w}\" customWidth=\"1\"/>";
        }

        $byRow=[];
        foreach ($cells as $coord=>[$val,$sty]) {
            preg_match('/^([A-Z]+)(\d+)$/',$coord,$m);
            $byRow[(int)$m[2]][]=$coord;
        }

        $allRows=array_unique(array_merge(array_keys($byRow),array_keys($rowH)));
        sort($allRows);

        $rowsXml='';
        foreach ($allRows as $r) {
            $ht=isset($rowH[$r])?" ht=\"{$rowH[$r]}\" customHeight=\"1\"":'';
            $rowCoords=$byRow[$r]??[];
            sort($rowCoords);
            $cxml='';
            foreach ($rowCoords as $coord) {
                [$val,$sty]=$cells[$coord];
                $cxml.=$this->cell($coord,(string)$val,$sty);
            }
            $rowsXml.="<row r=\"{$r}\"{$ht}>{$cxml}</row>";
        }

        $mxml='';
        if (!empty($merges)) {
            $mxml='<mergeCells count="'.count($merges).'">';
            foreach ($merges as $m) $mxml.="<mergeCell ref=\"{$m}\"/>";
            $mxml.='</mergeCells>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            .' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheetView workbookViewId="0"/>'
            .($colXml?"<cols>{$colXml}</cols>":'')
            ."<sheetData>{$rowsXml}</sheetData>"
            .$mxml
            .'</worksheet>';
    }

    private function cell(string $coord, string $val, int $sty): string
    {
        if ($val==='') return "<c r=\"{$coord}\" s=\"{$sty}\"/>";
        if (!isset($this->ss[$val])) $this->ss[$val]=count($this->ss);
        $idx=$this->ss[$val];
        return "<c r=\"{$coord}\" t=\"s\" s=\"{$sty}\"><v>{$idx}</v></c>";
    }

    private function getPeri(array $hijos, string $kw): ?array
    {
        foreach ($hijos as $h)
            if (str_contains(strtolower($h['r_tipo']??''),$kw)) return $h;
        return null;
    }

    private function addRels(ZipArchive $z): void
    {
        $z->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>');
    }

    private function addContentTypes(ZipArchive $z): void
    {
        $ov='';
        for ($i=1;$i<=count($this->sheets);$i++)
            $ov.='<Override PartName="/xl/worksheets/sheet'.$i.'.xml"'
                .' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        $z->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            .$ov.'</Types>');
    }

    private function addWorkbook(ZipArchive $z): void
    {
        $rels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $sheets='';
        for ($i=1;$i<=count($this->sheets);$i++) {
            $nm=$this->xe($this->sheets[$i-1]['nombre']);
            $sheets.="<sheet name=\"{$nm}\" sheetId=\"{$i}\" r:id=\"rId{$i}\"/>";
            $rels.="<Relationship Id=\"rId{$i}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet{$i}.xml\"/>";
        }
        $rels.='<Relationship Id="rIdSS" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
             .'<Relationship Id="rIdSt" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
             .'</Relationships>';
        $z->addFromString('xl/_rels/workbook.xml.rels',$rels);
        $z->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            .' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            ."<sheets>{$sheets}</sheets></workbook>");
    }

    private function addStyles(ZipArchive $z): void
    {
        $z->addFromString('xl/styles.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="8">'
            .'<font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="16"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="14"/><name val="Calibri"/></font>'
            .'<font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><sz val="10"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="10"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment horizontal="left" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment horizontal="left" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment horizontal="left" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment horizontal="left" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="5" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment horizontal="left" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="6" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment horizontal="left" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="7" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment horizontal="left" vertical="center"/></xf>'
            .'</cellXfs>'
            .'</styleSheet>');
    }

    private function addSharedStrings(ZipArchive $z): void
    {
        $total=count($this->ss);
        $xml='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            ."<sst xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" count=\"{$total}\" uniqueCount=\"{$total}\">";
        foreach ($this->ss as $str=>$_)
            $xml.='<si><t xml:space="preserve">'.$this->xe((string)$str).'</t></si>';
        $xml.='</sst>';
        $z->addFromString('xl/sharedStrings.xml',$xml);
    }

    private function xe(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1|ENT_QUOTES, 'UTF-8');
    }
}
