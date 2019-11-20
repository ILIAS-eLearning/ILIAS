<?php

/**
 * Class ilWebDAVMountInstructionsModalGUI
 */
class ilWebDAVMountInstructionsModalGUI
{
    /**
     * ID of the <div>-element, which contains the mount instructions
     */
    public const MOUNT_INSTRUCTIONS_CONTENT_ID = 'webdav_mount_instructions_content';

    /**
     * ilWebDAVMountInstructionsModalGUI constructor.
     * @param \ILIAS\UI\Factory $a_ui_factory
     * @param \ILIAS\UI\Renderer $a_ui_renderer
     * @param ilLanguage $a_lng
     */
    public function __construct(ILIAS\UI\Factory $a_ui_factory
        , ILIAS\UI\Renderer $a_ui_renderer
        , ilLanguage $a_lng)
    {
        global $DIC;
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

    /** @var ilWebDAVMountInstructionsModalGUI */
    private static $instance = null;
    private static $modal_already_rendered = false;

    /**
     * This is kind of a singleton pattern. But instead of getting creating only one instance of this class, an object
     * will be created which will only be rendered once into the global template.
     */
    public static function maybeRenderWebDAVModalInGlobalTpl()
    {
        global $DIC;
        if(!self::$modal_already_rendered)
        {
            if(self::$instance == null)
            {
                global $DIC;
                self::$instance = new ilWebDAVMountInstructionsModalGUI($DIC->ui()->factory(), $DIC->ui()->renderer(), $DIC->language());
            }

            self::$modal_already_rendered = true;
            $js_function = '<script>function triggerWebDAVModal(api_url){ $.ajax(api_url).done(function(data){ $(document).trigger("'.self::$instance->getModalShowSignalId().'", "{}"); $("#'.self::MOUNT_INSTRUCTIONS_CONTENT_ID.'").html(data);}) }</script>';

            $webdav_modal_html = self::$instance->getRenderedModal() . $js_function;

            $tpl = $DIC->ui()->mainTemplate();
            $tpl->setVariable('WEBDAV_MODAL', $webdav_modal_html);
        }
    }
}