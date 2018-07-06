<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPagePageGUI
 * @ilCtrl_Calls ilContentPagePageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilContentPagePageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilContentPagePageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 */
class ilContentPagePageGUI extends \ilPageObjectGUI implements \ilContentPageObjectConstants
{
	/**
	 * ilContentPagePageGUI constructor.
	 * @param int $a_id
	 * @param int $a_old_nr
	 */
	public function __construct($a_id = 0, $a_old_nr = 0)
	{
		parent::__construct(self::OBJ_TYPE, $a_id, $a_old_nr);
		$this->setTemplateTargetVar('ADM_CONTENT');
		$this->setTemplateOutput(true);
	}
}