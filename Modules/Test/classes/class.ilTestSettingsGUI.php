<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class that manages the editing of general test settings/properties
 * shown on "general" subtab
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id: class.ilObjTestSettingsGeneralGUI.php 57702 2015-01-31 21:30:34Z bheyser $
 *
 * @package		Modules/Test
 */
abstract class ilTestSettingsGUI
{
	protected function formPropertyExists(ilPropertyFormGUI $form, $propertyId)
	{
		return $form->getItemByPostVar($propertyId) instanceof ilFormPropertyGUI;
	}
}