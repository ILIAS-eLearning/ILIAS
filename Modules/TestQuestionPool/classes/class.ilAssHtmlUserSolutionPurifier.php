<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Html/classes/class.ilHtmlPurifierAbstractLibWrapper.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssHtmlUserSolutionPurifier extends ilHtmlPurifierAbstractLibWrapper
{
	const TYPE = 'qpl_usersolution';

	/**
	 * @return	HTMLPurifier_Config Instance of HTMLPurifier_Config
	 */
	protected function getPurifierConfigInstance()
	{
		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.DefinitionID', 'ilias forum post');
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
		return ilObjAdvancedEditing::_getUsedHTMLTags(self::TYPE);
	}
} 