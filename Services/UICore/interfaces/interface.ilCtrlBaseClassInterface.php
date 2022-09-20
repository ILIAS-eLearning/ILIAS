<?php

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

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
