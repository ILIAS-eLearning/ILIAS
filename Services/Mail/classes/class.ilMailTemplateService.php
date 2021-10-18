<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplateService
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailTemplateService
{
    protected ilMailTemplateRepository $repository;

    public function __construct(ilMailTemplateRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createNewTemplate(
        string $contextId,
        string $title,
        string $subject,
        string $message,
        string $language
    ) : ilMailTemplate {
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
    ) : void {
        $template = $this->repository->findById($templateId);

        $template->setContext($contextId);
        $template->setTitle($title);
        $template->setSubject($subject);
        $template->setMessage($message);
        $template->setLang($language);

        $this->repository->store($template);
    }

    public function loadTemplateForId(int $templateId) : ilMailTemplate
    {
        return $this->repository->findById($templateId);
    }

    /**
     * @param string $contextId
     * @return ilMailTemplate[]
     */
    public function loadTemplatesForContextId(string $contextId) : array
    {
        return $this->repository->findByContextId($contextId);
    }

    /**
     * @param int[] $templateIds
     */
    public function deleteTemplatesByIds(array $templateIds) : void
    {
        $this->repository->deleteByIds($templateIds);
    }

    /**
     * @return array[]
     */
    public function listAllTemplatesAsArray() : array
    {
        $templates = $this->repository->getAll();

        $templates = array_map(static function (\ilMailTemplate $template) : array {
            return $template->toArray();
        }, $templates);

        return $templates;
    }
    
    public function unsetAsContextDefault(ilMailTemplate $template) : void
    {
        $template->setAsDefault(false);

        $this->repository->store($template);
    }

    public function setAsContextDefault(ilMailTemplate $template) : void
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
