<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Component/classes/class.ilPlugin.php';

/**
 * Abstract parent class for all calendar custom modals plugin classes.
 * @author  Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
abstract class ilAppointmentCustomModalPlugin extends ilPlugin
{

	final public function getComponentType()
	{
		return IL_COMP_SERVICE;
	}

	final public function getComponentName()
	{
		return "Calendar";
	}

	final public function getSlot()
	{
		return "AppointmentCustomModal";
	}

	final public function getSlotId()
	{
		return "capm";
	}

	final public function slotInit()
	{
		//nothing to do here.
	}

	abstract function replaceContent();

	abstract function addExtraContent();

	abstract function infoscreenAddContent(ilInfoScreenGUI $a_info);

	abstract function toolbarAddItems(ilToolbarGUI $a_toolbar);

	abstract function toolbarReplaceContent();

}