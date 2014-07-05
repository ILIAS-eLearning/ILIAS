<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
 * Service context (factory) class
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesContext
 */
class ilContext
{		
	protected static $class_name; // [string]
	protected static $type; // [int]	
	
	const CONTEXT_WEB = 1;
	const CONTEXT_CRON = 2;
	const CONTEXT_RSS = 3;
	const CONTEXT_ICAL = 4;
	const CONTEXT_SOAP = 5;
	const CONTEXT_WEBDAV = 6;
	const CONTEXT_RSS_AUTH = 7;
	const CONTEXT_WEB_ACCESS_CHECK = 8;
	const CONTEXT_SESSION_REMINDER = 9;
	const CONTEXT_SOAP_WITHOUT_CLIENT = 10;
	const CONTEXT_UNITTEST = 11;
	const CONTEXT_REST = 12;
	const CONTEXT_SCORM = 13;
	
	/**
	 * Init context by type
	 * 
	 * @param int $a_type 
	 * @return bool
	 */
	public static function init($a_type)
	{
		$class_name = self::getClassForType($a_type);
		if($class_name)
		{
			include_once "Services/Context/classes/class.".$class_name.".php";
			self::$class_name = $class_name;
			self::$type = $a_type;
			return true;
		}
		return false;
	}
	
	/**
	 * Get class name for type id
	 * 	 
	 * @param int $a_type
	 * @return string 
	 */
	protected function getClassForType($a_type)
	{
		switch($a_type)
		{
			case self::CONTEXT_WEB:
				return "ilContextWeb";
				
			case self::CONTEXT_CRON:
				return "ilContextCron";
				
			case self::CONTEXT_RSS:
				return "ilContextRss";
				
			case self::CONTEXT_ICAL:
				return "ilContextIcal";
				
			case self::CONTEXT_SOAP:
				return "ilContextSoap";
			
			case self::CONTEXT_WEBDAV:
				return "ilContextWebdav";	
				
			case self::CONTEXT_RSS_AUTH:
				return "ilContextRssAuth";	
				
			case self::CONTEXT_WEB_ACCESS_CHECK:
				return "ilContextWebAccessCheck";	
			
			case self::CONTEXT_SESSION_REMINDER:
				return "ilContextSessionReminder";	
				
			case self::CONTEXT_SOAP_WITHOUT_CLIENT:
				return "ilContextSoapWithoutClient";
				
			case self::CONTEXT_UNITTEST:
				return "ilContextUnitTest";
				
			case self::CONTEXT_REST:
				return 'ilContextRest';

			case self::CONTEXT_SCORM:
				return 'ilContextScorm';
		}
	}
	
	/**
	 * Call current content
	 * 
	 * @param string $a_method
	 * @return bool
	 */
	protected static function callContext($a_method)
	{
		if(!self::$class_name)
		{
			self::init(self::CONTEXT_WEB);
		}		
		return call_user_func(array(self::$class_name, $a_method));
	}
	
	/**
	 * Are redirects supported?
	 * 
	 * @return bool 
	 */
	public static function supportsRedirects()
	{
		global $ilCtrl;
		
		// asynchronous calls must never be redirected
		if($ilCtrl && $ilCtrl->isAsynch())
		{
			return false;
		}
		
		return (bool)self::callContext("supportsRedirects");
	}
	
	/**
	 * Based on user authentication?
	 *  
	 * @return bool
	 */
	public static function hasUser()
	{
		return (bool)self::callContext("hasUser");		
	}
	
	/**
	 * Uses HTTP aka browser 
	 * 
	 * @return bool 
	 */
	public static function usesHTTP()
	{
		return (bool)self::callContext("usesHTTP");	
	}
	
	/**
	 * Has HTML output
	 *  
	 * @return bool
	 */
	public static function hasHTML()
	{
		return (bool)self::callContext("hasHTML");	
	}
	
	/**
	 * Uses template engine
	 *  
	 * @return bool
	 */
	public static function usesTemplate()
	{
		return (bool)self::callContext("usesTemplate");	
	}
	
	/**
	 * Init client
	 *  
	 * @return bool
	 */
	public static function initClient()
	{
		return (bool)self::callContext("initClient");	
	}
	
	/**
	 * Try authentication
	 *  
	 * @return bool
	 */
	public static function doAuthentication()
	{
		return (bool)self::callContext("doAuthentication");	
	}
	
	/**
	 * Get context type
	 * 
	 * @return int
	 */
	public static function getType()
	{
		return self::$type;
	}
}

?>