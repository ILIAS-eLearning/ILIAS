<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplateService
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailTemplateService
{
    /** @var \ilMailTemplateRepository */
    protected $repository;

    /**
     * ilMailTemplateService constructor.
     * @param ilMailTemplateRepository $repository
     */
    public function __construct(\ilMailTemplateRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $contextId
     * @param string $title
     * @param string $subject
     * @param string $message
     * @param string $language
     * @return \ilMailTemplate
     */
    public function createNewTemplate(
        string $contextId,
        string $title,
        string $subject,
        string $message,
        string $language
    ) : \ilMailTemplate {
        $template = new \ilMailTemplate();
        $template->setContext($contextId);
        $template->setTitle($title);
        $template->setSubject($subject);
        $template->setMessage($message);
        $template->setLang($language);

        $this->repository->store($template);

        return $template;
    }

    /**
     * @param int $templateId
     * @param string $contextId
     * @param string $title
     * @param string $subject
     * @param string $message
     * @param string $language
     */
    public function modifyExistingTemplate(
        int $templateId,
        string $contextId,
        string $title,
        string $subject,
        string $message,
        string $language
    ) {
        $template = $this->repository->findById($templateId);

        $template->setContext($contextId);
        $template->setTitle($title);
        $template->setSubject($subject);
        $template->setMessage($message);
        $template->setLang($language);

        $this->repository->store($template);
    }

    /**
     * @param int $templateId
     * @return \ilMailTemplate
     */
    public function loadTemplateForId(int $templateId) : \ilMailTemplate
    {
        return $this->repository->findById($templateId);
    }

    /**
     * @param string $contextId
     * @return \ilMailTemplate[]
     */
    public function loadTemplatesForContextId(string $contextId) : array
    {
        return $this->repository->findByContextId($contextId);
    }

    /**
     * @param array $templateIds
     */
    public function deleteTemplatesByIds(array $templateIds)
    {
        $this->repository->deleteByIds($templateIds);
    }

    /**
     * @return array[]
     */
    public function listAllTemplatesAsArray() : array
    {
        $templates = $this->repository->getAll();

        $templates = array_map(function (\ilMailTemplate $template) {
            return $template->toArray();
        }, $templates);

        return $templates;
    }

    /**
     * @param \ilMailTemplate $template
     */
    public function unsetAsContextDefault(\ilMailTemplate $template)
    {
        $template->setAsDefault(false);

        $this->repository->store($template);
    }

    /**
     * @param \ilMailTemplate $template
     */
    public function setAsContextDefault(\ilMailTemplate $template)
    {
        $allOfContext = $this->repository->findByContextId($template->getContext());
        foreach ($allOfContext as $otherTemplate) {
            $otherTemplate->setAsDefault(false);

            if ((int) $template->getTplId() === (int) $otherTemplate->getTplId()) {
                $otherTemplate->setAsDefault(true);
            }

            $this->repository->store($otherTemplate);
        }
    }
}
