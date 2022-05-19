<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilAccessibilityDocumentHtmlPurifier
 */
class ilAccessibilityDocumentHtmlPurifier extends ilHtmlPurifierAbstractLibWrapper
{
    private array $allowedTags = [];
    protected string $cacheDirectory = '';

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
