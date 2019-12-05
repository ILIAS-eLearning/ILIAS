<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilityDocumentHtmlPurifier
 */
class ilAccessibilityDocumentHtmlPurifier extends ilHtmlPurifierAbstractLibWrapper
{
	/** @var array */
	private $allowedTags = [];

	/** @var string */
	protected $cacheDirectory = '';

	/**
	 * ilAccessibilityDocumentHtmlPurifier constructor.
	 * @param array|null $allowedTags
	 * @param string     $cacheDirectory
	 */
	public function __construct(array $allowedTags = null, string $cacheDirectory = null)
	{
		if (null === $cacheDirectory) {
			$cacheDirectory = ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory();
		}
		$this->cacheDirectory = $cacheDirectory;

		if (null === $allowedTags) {
			$allowedTags = ilObjAdvancedEditing::_getUsedHTMLTags('textarea');
		}
		$this->allowedTags = $allowedTags;

		parent::__construct();
		$this->allowedTags = $allowedTags;
	}

	/**
	 * @inheritdoc
	 */
	protected function getPurifierConfigInstance() : HTMLPurifier_Config
	{
		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.DefinitionID', 'ilias accessibility document');
		$config->set('HTML.DefinitionRev', 1);
		$config->set('HTML.TargetBlank', true);
		$config->set('Cache.SerializerPath', $this->cacheDirectory);
		$config->set('HTML.Doctype', 'XHTML 1.0 Strict');

		$tags = $this->allowedTags;
		$tags = $this->makeElementListTinyMceCompliant($tags);

		$tags[] = 'b';
		$tags[] = 'i';

		$config->set('HTML.AllowedElements', $this->removeUnsupportedElements($tags));
		$config->set('HTML.ForbiddenAttributes', 'div@style');

		if ($def = $config->maybeGetRawHTMLDefinition()) {
			$def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
		}

		return $config;
	}
}