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

namespace ILIAS\LegalDocuments;

use ilObjAdvancedEditing;
use ilHtmlPurifierAbstractLibWrapper;
use HTMLPurifier_Config;
use Closure;

class HTMLPurifier extends ilHtmlPurifierAbstractLibWrapper
{
    /** @var list<string> */
    private readonly array $allowed_tags;
    private readonly string $cache_directory;

    /** @var Closure(): HTMLPurifier_Config */
    private readonly Closure $create_config;

    /**
     * @param null|Closure(): HTMLPurifier_Config $create_config
     */
    public function __construct(
        ?array $allowed_tags = null,
        ?string $cache_directory = null,
        ?Closure $create_config = null
    ) {
        $this->cache_directory = $cache_directory ?? ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory();
        $this->allowed_tags = $allowed_tags ?? ilObjAdvancedEditing::_getUsedHTMLTags('textarea');
        $this->create_config = $create_config ?? HTMLPurifier_Config::createDefault(...);
        parent::__construct();
    }

    protected function getPurifierConfigInstance(): HTMLPurifier_Config
    {
        $config = ($this->create_config)();
        $config->set('HTML.DefinitionID', 'ilias termsofservice document');
        $config->set('HTML.DefinitionRev', 1);
        $config->set('HTML.TargetBlank', true);
        $config->set('Cache.SerializerPath', $this->cache_directory);
        $config->set('HTML.Doctype', 'XHTML 1.0 Strict');

        $tags = $this->makeElementListTinyMceCompliant($this->allowed_tags);

        $tags[] = 'b';
        $tags[] = 'i';

        $config->set('HTML.AllowedElements', $this->removeUnsupportedElements($tags));
        $config->set('HTML.ForbiddenAttributes', 'div@style');

        if (($def = $config->maybeGetRawHTMLDefinition()) !== null) {
            $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        }

        return $config;
    }
}
