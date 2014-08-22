<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/LearningModule/classes/class.ilLMTOCExplorerGUI.php");

/**
 * LM presentation (separate toc screen) explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ModulesLearningModule
 */
class ilLMTableOfContentsExplorerGUI extends ilLMTOCExplorerGUI
{
	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent cmd
	 * @param ilLMPresentationGUI $a_lm_pres learning module presentation gui object
	 * @param string $a_lang language
	 */
	function __construct($a_parent_obj, $a_parent_cmd, ilLMPresentationGUI $a_lm_pres, $a_lang = "-")
	{
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_lm_pres, $a_lang);
		include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
		$chaps = ilLMObject::_getAllLMObjectsOfLM($this->lm->getId(), $a_type = "st");
		foreach ($chaps as $c)
		{
			$this->setNodeOpen($c);
		}
	}
}

?>
