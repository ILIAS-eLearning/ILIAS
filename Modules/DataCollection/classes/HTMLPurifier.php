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

namespace ILIAS\DataCollection;

use HTMLPurifier_Config;
use ilHtmlPurifierAbstractLibWrapper;
use ilObjAdvancedEditing;

class HTMLPurifier extends ilHtmlPurifierAbstractLibWrapper
{
    protected function getPurifierConfigInstance(): HTMLPurifier_Config
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.DefinitionID', 'ilias datacollection');
        $config->set('HTML.DefinitionRev', 1);
        $config->set('HTML.Doctype', 'XHTML 1.0 Strict');

        $tags = $this->makeElementListTinyMceCompliant(ilObjAdvancedEditing::_getUsedHTMLTags("dcl"));
        $config->set('HTML.AllowedElements', $this->removeUnsupportedElements($tags));

        return $config;
    }
}
