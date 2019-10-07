<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion;

use HTMLPurifier_Config;
use ilHtmlPurifierAbstractLibWrapper;
use ilObjAdvancedEditing;

require_once 'Services/Html/classes/class.ilHtmlPurifierAbstractLibWrapper.php';

/**
 * Class ilAsqHtmlPurifier
 * 
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAsqHtmlPurifier extends ilHtmlPurifierAbstractLibWrapper
{
    /**
     * @var ilAsqHtmlPurifier
     */
    private static $instance;
    
    /**
     * @return ilAsqHtmlPurifier
     */
    public static function getInstance() : ilAsqHtmlPurifier {
        if (self::$instance === null) {
            self::$instance = new ilAsqHtmlPurifier();
        }
        
        return self::$instance;
    }
    
    public function __construct() {
        parent::__construct();
    }
    
	protected function getPurifierType()
	{
		return 'assessment';
	}

	/**
	 * @return	HTMLPurifier_Config Instance of HTMLPurifier_Config
	 */
	protected function getPurifierConfigInstance()
	{
		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.DefinitionID', $this->getPurifierType());
		$config->set('HTML.DefinitionRev', 1);
		$config->set('Cache.SerializerPath', ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory());
		$config->set('HTML.Doctype', 'XHTML 1.0 Strict');
		$config->set('HTML.AllowedElements', $this->getAllowedElements());
		$config->set('HTML.ForbiddenAttributes', 'div@style');
		if ($def = $config->maybeGetRawHTMLDefinition()) {
			$def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
		}

		return $config;
	}
	
	private function getAllowedElements()
	{
		$allowedElements = $this->getElementsUsedForAdvancedEditing();
		
		$allowedElements = $this->makeElementListTinyMceCompliant($allowedElements);
		$allowedElements = $this->removeUnsupportedElements($allowedElements);
		
		return $allowedElements;
	}
	
	private function getElementsUsedForAdvancedEditing()
	{
		include_once 'Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';
		return ilObjAdvancedEditing::_getUsedHTMLTags($this->getPurifierType());
	}
} 