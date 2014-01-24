<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Copy a didactic template and all subitems
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateCopier
{
	private $tpl_id = 0;
	private $new_tpl_id = 0;

	/**
	 * Constructor
	 * @param int $a_tpl_id
	 */
	public function __construct($a_tpl_id)
	{
		$this->tpl_id = $a_tpl_id;
	}

	/**
	 * Get template id
	 * @return int
	 */
	public function getTemplateId()
	{
		return $this->tpl_id;
	}

	/**
	 * Get new template id
	 * @return int
	 */
	public function getNewTemplateId()
	{
		return $this->new_tpl_id;
	}

	/**
	 * Start copy
	 *
	 * @return int new template id
	 */
	public function start()
	{
		$orig = new ilDidacticTemplateSetting($this->getTemplateId());
		$copy = clone $orig;
		$copy->save();
		$this->new_tpl_id = $copy->getId();

		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateActionFactory.php';
		foreach(ilDidacticTemplateActionFactory::getActionsByTemplateId($this->getTemplateId()) as $action)
		{
			$action->setTemplateId($this->getNewTemplateId());
			$new = clone $action;
			$new->save();
		}


	}
}
?>