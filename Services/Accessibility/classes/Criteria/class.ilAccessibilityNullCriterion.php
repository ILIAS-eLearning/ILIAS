<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilAccessibilityNullCriterion
 */
class ilAccessibilityNullCriterion implements ilAccessibilityCriterionType
{
	/**
	 * @inheritdoc
	 */
	public function getTypeIdent() : string
	{
		return 'null';
	}

	/**
	 * @inheritdoc
	 */
	public function evaluate(ilObjUser $user, ilAccessibilityCriterionConfig $config) : bool
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function hasUniqueNature() : bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function ui(ilLanguage $lng) : ilAccessibilityCriterionTypeGUI
	{
		return new class($lng) implements ilAccessibilityCriterionTypeGUI
		{
			/** @var ilLanguage */
			protected $lng;

			/**
			 *  constructor.
			 * @param ilLanguage $lng
			 */
			public function __construct(ilLanguage $lng)
			{
				$this->lng = $lng;
			}

			/**
			 * @inheritdoc
			 */
			public function appendOption(ilRadioGroupInputGUI $option, ilAccessibilityCriterionConfig $config) : void
			{
			}

			/**
			 * @inheritdoc
			 */
			public function getConfigByForm(ilPropertyFormGUI $form) : ilAccessibilityCriterionConfig
			{
				return new ilAccessibilityCriterionConfig();
			}

			/**
			 * @inheritdoc
			 */
			public function getIdentPresentation() : string
			{
				return $this->lng->txt('deleted');
			}

			/**
			 * @inheritdoc
			 */
			public function getValuePresentation(
				ilAccessibilityCriterionConfig $config,
				Factory $uiFactory
			) : Component {
				return $uiFactory->legacy('-');
			}
		};
	}
}