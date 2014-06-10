<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Html/classes/class.ilHtmlPurifierAbstractLibWrapper.php';

/**
 * Class ilMailTemplateHtmlPurifier
 */
class ilMailTemplateHtmlPurifier extends ilHtmlPurifierAbstractLibWrapper
{
	/**
	 * Has to be implemented by subclasses to build the HTMLPurifier_Config instance with
	 * object specific configurations
	 * @access    protected
	 */
	protected function getPurifierConfigInstance()
	{
		include_once 'Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';

		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.DefinitionID', 'ilias forum post');
		$config->set('HTML.DefinitionRev', 1);
		$config->set('Cache.SerializerPath', ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory());
		$config->set('HTML.Doctype', 'XHTML 1.0 Strict');

		// Bugfix #5945: Necessary because TinyMCE does not use the "u" 
		// html element but <span style="text-decoration: underline">E</span>
		$tags = ilObjAdvancedEditing::_getUsedHTMLTags();
		if(in_array('u', $tags) && !in_array('span', $tags)) $tags[] = 'span';
		$config->set('HTML.AllowedElements', $this->removeUnsupportedElements($tags));
		$config->set('HTML.ForbiddenAttributes', 'div@style');

		if ($def = $config->maybeGetRawHTMLDefinition()) {
			$def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
		}

		return $config;
	}
} 