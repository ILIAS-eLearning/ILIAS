<?php
// saml-patch: begin
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlAttributeMappingRule
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlAttributeMappingRule
{
	/**
	 * @var string
	 */
	protected $attribute = '';

	/**
	 * @var string
	 */
	protected $idp_attribute = '';

	/**
	 * @var bool
	 */
	protected $update_automatically = false;

	/**
	 * @return string
	 */
	public function getIdpAttribute()
	{
		return $this->idp_attribute;
	}

	/**
	 * @param string $idp_attribute
	 */
	public function setIdpAttribute($idp_attribute)
	{
		$this->idp_attribute = $idp_attribute;
	}

	/**
	 * @return string
	 */
	public function getAttribute()
	{
		return $this->attribute;
	}

	/**
	 * @param string $attribute
	 */
	public function setAttribute($attribute)
	{
		$this->attribute = $attribute;
	}

	/**
	 * @return boolean
	 */
	public function isAutomaticallyUpdated()
	{
		return $this->update_automatically;
	}

	/**
	 * @param boolean $update_automatically
	 */
	public function updateAutomatically($update_automatically)
	{
		$this->update_automatically = $update_automatically;
	}
}
// saml-patch: end