<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../');
include_once 'include/inc.header.php';
/* @var ILIAS\DI\Container $DIC */

if( !$DIC->access()->checkAccess('read', '', SYSTEM_FOLDER_ID) )
{
	die('administrative privileges only!');
}

// ------------------------------------------------------------------------------------------------

function step1(ilDBInterface $db)
{
	// get tst type id
	$row = $db->fetchAssoc($db->queryF(
		"SELECT obj_id tst_type_id FROM object_data WHERE type = %s AND title = %s",
		array('text', 'text'), array('typ', 'tst')
	));
	$tstTypeId = $row['tst_type_id'];
	
	// get 'write' operation id
	$row = $db->fetchAssoc($db->queryF(
		"SELECT ops_id FROM rbac_operations WHERE operation = %s AND class = %s",
		array('text', 'text'), array('write', 'general')
	));
	$writeOperationId = $row['ops_id'];
	
	// register new 'object' rbac operation for tst
	$resultsOperationId = $db->nextId('rbac_operations');
	$db->insert('rbac_operations', array(
		'ops_id' => array('integer', $resultsOperationId),
		'operation' => array('text', 'tst_results'),
		'description' => array('text', 'view the results of test participants'),
		'class' => array('text', 'object'),
		'op_order' => array('integer', 7050)
	));
	$db->insert('rbac_ta', array(
		'typ_id' => array('integer', $tstTypeId),
		'ops_id' => array('integer', $resultsOperationId)
	));
	
	// update existing role templates and grant new operation for all templates having 'write' granted
	$res = $db->queryF(
		"SELECT rol_id, parent FROM rbac_templates WHERE type = %s AND ops_id = %s",
		array('text', 'integer'), array('tst', $writeOperationId)
	);
	$stmt = $db->prepareManip("
		INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (?, ?, ?, ?)
		", array('integer', 'text', 'integer', 'integer')
	);
	while( $row = $db->fetchAssoc($res) )
	{
		$db->execute($stmt, array($row['rol_id'], 'tst', $resultsOperationId, $row['parent']));
	}
}

// ------------------------------------------------------------------------------------------------

function step2(ilDBInterface $db)
{
	// get 'write' operation id
	$row = $db->fetchAssoc($db->queryF(
		"SELECT ops_id FROM rbac_operations WHERE operation = %s AND class = %s",
		array('text', 'text'), array('tst_results', 'object')
	));
	$resultsOperationId = $row['ops_id'];
	
	// get 'write' operation id
	$row = $db->fetchAssoc($db->queryF(
		"SELECT ops_id FROM rbac_operations WHERE operation = %s AND class = %s",
		array('text', 'text'), array('write', 'general')
	));
	$writeOperationId = $row['ops_id'];
	
	// get roles (not rolts) having 'tst_results' registered in rbac_template
	$res = $db->queryF("
		SELECT rol_id FROM rbac_templates INNER JOIN object_data
		ON obj_id = rol_id AND object_data.type = %s WHERE rbac_templates.type = %s AND ops_id = %s
		", array('text', 'text', 'integer'), array('role', 'tst', $resultsOperationId)
	);
	$roleIds = array();
	while( $row = $db->fetchAssoc($res) )
	{
		$roleIds[] = $row['rol_id'];
	}
	
	// get existing test object references
	$res = $db->queryF("
		SELECT oref.ref_id FROM object_data odat INNER JOIN object_reference oref
		ON oref.obj_id = odat.obj_id WHERE odat.type = %s
		", array('text'), array('tst')
	);
	$tstRefs = array();
	while( $row = $db->fetchAssoc($res) )
	{
		$tstRefs[] = $row['ref_id'];
	}
	
	// complete 'tst_results' permission for all existing role/reference combination that have 'write' permission
	$stmt = $db->prepareManip("
		UPDATE rbac_pa SET ops_id = ? WHERE rol_id = ? AND ref_id = ?
		", array('text', 'integer', 'integer')
	);
	$IN_roles = $db->in('rol_id', $roleIds, false, 'integer');
	$IN_tstrefs = $db->in('ref_id', $tstRefs, false, 'integer');
	$res = $db->query("SELECT * FROM rbac_pa WHERE {$IN_roles} AND {$IN_tstrefs}");
	while( $row = $db->fetchAssoc($res) )
	{
		$perms = unserialize($row['ops_id']);
		
		if( in_array($writeOperationId, $perms) && !in_array($resultsOperationId, $perms) )
		{
			$perms[] = $resultsOperationId;
			$db->execute($stmt, array(serialize($perms), $row['rol_id'], $row['ref_id']));
		}
	}
}

// ------------------------------------------------------------------------------------------------


//step1($DIC->database());
//step2($DIC->database());
