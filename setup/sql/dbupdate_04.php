<#4183>
<?php
	if (!$ilDB->tableColumnExists('il_poll', 'result_sort'))
	{
		$ilDB->addTableColumn('il_poll', 'result_sort', array(
			"type" => "integer",
			"notnull" => true,
			"length" => 1,
			"default" => 0
		));
	}
?>
<#4184>
<?php
	if (!$ilDB->tableColumnExists('il_poll', 'non_anon'))
	{
		$ilDB->addTableColumn('il_poll', 'non_anon', array(
			"type" => "integer",
			"notnull" => true,
			"length" => 1,
			"default" => 0
		));
	}
?>
<#4185>
<?php

if(!$ilDB->tableColumnExists('il_blog','abs_shorten')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_shorten',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 0
        ));
}

if(!$ilDB->tableColumnExists('il_blog','abs_shorten_len')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_shorten_len',
        array(
            'type' => 'integer',
			'length' => 2,
            'notnull' => false,
            'default' => 0
        ));
}

if(!$ilDB->tableColumnExists('il_blog','abs_image')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_image',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 0
        ));
}

if(!$ilDB->tableColumnExists('il_blog','abs_img_width')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_img_width',
        array(
            'type' => 'integer',
			'length' => 2,
            'notnull' => false,
            'default' => 0
        ));
}

if(!$ilDB->tableColumnExists('il_blog','abs_img_height')) 
{
    $ilDB->addTableColumn(
        'il_blog',
        'abs_img_height',
        array(
            'type' => 'integer',
			'length' => 2,
            'notnull' => false,
            'default' => 0
        ));
}

?>
<#4186>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4187>
<?php

if( !$ilDB->tableExists('usr_data_multi') )
{
	$ilDB->createTable('usr_data_multi', array(
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true
		),
		'value' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false,
		)
	));
}

?>