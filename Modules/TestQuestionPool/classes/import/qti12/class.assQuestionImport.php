<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for question imports
*
* assQuestionImport is a basis class question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assQuestionImport
{
	/**
	* The question object
	*
	* The question object
	*
	* @var object
	*/
	var $object;

	/**
	* assQuestionImport constructor
	*
	* @param object $a_object The question object
	* @access public
	*/
	function assQuestionImport(&$a_object)
	{
		$this->object =& $a_object;
	}
	
	function getFeedbackGeneric($item)
	{
		$feedbacksgeneric = array();
		foreach ($item->resprocessing as $resprocessing)
		{
			foreach ($resprocessing->respcondition as $respcondition)
			{
				foreach ($respcondition->displayfeedback as $feedbackpointer)
				{
					if (strlen($feedbackpointer->getLinkrefid()))
					{
						foreach ($item->itemfeedback as $ifb)
						{
							if (strcmp($ifb->getIdent(), "response_allcorrect") == 0)
							{
								// found a feedback for the identifier
								if (count($ifb->material))
								{
									foreach ($ifb->material as $material)
									{
										$feedbacksgeneric[1] = $material;
									}
								}
								if ((count($ifb->flow_mat) > 0))
								{
									foreach ($ifb->flow_mat as $fmat)
									{
										if (count($fmat->material))
										{
											foreach ($fmat->material as $material)
											{
												$feedbacksgeneric[1] = $material;
											}
										}
									}
								}
							} 
							else if (strcmp($ifb->getIdent(), "response_onenotcorrect") == 0)
							{
								// found a feedback for the identifier
								if (count($ifb->material))
								{
									foreach ($ifb->material as $material)
									{
										$feedbacksgeneric[0] = $material;
									}
								}
								if ((count($ifb->flow_mat) > 0))
								{
									foreach ($ifb->flow_mat as $fmat)
									{
										if (count($fmat->material))
										{
											foreach ($fmat->material as $material)
											{
												$feedbacksgeneric[0] = $material;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		// handle the import of media objects in XHTML code
		foreach ($feedbacksgeneric as $correctness => $material)
		{
			$m = $this->object->QTIMaterialToString($material);
			$feedbacksgeneric[$correctness] = $m;
		}
		return $feedbacksgeneric;
	}

	/**
	* Creates a question from a QTI file
	*
	* Receives parameters from a QTI parser and creates a valid ILIAS question object
	*
	* @param object $item The QTI item object
	* @param integer $questionpool_id The id of the parent questionpool
	* @param integer $tst_id The id of the parent test if the question is part of a test
	* @param object $tst_object A reference to the parent test object
	* @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
	* @param array $import_mapping An array containing references to included ILIAS objects
	* @access public
	*/
	function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
	{
	}
}

?>
