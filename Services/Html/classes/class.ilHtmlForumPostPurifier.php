<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Html/classes/class.ilHtmlPurifierAbstractLibWrapper.php';

/** 
* Concrete class for sanitizing html of forum posts
* 
* @author	Michael Jansen <mjansen@databay.de>
* @version	$Id$
* 
*/
class ilHtmlForumPostPurifier extends ilHtmlPurifierAbstractLibWrapper
{	
	public static $_type = 'frm_post';
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/** 
	* Concrete function which builds a html purifier config instance
	* 
	* @access	protected
	* @return	HTMLPurifier_Config Instance of HTMLPurifier_Config
	* 
	*/
	protected function getPurifierConfigInstance()
	{
		include_once 'Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';
		
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Cache.SerializerPath', ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory());
		$config->set('HTML.Doctype', 'XHTML 1.0 Strict');		
		$config->set('HTML.AllowedElements', $this->removeUnsupportedElements(ilObjAdvancedEditing::_getUsedHTMLTags(self::$_type)));
	
		return $config;
	}	
}
?>