<#1>
<?php
$fields = array(
	'id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'identifier' => array(
		'notnull' => '1',
		'type' => 'text',
		'length' => '50',

	),
	'data_type' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '1',

	),
	'position' => array(
		'type' => 'integer',
		'length' => '3',

	),
	'is_standard_field' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '1',

	),
	'object_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
);
global $ilDB;
if (! $ilDB->tableExists('il_bibl_field')) {
	$ilDB->createTable('il_bibl_field', $fields);
	$ilDB->addPrimaryKey('il_bibl_field', array( 'id' ));

	if (! $ilDB->sequenceExists('il_bibl_field')) {
		$ilDB->createSequence('il_bibl_field');
	}

}
?>
<#2>
<?php
$fields = array(
	'id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'field_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'object_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'filter_type' => array(
		'type' => 'integer',
		'length' => '1',

	),

);
if (! $ilDB->tableExists('il_bibl_filter')) {
	$ilDB->createTable('il_bibl_filter', $fields);
	$ilDB->addPrimaryKey('il_bibl_filter', array( 'id' ));

	if (! $ilDB->sequenceExists('il_bibl_filter')) {
		$ilDB->createSequence('il_bibl_filter');
	}

}
?>
<#3>
<?php
if(!$ilDB->tableColumnExists("il_bibl_data", "file_type")) {
	$ilDB->addTableColumn("il_bibl_data", "file_type", [
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 1
	]);
}

$type = function ($filename) {
	if (strtolower(substr($filename, - 6)) == "bibtex"
	    || strtolower(substr($filename, - 3)) == "bib") {
		return 2;
	}
	return 1;
};

$res = $ilDB->query("SELECT * FROM il_bibl_data");
while($d = $ilDB->fetchObject($res)) {
	$type_id = (int)$type($d->filname);
	$ilDB->update("il_bibl_data", [
		"file_type" => [ "integer", $type_id ]
	], [ "id" => $d->id ]);
}
?>
<#4>
<?php
$fields = array(
	'id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '4',

	),
	'field_id' => array(
		'notnull' => '1',
		'type' => 'integer',
		'length' => '8',

	),
	'language_key' => array(
		'notnull' => '1',
		'type' => 'text',
		'length' => '2',

	),
	'translation' => array(
		'type' => 'text',
		'length' => '256',

	),
	'description' => array(
		'type' => 'clob',

	),

);
if (! $ilDB->tableExists('il_bibl_translation')) {
	$ilDB->createTable('il_bibl_translation', $fields);
	$ilDB->addPrimaryKey('il_bibl_translation', array( 'id' ));

	if (! $ilDB->sequenceExists('il_bibl_translation')) {
		$ilDB->createSequence('il_bibl_translation');
	}

}
?>
<#5>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#6>
<?php
// Installing default fields from bib and ris
$tf = new ilBiblTypeFactory();

$bib = $tf->getInstanceForType(ilBiblTypeFactory::DATA_TYPE_BIBTEX);
foreach ($bib->getStandardFieldIdentifiers() as $identifier) {

}

$ris = $tf->getInstanceForType(ilBiblTypeFactory::DATA_TYPE_RIS);
foreach ($ris->getStandardFieldIdentifiers() as $identifier) {

}


?>

