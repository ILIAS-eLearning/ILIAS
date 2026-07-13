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

use ILIAS\Mail\TemplateEngine\TemplateEngineFactoryInterface;
use ILIAS\Mail\Templates\TemplateSubjectSyntaxException;
use ILIAS\Mail\Templates\TemplateMessageSyntaxException;

class ilMailTemplateService implements ilMailTemplateServiceInterface
{
    public function __construct(
        protected ilMailTemplateRepository $repository,
        protected TemplateEngineFactoryInterface $template_engine_factory
    ) {
    }

    public function createNewTemplate(
        string $context_id,
        string $title,
        string $subject,
        string $message,
        string $language
    ): ilMailTemplate {
        try {
            $this->template_engine_factory->getBasicEngine()->render($subject, []);
        } catch (Exception) {
            throw new TemplateSubjectSyntaxException('Invalid mail template for subject');
        }

        try {
            $this->template_engine_factory->getBasicEngine()->render($message, []);
        } catch (Exception) {
            throw new TemplateMessageSyntaxException('Invalid mail template for message');
        }

        $template = new ilMailTemplate();
        $template->setContext($context_id);
        $template->setTitle($title);
        $template->setSubject($subject);
        $template->setMessage($message);
        $template->setLang($language);

        $this->repository->store($template);

        return $template;
    }

    public function modifyExistingTemplate(
        int $template_id,
        string $context_id,
        string $title,
        string $subject,
        string $message,
        string $language
    ): void {
        try {
            $this->template_engine_factory->getBasicEngine()->render($subject, []);
        } catch (Exception) {
            throw new TemplateSubjectSyntaxException('Invalid mail template for subject');
        }

        try {
            $this->template_engine_factory->getBasicEngine()->render($message, []);
        } catch (Exception) {
            throw new TemplateMessageSyntaxException('Invalid mail template for message');
        }

        $template = $this->repository->findById($template_id);

        $template->setContext($context_id);
        $template->setTitle($title);
        $template->setSubject($subject);
        $template->setMessage($message);
        $template->setLang($language);

        $this->repository->store($template);
    }

    public function loadTemplateForId(int $template_id): ilMailTemplate
    {
        return $this->repository->findById($template_id);
    }

    public function loadTemplatesForContextId(string $context_id): array
    {
        return $this->repository->findByContextId($context_id);
    }

    public function deleteTemplatesByIds(array $template_ids): void
    {
        $this->repository->deleteByIds($template_ids);
    }

    public function listAllTemplatesAsArray(): array
    {
        $templates = $this->repository->getAll();

        return array_map(static fn(ilMailTemplate $template): array => $template->toArray(), $templates);
    }

    public function unsetAsContextDefault(ilMailTemplate $template): void
    {
        $template->setAsDefault(false);

        $this->repository->store($template);
    }

    public function setAsContextDefault(ilMailTemplate $template): void
    {
        $all_of_context = $this->repository->findByContextId($template->getContext());
        foreach ($all_of_context as $other_template) {
            $other_template->setAsDefault(false);

            if ($template->getTplId() === $other_template->getTplId()) {
                $other_template->setAsDefault(true);
            }

            $this->repository->store($other_template);
        }
    }
}
