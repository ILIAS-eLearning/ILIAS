<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Services/Form
 */
interface ilFormSubmitManipulator
{
	/**
	 * @param array $values
	 * @return array $values
	 */
	public function manipulateFormSubmitValues($values);
}