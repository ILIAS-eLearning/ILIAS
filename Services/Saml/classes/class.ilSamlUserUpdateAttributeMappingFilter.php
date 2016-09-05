<?php
// saml-patch: begin
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlUserUpdateAttributeMappingFilter
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlUserUpdateAttributeMappingFilter extends FilterIterator
{
	/**
	 * {@inheritdoc}
	 */
	public function accept()
	{
		/** @var $current ilSamlAttributeMappingRule */
		$current = parent::current();

		return $current->isAutomaticallyUpdated();
	}
}
// saml-patch: end