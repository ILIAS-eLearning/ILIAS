<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Import configuration for learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLearningModuleImportConfig extends ilImportConfig
{
	protected $transl_into = false;
	protected $transl_into_lm = null;
	protected $transl_lang = "";

	/**
	 * Set translation import mode
	 *
	 * @param ilObjLearningModule $a_lm learning module
	 * @param string $a_lang language
	 */
	function setTranslationImportMode($a_lm, $a_lang = "")
	{
		if ($a_lm != null)
		{
			$this->transl_into = true;
			$this->transl_into_lm = $a_lm;
			$this->transl_lang = $a_lang;
		}
		else
		{
			$this->transl_into = false;
		}
	}

	/**
	 * Get translation import mode
	 *
	 * @return bool check if translation import is activated
	 */
	function getTranslationImportMode()
	{
		return $this->transl_into;
	}

	/**
	 * Get translation lm
	 *
	 * @return ilObjLearningModule learning module
	 */
	function getTranslationLM()
	{
		return $this->transl_into_lm;
	}

	/**
	 * Get translation language
	 *
	 * @return string language
	 */
	function getTranslationLang()
	{
		return $this->transl_lang;
	}

}

?>