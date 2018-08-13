<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
interface ilTermsOfServiceSignableDocument
{
	/**
	 * @return bool
	 */
	public function hasContent();

	/**
	 * @return string
	 */
	public function getContent();
}
