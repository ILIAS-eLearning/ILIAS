<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * registration form for new users
 *
 * @author Sascha Hofmann <shofmann@databay.de>
 * @version $Id$
 *
 * @package ilias-core
 */

ilInitialisation::initILIAS();

// @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
// $ilCtrl->setCmd("jumpToRegistration");
$ilCtrl->callBaseClass('ilStartUpGUI');
$ilBench->save();