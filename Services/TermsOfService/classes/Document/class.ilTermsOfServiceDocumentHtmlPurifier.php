<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocumentHtmlPurifier
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentHtmlPurifier extends \ilHtmlPurifierAbstractLibWrapper
{
	/** @var string */
	public static $_type = 'textarea';

	/**
	 * @inheritdoc
	 */
	protected function getPurifierConfigInstance()
	{
		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.DefinitionID', 'ilias termsofservice document');
		$config->set('HTML.DefinitionRev', 1);
		$config->set('HTML.TargetBlank', true);
		$config->set('Cache.SerializerPath', \ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory());
		$config->set('HTML.Doctype', 'XHTML 1.0 Strict');

		$tags = \ilObjAdvancedEditing::_getUsedHTMLTags(self::$_type);
		$tags = $this->makeElementListTinyMceCompliant($tags);
		$config->set('HTML.AllowedElements', $this->removeUnsupportedElements($tags));
		$config->set('HTML.ForbiddenAttributes', 'div@style');

		if ($def = $config->maybeGetRawHTMLDefinition()) {
			$def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
		}

		return $config;
	}
}