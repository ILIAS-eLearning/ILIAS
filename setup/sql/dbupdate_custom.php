<#1>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('ltis', 'LTI Settings');

if (!$ilDB->tableExists('lti_ext_consumer'))
{
	$ilDB->createTable('lti_ext_consumer', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
		),
		'description' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
		),
		'prefix' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
		),
		'consumer_key' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
		),
		'consumer_secret' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
		),
		'user_language' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
		),
		'role' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'active' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('lti_ext_consumer',array('id'));
	$ilDB->createSequence('lti_ext_consumer');
}

if (!$ilDB->tableExists('lti_ext_consumer_otype'))
{
	$ilDB->createTable('lti_ext_consumer_otype', array(
		'consumer_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'object_type' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true
		),
	));
	$ilDB->addPrimaryKey('lti_ext_consumer_otype',array('consumer_id', 'object_type'));
}
?>
<#2>
<?php
if (!$ilDB->tableExists('lti2_consumer'))
{
	$ilDB->createTable('lti2_consumer', array(
		'consumer_pk' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'name' => array(
			'type' => 'text',
			'length' => 50,
			'notnull' => true
		),
		'consumer_key256' => array(
			'type' => 'text',
			'length' => 256,
			'notnull' => true
		),
		'consumer_key' => array(
			'type' => 'blob',
			'default' => null
		),
		'secret' => array(
			'type' => 'text',
			'length' => 1024,
			'notnull' => true
		),
		'lti_version' => array(
			'type' => 'text',
			'length' => 10,
			'default' => null
		),
		'consumer_name' => array(
			'type' => 'text',
			'length' => 255,
			'default' => null
		),
		'consumer_version' => array(
			'type' => 'text',
			'length' => 255,
			'default' => null
		),
		'consumer_guid' => array(
			'type' => 'text',
			'length' => 1024,
			'default' => null
		),
		'profile' => array(
			'type' => 'blob',
			'default' => null
		),
		'tool_proxy' => array(
			'type' => 'blob',
			'default' => null
		),
		'settings' => array(
			'type' => 'blob',
			'default' => null
		),
		'protected' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true
		),
		'enabled' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true
		),
		'enable_from' => array(
			'type' => 'timestamp',
			'default' => null
		),
		'enable_until' => array(
			'type' => 'timestamp',
			'default' => null
		),
		'last_access' => array(
			'type' => 'timestamp',
			'default' => null
		),
		'created' => array(
			'type' => 'timestamp',
			'notnull' => true
		),
		'updated' => array(
			'type' => 'timestamp',
			'notnull' => true
		)
	));
	$ilDB->addPrimaryKey('lti2_consumer',array('consumer_pk'));
	$ilDB->addUniqueConstraint('lti2_consumer', array('consumer_key256'), 'u1');
	$ilDB->createSequence('lti2_consumer');  
}
?>
<#3>
<?php
if (!$ilDB->tableExists('lti2_tool_proxy'))
{
	$ilDB->createTable('lti2_tool_proxy', array(
		'tool_proxy_pk' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'tool_proxy_id' => array(
			'type' => 'text',
			'length' => 32,
			'notnull' => true
		),
		'consumer_pk' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'tool_proxy' => array(
			'type' => 'blob',
			'notnull' => true
		),
		'created' => array(
			'type' => 'timestamp',
			'notnull' => true
		),
		'updated' => array(
			'type' => 'timestamp',
			'notnull' => true
		)
	));
	$ilDB->addPrimaryKey('lti2_tool_proxy',array('tool_proxy_pk'));
// ALTER TABLE lti2_tool_proxy
  // ADD CONSTRAINT lti2_tool_proxy_lti2_consumer_FK1 FOREIGN KEY (consumer_pk)
  // REFERENCES lti2_consumer (consumer_pk);
	$ilDB->addIndex('lti2_tool_proxy',array('consumer_pk'),'i1');
	$ilDB->addUniqueConstraint('lti2_tool_proxy', array('tool_proxy_id'), 'u1');
	$ilDB->createSequence('lti2_tool_proxy');
}
?>
<#4>
<?php
if (!$ilDB->tableExists('lti2_nonce'))
{
	$ilDB->createTable('lti2_nonce', array(
		'consumer_pk' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'value' => array(
			'type' => 'text',
			'length' => 32,
			'notnull' => true
		),
		'expires' => array(
			'type' => 'timestamp',
			'notnull' => true
		)
	));
	$ilDB->addPrimaryKey('lti2_nonce',array('consumer_pk','value'));
// ALTER TABLE lti2_nonce
  // ADD CONSTRAINT lti2_nonce_lti2_consumer_FK1 FOREIGN KEY (consumer_pk)
  // REFERENCES lti2_consumer (consumer_pk);
}
?>
<#5>
<?php
if (!$ilDB->tableExists('lti2_context'))
{
	$ilDB->createTable('lti2_context', array(
		'context_pk' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'consumer_pk' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'lti_context_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true
		),
		'settings' => array(
			'type' => 'blob',
			'default' => null
		),
		'created' => array(
			'type' => 'timestamp',
			'notnull' => true
		),
		'updated' => array(
			'type' => 'timestamp',
			'notnull' => true
		)
	));
	$ilDB->addPrimaryKey('lti2_context',array('context_pk'));
