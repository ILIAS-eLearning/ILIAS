<#1>
<?php
/** @var $ilDB ilDB */
if(!$ilDB->tableExists('saml_attribute_mapping'))
{
	$ilDB->createTable(
		'saml_attribute_mapping',
		array(
			'idp_id'        => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true
			),
			'attribute'     => array(
				'type'    => 'text',
				'length'  => '75',
				'notnull' => true
			),
			'idp_attribute' => array(
				'type'    => 'text',
				'length'  => '1000',
				'notnull' => false,
				'default' => null
			),
		)
	);
}
?>
<#2>
<?php
$ilDB->addPrimaryKey('saml_attribute_mapping', array('idp_id', 'attribute'));
?>
<#3>
<?php
if(!$ilDB->tableColumnExists('saml_attribute_mapping', 'idp_attribute'))
{
	$ilDB->modifyTableColumn('saml_attribute_mapping', 'idp_attribute', array(
		'type'    => 'text',
		'length'  => '1000',
		'notnull' => false,
		'default' => null
	));
}
?>
<#4>
<?php
if(!$ilDB->tableColumnExists('saml_attribute_mapping', 'update_automatically'))
{
	$ilDB->addTableColumn('saml_attribute_mapping', 'update_automatically', array(
		'type'    => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#5>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#6>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#7>
<?php
if(!$ilDB->tableExists('saml_idp_settings'))
{
	$ilDB->createTable(
		'saml_idp_settings',
		array(
			'idp_id'        => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true
			),
			'is_active'     => array(
				'type'    => 'integer',
				'length'  => 1,
				'notnull' => true
			)
		)
	);
}
?>
<#8>
<?php
$ilDB->addPrimaryKey('saml_idp_settings', array('idp_id'));
?>
<#9>
<?php
if(!$ilDB->tableColumnExists('saml_idp_settings', 'allow_local_auth'))
{
	$ilDB->addTableColumn('saml_idp_settings', 'allow_local_auth',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	);
}
if(!$ilDB->tableColumnExists('saml_idp_settings', 'default_role_id'))
{
	$ilDB->addTableColumn('saml_idp_settings', 'default_role_id',
		array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	);
}
if(!$ilDB->tableColumnExists('saml_idp_settings', 'uid_claim'))
{
	$ilDB->addTableColumn('saml_idp_settings', 'uid_claim',
		array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false,
			'default' => null
		)
	);
}
if(!$ilDB->tableColumnExists('saml_idp_settings', 'login_claim'))
{
	$ilDB->addTableColumn('saml_idp_settings', 'login_claim',
		array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false,
			'default' => null
		)
	);
}
if(!$ilDB->tableColumnExists('saml_idp_settings', 'sync_status'))
{
	$ilDB->addTableColumn('saml_idp_settings', 'sync_status',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	);
}
if(!$ilDB->tableColumnExists('saml_idp_settings', 'account_migr_status'))
{
	$ilDB->addTableColumn('saml_idp_settings', 'account_migr_status',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	);
}
?>
<#10>
<?php
$ilDB->manipulateF("UPDATE usr_data SET auth_mode = %s WHERE auth_mode = %s", array('text', 'text'), array('saml_1', 'saml'));
?>
