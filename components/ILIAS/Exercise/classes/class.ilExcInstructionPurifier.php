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

declare(strict_types=1);

class ilExcInstructionPurifier extends ilHtmlPurifierAbstractLibWrapper
{
    // corresponds to "mini" tagset of ilTextAreaInputGUI
    protected const TAGSET = ["strong", "em", "u", "ol", "li", "ul", "blockquote", "a", "p", "span", "br"];

    public function __construct()
    {
        parent::__construct();
    }

    protected function getPurifierConfigInstance(): HTMLPurifier_Config
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.DefinitionID', 'ilias exercise instruction');
        $config->set('HTML.DefinitionRev', 1);
        $config->set('Cache.SerializerPath', ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory());
        $config->set('HTML.Doctype', 'XHTML 1.0 Strict');

        $tags = $this->makeElementListTinyMceCompliant(self::TAGSET);
        $config->set('HTML.AllowedElements', $this->removeUnsupportedElements($tags));
        $config->set('HTML.ForbiddenAttributes', 'div@style');

        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        }

        return $config;
    }
}