// ALTER TABLE lti2_context
  // ADD CONSTRAINT lti2_context_lti2_consumer_FK1 FOREIGN KEY (consumer_pk)
  // REFERENCES lti2_consumer (consumer_pk);
	$ilDB->addIndex('lti2_context',array('consumer_pk'),'i1');
	$ilDB->createSequence('lti2_context');

}
?>
<#6>
<?php
if (!$ilDB->tableExists('lti2_resource_link'))
{
	$ilDB->createTable('lti2_resource_link', array(
		'resource_link_pk' => array(
			'type' => 'integer',
			'length' => 4
		),
		'context_pk' => array(
			'type' => 'integer',
			'length' => 4,
			'default' => null
		),
		'consumer_pk' => array(
			'type' => 'integer',
			'length' => 4,
			'default' => null
		),
		'lti_resource_link_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true
		),
		'settings' => array(
			'type' => 'blob'
		),
		'primary_resource_link_pk' => array(
			'type' => 'integer',
			'length' => 4,
			'default' => null
		),
		'share_approved' => array(
			'type' => 'integer',
			'length' => 1,
			'default' => null
		),
		'created' => array(
			'type' => 'timestamp',
			'notnull' => true
		),
		'updated' => array(
			'type' => 'timestamp',
			'notnull' => true
		)
	));
	$ilDB->addPrimaryKey('lti2_resource_link',array('resource_link_pk'));
// ALTER TABLE lti2_resource_link
  // ADD CONSTRAINT lti2_resource_link_lti2_context_FK1 FOREIGN KEY (context_pk)
  // REFERENCES lti2_context (context_pk);
// ALTER TABLE lti2_resource_link
  // ADD CONSTRAINT lti2_resource_link_lti2_resource_link_FK1 FOREIGN KEY (primary_resource_link_pk)
  // REFERENCES lti2_resource_link (resource_link_pk);
	$ilDB->addIndex('lti2_resource_link',array('consumer_pk'),'i1');
	$ilDB->addIndex('lti2_resource_link',array('context_pk'),'i2');
	$ilDB->createSequence('lti2_resource_link');
}
?>
<#7>
<?php
if (!$ilDB->tableExists('lti2_user_result'))
{
	$ilDB->createTable('lti2_user_result', array(
		'user_pk' => array(
			'type' => 'integer',
			'length' => 4
		),
		'resource_link_pk' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'lti_user_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true
		),
		'lti_result_sourcedid' => array(
			'type' => 'text',
			'length' => 1024,
			'notnull' => true
		),
		'created' => array(
			'type' => 'timestamp',
			'notnull' => true
		),
		'updated' => array(
			'type' => 'timestamp',
			'notnull' => true
		)
	));
	$ilDB->addPrimaryKey('lti2_user_result',array('user_pk'));
// ALTER TABLE lti2_user_result
  // ADD CONSTRAINT lti2_user_result_lti2_resource_link_FK1 FOREIGN KEY (resource_link_pk)
  // REFERENCES lti2_resource_link (resource_link_pk);
	$ilDB->addIndex('lti2_user_result',array('resource_link_pk'),'i1');
	$ilDB->createSequence('lti2_user_result');
}
?>
<#8>
<?php
if (!$ilDB->tableExists('lti2_share_key'))
{
	$ilDB->createTable('lti2_share_key', array(
		'share_key_id' => array(
			'type' => 'text',
			'length' => 32,
			'notnull' => true
		),
		'resource_link_pk' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'auto_approve' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true
		),
		'expires' => array(
			'type' => 'timestamp',
			'notnull' => true
		)
	));
	$ilDB->addPrimaryKey('lti2_share_key',array('share_key_id'));
// ALTER TABLE lti2_share_key
  // ADD CONSTRAINT lti2_share_key_lti2_resource_link_FK1 FOREIGN KEY (resource_link_pk)
  // REFERENCES lti2_resource_link (resource_link_pk);
	$ilDB->addIndex('lti2_share_key',array('resource_link_pk'),'i1');
}
?>
<#9>
<?php
if(!$ilDB->tableColumnExists('lti_ext_consumer','local_role_always_member'))
{
	$ilDB->addTableColumn('lti_ext_consumer', 'local_role_always_member', array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		));
}
?>
<#10>
<?php
if(!$ilDB->tableColumnExists('lti_ext_consumer','default_skin'))
{
	$ilDB->addTableColumn('lti_ext_consumer', 'default_skin', array(
			'type' => 'text',
			'length' => 50,
			'default' => null
		));
}
?>
<#11>
<?php
if($ilDB->tableColumnExists('lti_ext_consumer', 'consumer_key'))
{
	$ilDB->dropTableColumn('lti_ext_consumer', 'consumer_key');
}
if($ilDB->tableColumnExists('lti_ext_consumer', 'consumer_secret'))
{
	$ilDB->dropTableColumn('lti_ext_consumer', 'consumer_secret');
}
if($ilDB->tableColumnExists('lti_ext_consumer', 'active'))
{
	$ilDB->dropTableColumn('lti_ext_consumer', 'active');
}
?>

<#12>
<?php
if (!$ilDB->tableExists('lti_int_provider_obj'))
{
	$ilDB->createTable('lti_int_provider_obj', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'consumer_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		
		'enabled' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		),
		'admin' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		),
		'tutor' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		),
		'member' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		)
	));
	$ilDB->addPrimaryKey('lti_int_provider_obj',array('obj_id','consumer_id'));
}
?>

