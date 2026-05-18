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

namespace ILIAS\Questions\AnswerForm\Migration;

class MigrationPurifier extends \ilHtmlPurifierAbstractLibWrapper
{
    private const DEFAULT_TAGS = [
        'a',
        'blockquote',
        'br',
        'cite',
        'code',
        'dd',
        'div',
        'dl',
        'dt',
        'em',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hr',
        'img',
        'li',
        'ol',
        'p',
        'pre',
        'span',
        'strike',
        'strong',
        'sub',
        'sup',
        'u',
        'ul'
    ];

    public function __construct(
        private readonly \ilDBInterface $db
    ) {
        parent::__construct();
    }

    /**
     * @return	HTMLPurifier_Config Instance of HTMLPurifier_Config
     */
    protected function getPurifierConfigInstance(): \HTMLPurifier_Config
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.DefinitionID', 'migration');
        $config->set('HTML.DefinitionRev', 2);
        $config->set('Cache.SerializerPath', sys_get_temp_dir());
        $config->set('HTML.Doctype', 'XHTML 1.0 Strict');
        $config->set('HTML.AllowedElements', $this->getAllowedElements());
        $config->set('HTML.ForbiddenAttributes', 'div@style');
        $config->autoFinalize = false;
        $config->set(
            'URI.AllowedSchemes',
            [
                ...$config->get('URI.AllowedSchemes'),
                'data' => true
            ]
        );
        $config->autoFinalize = true;
        if (($def = $config->maybeGetRawHTMLDefinition()) !== null) {
            $def->addAttribute('img', 'data-id', 'Number');
            $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        }

        return $config;
    }

    private function getAllowedElements(): array
    {
        return $this->removeUnsupportedElements(
            $this->makeElementListTinyMceCompliant(
                $this->getElementsUsedForAdvancedEditing()
            )
        );
    }

    private function getElementsUsedForAdvancedEditing(): array
    {
        $tags = $this->db->fetchObject(
            $this->db->query(
                'SELECT value FROM settings' . PHP_EOL
                . 'WHERE module = "advanced_editing"' . PHP_EOL
                . 'AND keyword = "advanced_editing_used_html_tags_assessment"'
            )
        )?->value ?? '';

        if ($tags === '') {
            return self::DEFAULT_TAGS;
        }

        return unserialize($tags, ['allowed_classes' => false]);
    }
}
