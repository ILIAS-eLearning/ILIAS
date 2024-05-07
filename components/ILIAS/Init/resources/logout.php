<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * logout script for ilias
 *
 * @author Sascha Hofmann <shofmann@databay.de>
 * @version $Id$
 *
 * @package ilias-core
 */

require_once("../vendor/composer/vendor/autoload.php");
ilInitialisation::initILIAS();

// @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
// $ilCtrl->setCmd('doLogout');
$ilCtrl->callBaseClass('ilStartUpGUI');
$ilBench->save();

exit;