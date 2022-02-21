<?php declare(strict_types = 1);


class ilWebDAVMountInstructionsDocumentPurifier extends ilHtmlPurifierAbstractLibWrapper
{
    private array $allowedTags = [];
    protected string $cacheDirectory = '';
    
    public function __construct(array $allowedTags = null, string $cacheDirectory = null)
    {
        if ($cacheDirectory === null) {
            $cacheDirectory = ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory();
        }
        $this->cacheDirectory = $cacheDirectory;

        if ($allowedTags === null) {
            $allowedTags = ilObjAdvancedEditing::_getUsedHTMLTags('textarea');
        }
        $this->allowedTags = $allowedTags;

        parent::__construct();
    }
    
    protected function getPurifierConfigInstance() : HTMLPurifier_Config
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.DefinitionID', 'ilias termsofservice document');
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
