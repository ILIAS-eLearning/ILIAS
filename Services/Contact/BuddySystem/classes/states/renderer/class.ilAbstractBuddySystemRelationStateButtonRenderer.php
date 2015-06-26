<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/interfaces/interface.ilBuddySystemRelationStateButtonRenderer.php';

/**
 * Class ilAbstractBuddySystemRelationStateButtonRenderer
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilAbstractBuddySystemRelationStateButtonRenderer implements ilBuddySystemRelationStateButtonRenderer
{
	/**
	 * @var ilBuddySystemRelation
	 */
	protected $relation;

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @param int                   $user_id
	 * @param ilBuddySystemRelation $relation
	 */
	public function __construct($user_id, ilBuddySystemRelation $relation)
	{
		$this->user_id   = $user_id;
		$this->relation  = $relation;

		$this->tpl = new ilTemplate(
			'tpl.buddy_system_state_' . self::convertUpperCamelCaseToUnderscoreCase($this->relation->getState()->getName()) . '.html',
			true,
			true,
			'Services/Contact/BuddySystem'
		);
	}

	/**
	 * Convert a value given in camel case conversion to underscore case conversion (e.g. MyClass to my_class)
	 * @param string $value Value in lower camel case conversion
	 * @return string The value in underscore case conversion
	 */
	protected static function convertUpperCamelCaseToUnderscoreCase($value) {
		return preg_replace('/(^|[a-z])([A-Z])/e', 'strtolower(strlen("\\1") ? "\\1_\\2" : "\\2")', $value);
	}

	/**
	 * @return string
	 */
	protected function getLanguageVariableSuffix()
	{
		if($this->user_id == $this->relation->getUserId())
		{
			$suffix = '_a';
		}
		else
		{
			$suffix = '_p';
		}
		return $suffix;
	}

	/**
	 * 
	 */
	protected function render()
	{
		$this->renderStateButton($this->relation->getState());
		foreach($this->relation->getState()->getPossibleTargetStates() as $target_state)
		{
			$this->renderTargetState($target_state);
		}
	}

	/**
	 * @return string
	 */
	protected function getTemplateVariablePrefix()
	{
		return '';
	}

	/**
	 *
	 */
	protected function renderStateButton()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$state_id = self::convertUpperCamelCaseToUnderscoreCase($this->relation->getState()->getName());

		$this->tpl->setVariable(
			$this->getTemplateVariablePrefix() . 'BUTTON_TXT',
			$lng->txt(
				'buddy_bs_btn_txt_' . $state_id . $this->getLanguageVariableSuffix()
			)
		);
	}

	/**
	 * @param ilBuddySystemRelationState $target_state
	 */
	protected function renderTargetState(ilBuddySystemRelationState $target_state)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$state_id        = self::convertUpperCamelCaseToUnderscoreCase($this->relation->getState()->getName());
		$target_state_id = self::convertUpperCamelCaseToUnderscoreCase($target_state->getName());

		$this->tpl->setVariable(
			$this->getTemplateVariablePrefix() . 'TARGET_STATE_' . strtoupper($target_state_id),
			get_class($target_state)
		);
		$this->tpl->setVariable(
			$this->getTemplateVariablePrefix() . 'TARGET_STATE_ACTION_' . strtoupper($target_state_id),
			$target_state->getAction()
		);
		$this->tpl->setVariable(
			$this->getTemplateVariablePrefix() . 'TARGET_STATE_TXT_' . strtoupper($target_state_id),
			$lng->txt(
				'buddy_bs_act_btn_txt_' . $state_id . '_to_' . $target_state_id
			)
		);
	}

	/**
	 * @return string
	 */
	public function getHtml()
	{
		$this->render();
		return $this->tpl->get();
	}
}