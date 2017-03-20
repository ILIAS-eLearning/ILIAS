<?php
// saml-patch: begin
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAuthContainerSAML
 * This is a helper class to transport the simplesamlphp attributes to the auth container.
 * This is indeed also global scope, but better than a global array.
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlAttributesHolder
{
	/**
	 * @var array
	 */
	protected static $attributes = array();

	/**
	 * @var string
	 */
	protected static $return_to = '';

	/**
	 * @param array $attributes
	 */
	public static function setAttributes(array $attributes)
	{
		self::$attributes = $attributes;
	}

	/**
	 * @return array
	 */
	public static function getAttributes()
	{
		return self::$attributes;
	}

	/**
	 * @return string
	 */
	public static function getReturnTo()
	{
		return self::$return_to;
	}

	/**
	 * @param string $return_to
	 */
	public static function setReturnTo($return_to)
	{
		self::$return_to = $return_to;
	}
}
// saml-patch: end