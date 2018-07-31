<?php /* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../');
include_once 'include/inc.header.php';

/* @var ilAccess $ilAccess */
if( !$ilAccess->checkAccess('read', '', SYSTEM_FOLDER_ID) )
{
	die('administrative privileges only!');
}

/* @var \ILIAS\DI\Container $DIC */
try
{
	if( !$DIC->database()->tableColumnExists('qpl_fb_specific', 'question') )
	{
		// add new table column for indexing different question gaps in assClozeTest
		$DIC->database()->addTableColumn('qpl_fb_specific', 'question', array(
			'type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => null
		));
		
		// give all other qtypes having a single subquestion the question index 0
		$DIC->database()->manipulateF(
			"UPDATE qpl_fb_specific SET question = %s WHERE question_fi NOT IN(
				SELECT question_id FROM qpl_questions
				INNER JOIN qpl_qst_type ON question_type_id = question_type_fi
			  	WHERE type_tag = %s
			)", array('integer', 'text'), array(0, 'assClozeTest')
		);
		
		// for all assClozeTest entries - migrate the gap feedback indexes from answer field to questin field
		$DIC->database()->manipulateF(
			"UPDATE qpl_fb_specific SET question = answer WHERE question_fi IN(
				SELECT question_id FROM qpl_questions
				INNER JOIN qpl_qst_type ON question_type_id = question_type_fi
			  	WHERE type_tag = %s
			)", array('text'), array('assClozeTest')
		);
		
		// for all assClozeTest entries - initialize the answer field with 0 for the formaly stored gap feedback
		$DIC->database()->manipulateF(
			"UPDATE qpl_fb_specific SET answer = %s WHERE question_fi IN(
				SELECT question_id FROM qpl_questions
				INNER JOIN qpl_qst_type ON question_type_id = question_type_fi
			  	WHERE type_tag = %s
			)", array('integer', 'text'), array(0, 'assClozeTest')
		);
		
		// finaly set the question index field to notnull = true (not nullable) as it is now initialized
		$DIC->database()->modifyTableColumn('qpl_fb_specific', 'question', array(
			'notnull' => true, 'default' => 0
		));
		
		// add unique constraint on qid and the two specific feedback indentification index fields
		$DIC->database()->addUniqueConstraint('qpl_fb_specific', array(
			'question_fi', 'question', 'answer'
		));
	}
	
	if( !$DIC->database()->tableColumnExists('qpl_qst_cloze', 'feedback_mode') )
	{
		$DIC->database()->addTableColumn('qpl_qst_cloze', 'feedback_mode', array(
			'type' => 'text', 'length' => 16, 'notnull' => false, 'default' => null
		));
		
		$DIC->database()->manipulateF("UPDATE qpl_qst_cloze SET feedback_mode = %s",
			array('text'), array('gapQuestion')
		);
		
		$DIC->database()->modifyTableColumn('qpl_qst_cloze', 'feedback_mode', array(
			'notnull' => true, 'default' => 'gapQuestion'
		));
	}
	
}
catch(ilException $e)
{
	
}
