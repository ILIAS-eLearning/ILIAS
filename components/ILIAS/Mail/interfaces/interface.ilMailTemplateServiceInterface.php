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

use ILIAS\Mail\Templates\TemplateSubjectSyntaxException;
use ILIAS\Mail\Templates\TemplateMessageSyntaxException;

interface ilMailTemplateServiceInterface
{
    public function createNewTemplate(
        string $contextId,
        string $title,
        string $subject,
        string $message,
        string $language
    ): ilMailTemplate;

    /**
     * @throws TemplateSubjectSyntaxException|TemplateMessageSyntaxException
     */
    public function modifyExistingTemplate(
        int $templateId,
        string $contextId,
        string $title,
        string $subject,
        string $message,
        string $language
    ): void;

    public function loadTemplateForId(int $templateId): ilMailTemplate;

    /**
     * @return list<ilMailTemplate>
     */
    public function loadTemplatesForContextId(string $contextId): array;

    /**
     * @param list<int> $templateIds
     */
    public function deleteTemplatesByIds(array $templateIds): void;

    /**
     * @return list<array<string, mixed>>
     */
    public function listAllTemplatesAsArray(): array;

    public function unsetAsContextDefault(ilMailTemplate $template): void;

    public function setAsContextDefault(ilMailTemplate $template): void;
}
