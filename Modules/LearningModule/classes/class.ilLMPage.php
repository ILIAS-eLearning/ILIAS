<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
 * Extension of ilPageObject for learning modules 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLMPage extends ilPageObject
{
	/**
	 * Constructor
	 */
	function __construct($a_id = 0, $a_old_nr = 0, $a_halt = true)
	{
		parent::__construct("lm", $a_id, $a_old_nr, $a_halt);
	}
	
	/**
	 * Before page content update
	 *
	 * Note: This one is "work in progress", currently only text paragraphs call this hook
	 * It is called before the page content object invokes the update procedure of
	 * ilPageObject
	 *
	 * @param
	 * @return
	 */
	function beforePageContentUpdate($a_page_content)
	{
		if ($a_page_content->getType() == "par")
		{
			include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
			$glos = ilObjContentObject::lookupAutoGlossaries($this->getParentId());
			$a_page_content->autoLinkGlossaries($glos);
		}
	}

}

?>
