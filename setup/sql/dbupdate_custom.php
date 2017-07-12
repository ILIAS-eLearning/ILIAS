<#1>
<?php
global $ilDB;
if (!$ilDB->tableExists('pdfgen_conf'))
{
	$fields = array (
		'conf_id'			=> array('type' => 'integer', 	'length' => 4,		'notnull' => true),
		'renderer'			=> array('type' => 'text', 		'length' => 255,	'notnull' => true),
		'service'			=> array('type' => 'text',	  	'length' => 255,	'notnull' => true),
		'purpose'			=> array('type' => 'text',		'length' => 255,	'notnull' => true),
		'config'			=> array('type' => 'clob')
	);

	$ilDB->createTable('pdfgen_conf', $fields);
	$ilDB->addPrimaryKey('pdfgen_conf', array('conf_id'));
	$ilDB->createSequence('pdfgen_conf');
}

if (!$ilDB->tableExists('pdfgen_map'))
{
	$fields = array (
		'map_id'			=> array('type' => 'integer', 	'length' => 4,		'notnull' => true),
		'service'			=> array('type' => 'text', 		'length' => 255,	'notnull' => true),
		'purpose'			=> array('type' => 'text',	  	'length' => 255,	'notnull' => true),
		'preferred'			=> array('type' => 'text',		'length' => 255,	'notnull' => true),
		'selected'			=> array('type' => 'text',		'length' => 255,	'notnull' => true)
	);

	$ilDB->createTable('pdfgen_map', $fields);
	$ilDB->addPrimaryKey('pdfgen_map', array('map_id'));
	$ilDB->createSequence('pdfgen_map');
}
?>
<#2>
<?php
global $ilDB;
if (!$ilDB->tableExists('pdfgen_purposes'))
{
	$fields = array (
		'purpose_id'		=> array('type' => 'integer', 	'length' => 4,		'notnull' => true),
		'service'			=> array('type' => 'text', 		'length' => 255,	'notnull' => true),
		'purpose'			=> array('type' => 'text',	  	'length' => 255,	'notnull' => true),
	);

	$ilDB->createTable('pdfgen_purposes', $fields);
	$ilDB->addPrimaryKey('pdfgen_purposes', array('purpose_id'));
	$ilDB->createSequence('pdfgen_purposes');
}
?>
<#3>
<?php
	include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
	ilDBUpdateNewObjectType::addAdminNode('pdfg', 'PDFGeneration');
?>
<#4>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#5>
<?php
if (!$ilDB->tableExists('pdfgen_renderer'))
{
	$fields = array (
		'renderer_id'	=> array('type' => 'integer', 	'length' => 4,		'notnull' => true),
		'renderer'		=> array('type' => 'text',	  	'length' => 255,	'notnull' => true),
		'path'			=> array('type' => 'text',	  	'length' => 255,	'notnull' => true),
	);

	$ilDB->createTable('pdfgen_renderer', $fields);
	$ilDB->addPrimaryKey('pdfgen_renderer', array('renderer_id'));
	$ilDB->createSequence('pdfgen_renderer');
}

if (!$ilDB->tableExists('pdfgen_renderer_avail'))
{
	$fields = array (
		'availability_id'	=> array('type' => 'integer', 	'length' => 4,		'notnull' => true),
		'service'			=> array('type' => 'text', 		'length' => 255,	'notnull' => true),
		'purpose'			=> array('type' => 'text',	  	'length' => 255,	'notnull' => true),
		'renderer'			=> array('type' => 'text',	  	'length' => 255,	'notnull' => true),
	);

	$ilDB->createTable('pdfgen_renderer_avail', $fields);
	$ilDB->addPrimaryKey('pdfgen_renderer_avail', array('availability_id'));
	$ilDB->createSequence('pdfgen_renderer_avail');
}
?>
<#6>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#7>
<?php
$ilDB->insert('pdfgen_renderer',
			  array(
				  'renderer_id' => array('integer', $ilDB->nextId('pdfgen_renderer')),
				  'renderer'	=> array('text', 'TCPDF'),
				  'path'		=> array('text', 'Services/PDFGeneration/classes/renderer/tcpdf/class.ilTCPDFRenderer.php')
			  )
);
?>
<#8>
<?php
$ilDB->insert('pdfgen_renderer',
			  array(
				  'renderer_id' => array('integer',$ilDB->nextId('pdfgen_renderer')),
				  'renderer'	=> array('text','PhantomJS'),
				  'path'		=> array('text','Services/PDFGeneration/classes/renderer/phantomjs/class.ilPhantomJSRenderer.php')
			  )
);
?>
<#9>
<?php
$ilDB->insert('pdfgen_renderer_avail',
			  array(
				  'availability_id' => array('integer', $ilDB->nextId('pdfgen_renderer_avail')),
				  'service' 	=> array('text', 'Test'),
				  'purpose' 	=> array('text', 'PrintViewOfQuestions'),
				  'renderer'	=> array('text', 'PhantomJS')
			  )
);
?>
<#10>
<?php
$ilDB->insert('pdfgen_renderer_avail',
			  array(
				  'availability_id' => array('integer', $ilDB->nextId('pdfgen_renderer_avail')),
				  'service' 	=> array('text', 'Test'),
				  'purpose' 	=> array('text', 'UserResult'),
				  'renderer'	=> array('text', 'PhantomJS')
			  )
);
?>
<#11>
<?php
$ilDB->insert('pdfgen_renderer_avail',
			  array(
				  'availability_id' => array('integer', $ilDB->nextId('pdfgen_renderer_avail')),
				  'service' 	=> array('text', 'Test'),
				  'purpose' 	=> array('text', 'PrintViewOfQuestions'),
				  'renderer'	=> array('text', 'TCPDF')
			  )
);
?>
<#12>
<?php
$ilDB->insert('pdfgen_renderer_avail',
			  array(
				  'availability_id' => array('integer', $ilDB->nextId('pdfgen_renderer_avail')),
				  'service' 	=> array('text', 'Test'),
				  'purpose' 	=> array('text', 'UserResult'),
				  'renderer'	=> array('text', 'TCPDF')
			  )
);

?>