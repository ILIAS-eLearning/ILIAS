<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./setup/classes/class.ilDatabaseSelectionWS.php");
include_once("./setup/classes/class.ilBasicDataWS.php");
include_once("./setup/classes/class.ilInstallDBWS.php");
include_once("./setup/classes/class.ilContactWS.php");
include_once("./setup/classes/class.ilRegisterWS.php");
include_once("./setup/classes/class.ilFinishSetupWS.php");
include_once("./Services/Workflow/classes/class.ilWorkflow.php");

/**
* Workflow for the creation of a new client.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesSetup
*/
class ilClientCreationWorkflow extends Workflow
{	
	/**
	* Constructor
	*/
	function __construct($a_setup)
	{
		$ws = new ilDatabaseSelectionWS($a_setup);
		$this->addStep($ws);
		$ws = new ilBasicDataWS($a_setup);
		$this->addStep($ws);
		$ws = new ilInstallDBWS($a_setup);
		$this->addStep($ws);
		$ws = new ilInstallLanguagesWS($a_setup);
		$this->addStep($ws);
		$ws = new ilContactWS($a_setup);
		$this->addStep($ws);
		$ws = new ilRegisterWS($a_setup);
		$this->addStep($ws);
		$ws = new ilFinishSetupWS($a_setup);
		$this->addStep($ws);
	}
}

?>