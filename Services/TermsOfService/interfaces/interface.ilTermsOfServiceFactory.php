<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
interface ilTermsOfServiceFactory
{
	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getByName($name);
}
