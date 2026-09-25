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

namespace ILIAS\Questions\AnswerForm\Capabilities\TextFeedback;

class HtmlPurifier extends \ilHtmlPurifierAbstractLibWrapper
{
    public function prepareAndPurify(
        string $html
    ): string {
        return $this->purifier->purify(
            preg_replace(
                [
                    '/<br>|<br\/>|<br \/>/',
                    '/<a class="small" id="ilPageShowAdvContent"(.+?)<\/a>/s',
                    '/<div class=\'il-copg-mob-fullscreen-modal\'>\s*<dialog(.+?)<\/dialog>\s*<\/div>/s',
                    '/\s\s+/'
                ],
                [
                    "\n",
                    '',
                    '',
                    ' '
                ],
                $html
            )
        );
    }

    protected function getPurifierType(): string
    {
        return 'question_feedback';
    }

    #[\Override]
    protected function getPurifierConfigInstance(): \HTMLPurifier_Config
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.DefinitionID', $this->getPurifierType());
        $config->set('HTML.DefinitionRev', 2);
        $config->set('Cache.SerializerPath', \ilHtmlPurifierAbstractLibWrapper::_getCacheDirectory());
        $config->set('HTML.Doctype', 'HTML 4.01 Strict');
        $config->set('HTML.AllowedElements', []);
        $config->set('HTML.ForbiddenAttributes', 'div@style');
        $config->autoFinalize = false;
        $config->set(
            'URI.AllowedSchemes',
            array_merge(
                $config->get('URI.AllowedSchemes'),
                ['data' => true]
            )
        );
        $config->autoFinalize = true;

        return $config;
    }
}
