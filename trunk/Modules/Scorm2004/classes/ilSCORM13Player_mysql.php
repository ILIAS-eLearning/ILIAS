<?php

self::$SQLCOMMAND['view_cmi_node'] = 'SELECT cmi_node.* 
	FROM cmi_node 
	INNER JOIN cp_node ON cmi_node.cp_node_id = cp_node.cp_node_id
	WHERE cmi_node.user_id=? AND cp_node.slm_id=?';

self::$SQLCOMMAND['view_cmi_comment'] = 'SELECT cmi_comment.* 
	FROM cmi_comment 
	INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_comment.cmi_node_id 
	INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
	WHERE cmi_node.user_id=? AND cp_node.slm_id=?';

self::$SQLCOMMAND['view_cmi_correct_response'] = 'SELECT cmi_correct_response.* 
	FROM cmi_correct_response 
	INNER JOIN cmi_interaction 
	ON cmi_interaction.cmi_interaction_id = cmi_correct_response.cmi_interaction_id 
	INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
	INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
	WHERE cmi_node.user_id=? AND cp_node.slm_id=?';

self::$SQLCOMMAND['view_cmi_interaction'] = 'SELECT cmi_interaction.* 
	FROM cmi_interaction 
	INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
	INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
	WHERE cmi_node.user_id=? AND cp_node.slm_id=?';

self::$SQLCOMMAND['view_cmi_objective'] = 'SELECT cmi_objective.* 
	FROM cmi_objective 
	INNER JOIN cmi_node ON cmi_node.cmi_node_id = cmi_objective.cmi_node_id 
	INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
	WHERE cmi_node.user_id=? AND cp_node.slm_id=?';

self::$SQLCOMMAND['view_cmi_package'] = 'SELECT usr_data.usr_id AS user_id, 
	CONCAT(usr_data.firstname, " ", usr_data.lastname) AS learner_name, 
	sahs_lm.id AS slm_id , sahs_lm.default_lesson_mode AS mode, sahs_lm.credit
	FROM usr_data , cp_package
	INNER JOIN sahs_lm ON cp_package.obj_id = sahs_lm.id 
	WHERE usr_data.usr_id=? AND sahs_lm.id=?';

self::$SQLCOMMAND['delete_cmi_correct_responses'] = 'DELETE FROM 
	cmi_correct_response WHERE cmi_interaction_id IN (
	SELECT cmi_interaction.cmi_interaction_id FROM cmi_interaction 
	INNER JOIN cmi_node ON cmi_node.cmi_node_id=cmi_interaction.cmi_node_id 
	INNER JOIN cp_node ON cmi_node.cp_node_id=cp_node.cp_node_id 
	WHERE cmi_node.user_id=? AND cp_node.slm_id=?)';

self::$SQLCOMMAND['delete_cmi_interactions'] = 'DELETE FROM cmi_interaction 
	WHERE cmi_node_id IN (
	SELECT cmi_node.cmi_node_id FROM cmi_node 
	INNER JOIN cp_node ON cmi_node.cp_node_id=cp_node.cp_node_id 
	WHERE cmi_node.user_id=? AND cp_node.slm_id=?)';

self::$SQLCOMMAND['delete_cmi_comments'] = 'DELETE FROM cmi_comment 
	WHERE cmi_node_id IN (
	SELECT cmi_node.cmi_node_id FROM cmi_node 
	INNER JOIN cp_node ON cmi_node.cp_node_id=cp_node.cp_node_id 
	WHERE cmi_node.user_id=? AND cp_node.slm_id=?)';

self::$SQLCOMMAND['delete_cmi_objectives'] = 'DELETE FROM cmi_objective 
	WHERE cmi_node_id IN (
	SELECT cmi_node.cmi_node_id FROM cmi_node 
	INNER JOIN cp_node ON cmi_node.cp_node_id=cp_node.cp_node_id 
	WHERE cmi_node.user_id=? AND cp_node.slm_id=?)';

self::$SQLCOMMAND['delete_cmi_nodes'] = 'DELETE FROM cmi_node 
	WHERE user_id=? AND cp_node_id IN (
	SELECT cp_node_id FROM cp_node 
	WHERE slm_id=?)';

self::$SQLCOMMAND['delete_cmi_correct_response'] = 'DELETE FROM cmi_correct_response 
	WHERE cmi_interaction_id IN (
	SELECT cmi_interaction.cmi_interaction_id FROM cmi_interaction 
	INNER JOIN cmi_node ON cmi_node.cmi_node_id=cmi_interaction.cmi_node_id 
	WHERE cmi_node.cp_node_id=?)';

self::$SQLCOMMAND['delete_cmi_interaction'] = 'DELETE FROM cmi_interaction 
	WHERE cmi_node_id IN (
	SELECT cmi_node.cmi_node_id FROM cmi_node 
	WHERE cmi_node.cp_node_id=?)';

self::$SQLCOMMAND['delete_cmi_comment'] = 'DELETE FROM cmi_comment 
	WHERE cmi_node_id IN (
	SELECT cmi_node.cmi_node_id FROM cmi_node 
	WHERE cmi_node.cp_node_id=?)';

self::$SQLCOMMAND['delete_cmi_objective'] = 'DELETE FROM cmi_objective 
	WHERE cmi_node_id IN (
	SELECT cmi_node.cmi_node_id FROM cmi_node 
	WHERE cmi_node.cp_node_id=?)';

self::$SQLCOMMAND['delete_cmi_node'] = 'DELETE FROM cmi_node WHERE cp_node_id=?';


?>
