<?php

declare(strict_types=1);

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
 * @author Guido Vollbach <gvollbach@databay.de>
 * Class ilMailTemplateGenericContext
 */
class ilMailTemplateGenericContext extends ilMailTemplateContext
{
    public function getId(): string
    {
        return 'mail_template_generic';
    }

    public function getTitle(): string
    {
        return $this->getLanguage()->txt('please_choose');
    }

    public function getDescription(): string
    {
        return $this->getLanguage()->txt('please_choose');
    }

    public function getSpecificPlaceholders(): array
    {
        return [];
    }

    public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        bool $html_markup = false
    ): string {
        return '';
    }
}
