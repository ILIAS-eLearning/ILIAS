<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('Services/Context/interfaces/interface.ilContextTemplate.php');

/**
 * Service context base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesContext
 *
 * @deprecated instead of extending from ilContextBase, implement the ihe interface ilContextTemplate
 */
abstract class ilContextBase implements ilContextTemplate
{
}