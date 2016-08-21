<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
 * Service context (factory) class
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version $Id$
 * 
 * @ingroup ServicesContext
 */
class ilContext
{		
	protected static $class_name; // [string]
	protected static $type; // [string]	
	
	const CONTEXT_WEB = "ilContextWeb";
	const CONTEXT_CRON = "ilContextCron";
	const CONTEXT_RSS = "ilContextRss";
	const CONTEXT_ICAL = "ilContextIcal";
	const CONTEXT_SOAP = "ilContextSoap";
	const CONTEXT_WEBDAV = "ilContextWebdav";
	const CONTEXT_RSS_AUTH = "ilContextRssAuth";
	const CONTEXT_WEB_ACCESS_CHECK = "ilContextWebAccessCheck";
	const CONTEXT_SESSION_REMINDER = "ilContextSessionReminder";
	const CONTEXT_SOAP_WITHOUT_CLIENT = "ilContextSoapWithoutClient";
	const CONTEXT_UNITTEST = "ilContextUnitTest";
	const CONTEXT_REST = "ilContextRest";
	const CONTEXT_SCORM = "ilContextScorm";
	const CONTEXT_WAC = "ilContextWAC";
	const CONTEXT_APACHE_SSO = 'ilContextApacheSSO';
	
	/**
	 * Init context by type
	 * 
	 * @param string $a_type 
	 * @return bool
	 */
	public static function init($a_type)
	{
		include_once "Services/Context/classes/class.".$a_type.".php";
		self::$class_name = $a_type;
		self::$type = $a_type;
		
		return true;
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
	 * @return string
	 */
	public static function getType()
	{
		return self::$type;
	}
	
	/**
	 * Check if context supports persistent
	 * session handling.
	 * false for cli context
	 * 
	 * @return bool
	 */
	public static function supportsPersistentSessions()
	{
		return (bool) self::callContext('supportsPersistentSessions');
	}
}

?>