<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/Context/classes/class.ilContextBase.php');

/**
 * Class ilContextWAC
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilContextWAC extends ilContextBase {

	/**
	 * @return bool
	 */
	public static function supportsRedirects() {
		return false;
	}


	/**
	 * @return bool
	 */
	public static function hasUser() {
		return true;
	}


	/**
	 * @return bool
	 */
	public static function usesHTTP() {
		return true;
	}


	/**
	 * @return bool
	 */
	public static function hasHTML() {
		return true;
	}


	/**
	 * @return bool
	 */
	public static function usesTemplate() {
		return true;
	}


	/**
	 * @return bool
	 */
	public static function initClient() {
		return true;
	}


	/**
	 * @return bool
	 */
	public static function doAuthentication() {
		return true;
	}

	/**
	 * Supports push messages
	 *
	 * @return bool
	 */
	public static function supportsPushMessages()
	{
		return false;
	}


}

?>