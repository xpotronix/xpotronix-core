<?
class pdo_row {
	public function __construct() { }
}

$pdo = new PDO('mysql:host=mysql1.jusbaires.gov.ar;dbname=xpay', 'xpay', 'tXrXzHN6LxhadQ6h');

// $pdo->exec("DROP TABLE test");
$pdo->exec("CREATE TABLE test(id INT, col VARCHAR(200))");
// for ($i = 0; $i < 100; $i++) {
//   $pdo->exec(sprintf("INSERT INTO test(id, col) VALUES (1, '012345678901234567890123456789012345678901234567890123456789-%d')", $i));
// }

printf("With ctor argument (memory usage increase):");
for ($i = 0; $i < 100; $i++) {
	// $stmt = $pdo->prepare("SELECT col FROM test");
	$stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS `_licencia`.`ID`,`_licencia`.`IDTIPLIC`,`_licencia`.`LEGAJO`,`_licencia`.`DH`,`_licencia`.`FECOTORGA`,`_licencia`.`FECDESDE`,`_licencia`.`FECHASTA`,if( CURDATE() >= _licencia.FECDESDE AND CURDATE() <= _licencia.FECHASTA,1,0 ) AS `actual`,`_licencia`.`dias`,`_licencia`.`vencimiento`,`_licencia`.`USUARIO`,`_licencia`.`HSREDHOR`,`_licencia`.`CERTID`,`_licencia`.`CERTFECHA`,`_licencia`.`ALERVEN`,`_licencia`.`feria`,`_licencia`.`feria_ID`,`_licencia`.`OBSERV`,`_licencia`.`PROMESES`,`_licencia`.`ID_payroll`,`_licencia`.`licencia_relac_ID`, IF( _t_licencia.grupo is null, (YEAR(NOW()) -TOPEAN +1 ) <= YEAR(_licencia.FECDESDE), DATE(NOW()) <= date_add( _licencia.FECDESDE, interval TOPEAN YEAR) OR _licencia.vencimiento >= DATE(NOW()))  AS `vigente`,(    select group_concat( concat(ff.AN,'/',ff.PERIODO,': ',cc.dias) separator '; ' )    from _compensatoria cc inner join _feria ff on cc.feria_ID = ff.ID    where cc.licencia_ID = _licencia.ID) AS `compensatorias`,(select group_concat( l.ID ) from licencia as l where l.legajo = _licencia.LEGAJO and l.t_licencia_ID = _licencia.IDTIPLIC and l.fec_inicio = _licencia.FECDESDE and l.fec_fin = _licencia.FECHASTA) AS `match_licencia`,_t_licencia.DESCRIP AS `IDTIPLIC_label`,concat('#', LTRIM( _empleado.legajo ), ' ', _empleado.nombre ) AS `LEGAJO_label`,RT48.Descrip AS `CERTID_label`,concat(f.AN,'/',f.PERIODO) AS `feria_ID_label` FROM `_licencia`  LEFT JOIN `_t_licencia` ON `_licencia`.`IDTIPLIC`=`_t_licencia`.`ID` LEFT JOIN `_empleado` ON `_licencia`.`LEGAJO`=`_empleado`.`legajo` LEFT JOIN `_clasifica` AS `RT48` ON `_licencia`.`CERTID`=`RT48`.`Codigo` AND RT48.Cotab='48' LEFT JOIN `_feria` AS `f` ON `_licencia`.`feria_ID`=`f`.`ID`  WHERE (_licencia.ID = '$i')");
    $stmt->setFetchMode(PDO::FETCH_CLASS, 'pdo_row', array($stmt));
	$stmt->execute();
	$rows = $stmt->fetch();
	printf("emalloc %d kB, malloc %d kB\n",
		memory_get_usage() / 1024,
		memory_get_usage(true) / 1024);
}

printf("Without ctor argument (no memory usage increase):");
for ($i = 0; $i < 100; $i++) {
	// $stmt = $pdo->prepare("SELECT col FROM test");
	$stmt = $pdo->query("SELECT SQL_CALC_FOUND_ROWS `_licencia`.`ID`,`_licencia`.`IDTIPLIC`,`_licencia`.`LEGAJO`,`_licencia`.`DH`,`_licencia`.`FECOTORGA`,`_licencia`.`FECDESDE`,`_licencia`.`FECHASTA`,if( CURDATE() >= _licencia.FECDESDE AND CURDATE() <= _licencia.FECHASTA,1,0 ) AS `actual`,`_licencia`.`dias`,`_licencia`.`vencimiento`,`_licencia`.`USUARIO`,`_licencia`.`HSREDHOR`,`_licencia`.`CERTID`,`_licencia`.`CERTFECHA`,`_licencia`.`ALERVEN`,`_licencia`.`feria`,`_licencia`.`feria_ID`,`_licencia`.`OBSERV`,`_licencia`.`PROMESES`,`_licencia`.`ID_payroll`,`_licencia`.`licencia_relac_ID`, IF( _t_licencia.grupo is null, (YEAR(NOW()) -TOPEAN +1 ) <= YEAR(_licencia.FECDESDE), DATE(NOW()) <= date_add( _licencia.FECDESDE, interval TOPEAN YEAR) OR _licencia.vencimiento >= DATE(NOW()))  AS `vigente`,(    select group_concat( concat(ff.AN,'/',ff.PERIODO,': ',cc.dias) separator '; ' )    from _compensatoria cc inner join _feria ff on cc.feria_ID = ff.ID    where cc.licencia_ID = _licencia.ID) AS `compensatorias`,(select group_concat( l.ID ) from licencia as l where l.legajo = _licencia.LEGAJO and l.t_licencia_ID = _licencia.IDTIPLIC and l.fec_inicio = _licencia.FECDESDE and l.fec_fin = _licencia.FECHASTA) AS `match_licencia`,_t_licencia.DESCRIP AS `IDTIPLIC_label`,concat('#', LTRIM( _empleado.legajo ), ' ', _empleado.nombre ) AS `LEGAJO_label`,RT48.Descrip AS `CERTID_label`,concat(f.AN,'/',f.PERIODO) AS `feria_ID_label` FROM `_licencia`  LEFT JOIN `_t_licencia` ON `_licencia`.`IDTIPLIC`=`_t_licencia`.`ID` LEFT JOIN `_empleado` ON `_licencia`.`LEGAJO`=`_empleado`.`legajo` LEFT JOIN `_clasifica` AS `RT48` ON `_licencia`.`CERTID`=`RT48`.`Codigo` AND RT48.Cotab='48' LEFT JOIN `_feria` AS `f` ON `_licencia`.`feria_ID`=`f`.`ID`  WHERE (_licencia.ID = '$i')");
    // $stmt->setFetchMode(PDO::FETCH_CLASS, 'pdo_row');
	// $stmt->execute();
	$rows = $stmt->fetch( PDO::FETCH_ASSOC );
	printf("emalloc %d kB, malloc %d kB\n",
		memory_get_usage() / 1024,
		memory_get_usage(true) / 1024);
}
?>
