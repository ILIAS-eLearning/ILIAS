<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilExternalAuthUserCreationAttributeMappingFilter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilExternalAuthUserCreationAttributeMappingFilter extends FilterIterator
{
	/**
	 * {@inheritdoc}
	 */
	public function accept()
	{
		return true;
	}
}