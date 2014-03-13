<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilSCORM2004DeleteData
*
* @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
*
* @ingroup ModulesScormAicc
*/
class ilSCORM2004DeleteData
{
	public function removeCMIDataForPackage($packageId)
	{
		global $ilDB;

		$res = $ilDB->queryF('
			SELECT cmi_node.cmi_node_id 
			FROM cmi_node, cp_node 
			WHERE cp_node.slm_id = %s AND cmi_node.cp_node_id = cp_node.cp_node_id',
			array('integer'),
			array($packageId)
		);		
		while($data = $ilDB->fetchAssoc($res)) 
		{
			$cmi_node_values[] = $data['cmi_node_id'];
		}
		self::removeCMIDataForNodes($cmi_node_values);

		//custom
		$query = 'DELETE FROM cmi_custom WHERE obj_id = %s';
		$ilDB->manipulateF($query, array('integer'), array($packageId));

		//g_objective
		$query = 'DELETE FROM cmi_gobjective WHERE scope_id = %s';
		$ilDB->manipulateF($query, array('integer'), array($packageId));
	}

	public function removeCMIDataForUser($user_id)
	{
		global $ilDB;

		//get all cmi_nodes to delete
		$res = $ilDB->queryF('
			SELECT cmi_node.cmi_node_id 
			FROM cmi_node, cp_node 
			WHERE cmi_node.user_id = %s AND cmi_node.cp_node_id = cp_node.cp_node_id',
			array('integer'),
			array($user_id)
		);		
		
		while($data = $ilDB->fetchAssoc($res)) 
		{
			$cmi_node_values[] = $data['cmi_node_id'];
		}
		self::removeCMIDataForNodes($cmi_node_values);

		//custom
		$ilDB->manipulateF(
			'DELETE FROM cmi_custom WHERE user_id = %s',
			array('integer'),
			array($user_id)
		);

		//gobjective
		$ilDB->manipulateF(
			'DELETE FROM cmi_gobjective WHERE user_id = %s',
			array('integer'),
			array($user_id)
		);
	}
	
	public function removeCMIDataForUserAndPackage($user_id,$packageId)
	{
		global $ilDB;

		//get all cmi_nodes to delete
		$res = $ilDB->queryF('
			SELECT cmi_node.cmi_node_id 
			FROM cmi_node, cp_node 
			WHERE cmi_node.user_id = %s AND cmi_node.cp_node_id = cp_node.cp_node_id AND cp_node.slm_id = %s',
			array('integer','integer'),
			array($user_id,$packageId)
		);		
		while($data = $ilDB->fetchAssoc($res)) 
		{
			$cmi_node_values[] = $data['cmi_node_id'];
		}
		self::removeCMIDataForNodes($cmi_node_values);

		//custom
		$ilDB->manipulateF(
			'DELETE FROM cmi_custom WHERE user_id = %s AND obj_id = %s',
			array('integer','integer'),
			array($user_id,$packageId)
		);
		
		//gobjective
		$ilDB->manipulateF(
			'DELETE FROM cmi_gobjective WHERE user_id = %s AND scope_id = %s',
			array('integer','integer'),
			array($user_id,$packageId)
		);
	}
	
	public function removeCMIDataForNodes($cmi_node_values)
	{
		global $ilDB;
		
		//cmi interaction nodes
		$cmi_inodes = array();
		
		$query = 'SELECT cmi_interaction_id FROM cmi_interaction WHERE '
			. $ilDB->in('cmi_interaction.cmi_node_id', $cmi_node_values, false, 'integer');
		$res = $ilDB->query($query);		
		while($data = $ilDB->fetchAssoc($res)) 
		{
			$cmi_inode_values[] = $data['cmi_interaction_id'];
		}

		//response
		$query = 'DELETE FROM cmi_correct_response WHERE '
			   . $ilDB->in('cmi_correct_response.cmi_interaction_id', $cmi_inode_values, false, 'integer');
		$ilDB->manipulate($query);
			
		//objective interaction
		$query = 'DELETE FROM cmi_objective WHERE '
			   . $ilDB->in('cmi_objective.cmi_interaction_id', $cmi_inode_values, false, 'integer');
		$ilDB->manipulate($query);	
			
		//objective
		$query = 'DELETE FROM cmi_objective WHERE '
			   . $ilDB->in('cmi_objective.cmi_node_id', $cmi_node_values, false, 'integer');
		$ilDB->manipulate($query);	
				
		//interaction
		$query = 'DELETE FROM cmi_interaction WHERE '
		 	   . $ilDB->in('cmi_interaction.cmi_node_id', $cmi_node_values, false, 'integer');
		$ilDB->manipulate($query);	
			
		//comment
		$query = 'DELETE FROM cmi_comment WHERE '
			   . $ilDB->in('cmi_comment.cmi_node_id', $cmi_node_values, false, 'integer');
		$ilDB->manipulate($query);	
					
		//node
		$query = 'DELETE FROM cmi_node WHERE '
			   . $ilDB->in('cmi_node.cmi_node_id', $cmi_node_values, false, 'integer');
		$ilDB->manipulate($query);

	}
}
?>
