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
 * Interface ilCtrlBaseClassInterface describes ilCtrl base classes.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * Constructors of ilCtrl base classes MUST NOT contain arguments!
 * If they do though, they must be optional and cannot use DI yet.
 *
 * Up till now, base classes were determined by an entry in the
 * database tables 'service_class' and 'module_class'. This
 * interface makes that query obsolete, because the ilCtrl
 * structure can use the interface-collector to gather all known
 * base classes now.
 *
 * In the future, ilCtrl might as well prescribe some functions
 * like executeCommand() or getHTML() that are essential and
 * provide other interfaces for common GUI classes too.
 */
interface ilCtrlBaseClassInterface
{
}
