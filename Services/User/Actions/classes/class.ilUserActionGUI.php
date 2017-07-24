<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * A class that provides a collection of actions on users
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserActionGUI
{
	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * Constructor
	 */
	protected function __construct()
	{
		global $DIC;

		$this->tpl = $DIC["tpl"];
	}

	/**
	 * Get instance
	 *
	 * @return ilUserActionGUI
	 */
	static function getInstance()
	{
		return new ilUserActionGUI();
	}

	/**
	 * Add requried js for an action context
	 *
	 * @param string $a_context_component_id
	 * @param string $a_context_id
	 */
	function addRequiredJsForContext($a_context_component_id, $a_context_id)
	{
		$tpl = $this->tpl;

		include_once("./Services/User/Actions/classes/class.ilUserActionAdmin.php");
		include_once("./Services/User/Actions/classes/class.ilUserActionProviderFactory.php");
		foreach (ilUserActionProviderFactory::getAllProviders() as $prov)
		{
			foreach ($prov->getActionTypes() as $act_type => $txt)
			{
				if (ilUserActionAdmin::lookupActive($a_context_component_id, $a_context_id, $prov->getComponentId(), $act_type))
				{
					foreach ($prov->getJsScripts($act_type) as $script)
					{
						$tpl->addJavascript($script);
					}
				}
			}
		}
	}

}