<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Concrete class for sanitizing html of forum posts
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlForumPostPurifier extends ilHtmlPurifierAbstractLibWrapper
{
    /**
     * ilHtmlForumPostPurifier constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Concrete function which builds a html purifier config instance
     * @return    HTMLPurifier_Config Instance of HTMLPurifier_Config
     */
    protected function getPurifierConfigInstance() : HTMLPurifier_Config
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.DefinitionID', 'ilias forum post');
        $config->set('HTML.DefinitionRev', 1);
        $config->set('Cache.SerializerPath', ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory());
        $config->set('HTML.Doctype', 'XHTML 1.0 Strict');

        $tags = ilObjAdvancedEditing::_getUsedHTMLTags('frm_post');
        $tags = $this->makeElementListTinyMceCompliant($tags);
        $config->set('HTML.AllowedElements', $this->removeUnsupportedElements($tags));
        $config->set('HTML.ForbiddenAttributes', 'div@style');

        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        }

        return $config;
    }
}