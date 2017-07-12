<?php
// saml-patch: begin
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlCreateUpdateAttributeMappingFilter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlCreateUpdateAttributeMappingFilter extends FilterIterator
{
	/**
	 * {@inheritdoc}
	 */
	public function accept()
	{
		return true;
	}
}
// saml-patch: end