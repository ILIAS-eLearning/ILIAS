<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* ilias.php. main script.
*
* If you want to use this script your base class must be declared
* within modules.xml.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

/**
 * @var $DIC \ILIAS\DI\Container
 */
global $DIC, $ilBench;

$DIC->ctrl()->callBaseClass();
$ilBench->save();
