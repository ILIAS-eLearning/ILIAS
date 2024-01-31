<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * login script for ilias
 *
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 * @author Peter Gabriel <pgabriel@databay.de>
 * @version $Id$
 *
 * @package ilias-layout
 */

require_once '../vendor/composer/vendor/autoload.php';

// jump to setup if ILIAS3 is not installed
if (!file_exists(getcwd() . "/../ilias.ini.php")) {
    header("Location: ./cli/setup.php");
    exit();
}

ilInitialisation::initILIAS();

// @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
// $ilCtrl->setCmd('showLoginPageOrStartupPage');
$ilCtrl->callBaseClass('ilStartUpGUI');
$ilBench->save();

exit;