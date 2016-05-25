<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Context/classes/class.ilContextBase.php";

/** 
 * Context for showing UI stuff via src/UI/show_example.php 
 * 
 * @author 	Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 * 
 * @ingroup ServicesContext
 */
class ilContextUIShowcase extends ilContextBase
{			
	/**
	 * Are redirects supported?
	 * 
	 * @return bool 
	 */
	public static function supportsRedirects()
	{
		return false;
	}
	
	/**
	 * Based on user authentication?
	 *  
	 * @return bool
	 */
	public static function hasUser()
	{
		return false;
	}
	
	/**
	 * Uses HTTP aka browser 
	 * 
	 * @return bool 
	 */
	public static function usesHTTP()
	{
		return true;
	}
	
	/**
	 * Has HTML output
	 *  
	 * @return bool
	 */
	public static function hasHTML()
	{
		return true;
	}
	
	/**
	 * Uses template engine
	 *  
	 * @return bool
	 */
	public static function usesTemplate()
	{
		return true;
	}
	
	/**
	 * Init client
	 *  
	 * @return bool
	 */
	public static function initClient()
	{
		return true;
	}
	
	/**
	 * Try authentication
	 *  
	 * @return bool
	 */
	public static function doAuthentication()
	{
		return false;
	}
}

?>
