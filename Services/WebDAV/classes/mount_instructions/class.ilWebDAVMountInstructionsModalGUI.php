<?php


class ilWebDAVMountInstructionsModalGUI
{
    public const MOUNT_INSTRUCTIONS_CONTENT_ID = 'webdav_mount_instructions_content';

    public function __construct(ilWebDAVBaseMountInstructions $a_mount_instructions
        , ILIAS\UI\Factory $a_ui_factory
        , ILIAS\UI\Renderer $a_ui_renderer
        , ilLanguage $a_lng)
    {
        global $DIC;
        $this->mount_instructions = $a_mount_instructions;
        $this->repository = new ilWebDAVMountInstructionsRepositoryImpl($DIC->database());
        $this->ui_factory = $a_ui_factory;
        $this->ui_renderer = $a_ui_renderer;
        $this->lng = $a_lng;

        $document = $this->repository->getMountInstructionsByLanguage($this->lng->getUserLanguage());
        $content_div = '<div id="'. self::MOUNT_INSTRUCTIONS_CONTENT_ID .'"></div>';
        $page = $this->ui_factory->modal()->lightboxTextPage($content_div, $document->getTitle());
        $this->modal = $this->ui_factory->modal()->lightbox($page);
    }

    /**
     * @return string
     */
    public function getRenderedModal()
    {
        return $this->ui_renderer->render($this->modal);
    }

    /**
     * @return string
     */
    public function getModalShowSignalId()
    {
        return $this->modal->getShowSignal()->getId();
    }
}