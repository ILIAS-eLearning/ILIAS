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
<#4188>
<?php

// #12845
$set = $ilDB->query("SELECT od.owner, prtf.id prtf_id, pref.value public".
	", MIN(acl.object_id) acl_type".
	" FROM usr_portfolio prtf".
	" JOIN object_data od ON (od.obj_id = prtf.id)".
	" LEFT JOIN usr_portf_acl acl ON (acl.node_id = prtf.id)".
	" LEFT JOIN usr_pref pref ON (pref.usr_id = od.owner".
	" AND pref.keyword = ".$ilDB->quote("public_profile", "text").")".
	" WHERE prtf.is_default = ".$ilDB->quote(1, "integer").
	" GROUP BY od.owner, prtf.id, pref.value");
while($row = $ilDB->fetchAssoc($set))
{	
	$acl_type = (int)$row["acl_type"];
	$pref = trim($row["public"]);
	
	// portfolio is not published, remove as profile
	if($acl_type >= 0)
	{
		$ilDB->manipulate("UPDATE usr_portfolio".
			" SET is_default = ".$ilDB->quote(0, "integer").
			" WHERE id = ".$ilDB->quote($row["prtf_id"], "integer"));		
		$new_pref = "n";
	}
	// check if portfolio sharing matches user preference
	else 
	{		
		// registered vs. published
		$new_pref = ($acl_type < -1)
			? "g"
			: "y";		
	}	
	
	if($pref)
	{
		if($pref != $new_pref)
		{
			$ilDB->manipulate("UPDATE usr_pref".
				" SET value = ".$ilDB->quote($new_pref, "text").
				" WHERE usr_id = ".$ilDB->quote($row["owner"], "integer").
				" AND keyword = ".$ilDB->quote("public_profile", "text"));
		}
	}	
	else
	{
		$ilDB->manipulate("INSERT INTO usr_pref (usr_id, keyword, value) VALUES".
			" (".$ilDB->quote($row["owner"], "integer").
			", ".$ilDB->quote("public_profile", "text").
			", ".$ilDB->quote($new_pref, "text").")");
	}	
}

?>

<#4189>
<?php
$ilDB->modifyTableColumn(
		'object_data', 
		'title',
		array(
			"type" => "text", 
			"length" => 255, 
			"notnull" => false,
			'fixed' => true
		)
	);
?>

