<#1>
<?php

if(!$ilDB->tableExists('il_meta_oer_stat'))
{
	$ilDB->createTable('il_meta_oer_stat', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
		),
		'href_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'blocked' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));
}
?>
<#2>
<?php

if($ilDB->tableExists('il_md_cpr_selections'))
{
	if(!$ilDB->tableColumnExists('il_md_cpr_selections','is_default'))
	{
		$ilDB->addTableColumn('il_md_cpr_selections', 'is_default', array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		));
	}

	$id = $ilDB->nextId('il_md_cpr_selections');
	$ilDB->insert("il_md_cpr_selections", array(
			'entry_id' => array('integer',$id),
			'title' => array('text', 'All rights reserved'),
			'description' => array('clob', ''),
			'copyright' => array('clob', 'This work has all rights reserved by the owner.'),
			'language' => array('text', 'en'),
			'costs' => array('integer', '0'),
			'cpr_restrictions' => array('integer', '1'),
			'is_default' => array('integer', '1')
		)
	);
}
?>
