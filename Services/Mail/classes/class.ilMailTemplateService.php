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

class ilMailTemplateService implements ilMailTemplateServiceInterface
{
    public function __construct(
        protected ilMailTemplateRepository $repository,
        protected ilMustacheFactory $mustacheFactory
    ) {
    }

    public function createNewTemplate(
        string $contextId,
        string $title,
        string $subject,
        string $message,
        string $language
    ): ilMailTemplate {
        try {
            $this->mustacheFactory->getBasicEngine()->render($subject, []);
        } catch (Exception) {
            throw new TemplateSubjectSyntaxException('Invalid mail template for subject');
        }

        try {
            $this->mustacheFactory->getBasicEngine()->render($message, []);
        } catch (Exception) {
            throw new TemplateMessageSyntaxException('Invalid mail template for message');
        }

        $template = new ilMailTemplate();
        $template->setContext($contextId);
        $template->setTitle($title);
        $template->setSubject($subject);
        $template->setMessage($message);
        $template->setLang($language);

        $this->repository->store($template);

        return $template;
    }

    public function modifyExistingTemplate(
        int $templateId,
        string $contextId,
        string $title,
        string $subject,
        string $message,
        string $language
    ): void {
        try {
            $this->mustacheFactory->getBasicEngine()->render($subject, []);
        } catch (Exception) {
            throw new TemplateSubjectSyntaxException('Invalid mail template for subject');
        }

        try {
            $this->mustacheFactory->getBasicEngine()->render($message, []);
        } catch (Exception) {
            throw new TemplateMessageSyntaxException('Invalid mail template for message');
        }

        $template = $this->repository->findById($templateId);

        $template->setContext($contextId);
        $template->setTitle($title);
        $template->setSubject($subject);
        $template->setMessage($message);
        $template->setLang($language);

        $this->repository->store($template);
    }

    public function loadTemplateForId(int $templateId): ilMailTemplate
    {
        return $this->repository->findById($templateId);
    }

    public function loadTemplatesForContextId(string $contextId): array
    {
        return $this->repository->findByContextId($contextId);
    }

    public function deleteTemplatesByIds(array $templateIds): void
    {
        $this->repository->deleteByIds($templateIds);
    }

    public function listAllTemplatesAsArray(): array
    {
        $templates = $this->repository->getAll();

        return array_map(static function (ilMailTemplate $template): array {
            return $template->toArray();
        }, $templates);
    }

    public function unsetAsContextDefault(ilMailTemplate $template): void
    {
        $template->setAsDefault(false);

        $this->repository->store($template);
    }

    public function setAsContextDefault(ilMailTemplate $template): void
    {
        $allOfContext = $this->repository->findByContextId($template->getContext());
        foreach ($allOfContext as $otherTemplate) {
            $otherTemplate->setAsDefault(false);

            if ($template->getTplId() === $otherTemplate->getTplId()) {
                $otherTemplate->setAsDefault(true);
            }

            $this->repository->store($otherTemplate);
        }
    }
}
