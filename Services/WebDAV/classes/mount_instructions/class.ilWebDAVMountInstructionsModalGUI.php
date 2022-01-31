<?php declare(strict_types = 1);

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

class ilWebDAVMountInstructionsModalGUI
{
    private const MOUNT_INSTRUCTIONS_CONTENT_ID = 'webdav_mount_instructions_content';

    private function __construct(ilWebDAVMountInstructionsRepositoryImpl $repository, Factory $ui_factory, Renderer $ui_renderer, ilLanguage $lng)
    {
        $this->repository = $repository;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->lng = $lng;

        try {
            $document = $this->repository->getMountInstructionsByLanguage($this->lng->getUserLanguage());
            $title = $document->getTitle();
        } catch (InvalidArgumentException $e) {
            $title = $this->lng->txt('webfolder_instructions_titletext');
        }

        $content_div = '<div id="' . self::MOUNT_INSTRUCTIONS_CONTENT_ID . '"></div>';
        $page = $this->ui_factory->modal()->lightboxTextPage($content_div, $title);
        $this->modal = $this->ui_factory->modal()->lightbox($page);
    }
    
    private function getRenderedModal() : string
    {
        return $this->ui_renderer->render($this->modal);
    }
    
    private function getModalShowSignalId() : string
    {
        return $this->modal->getShowSignal()->getId();
    }
    
    private static bool $modal_already_rendered = false;
    
    public static function maybeRenderWebDAVModalInGlobalTpl() : void
    {
        if (self::$modal_already_rendered) {
            return;
        }
        
        global $DIC;
        $repository = new ilWebDAVMountInstructionsRepositoryImpl($DIC->database());
        $instance = new ilWebDAVMountInstructionsModalGUI($repository, $DIC->ui()->factory(), $DIC->ui()->renderer(), $DIC->language());

        self::$modal_already_rendered = true;
        $js_function = '<script>function triggerWebDAVModal(api_url){ $.ajax(api_url).done(function(data){ $(document).trigger("' . $instance->getModalShowSignalId() . '", "{}"); $("#' . self::MOUNT_INSTRUCTIONS_CONTENT_ID . '").html(data);}) }</script>';

        $webdav_modal_html = $instance->getRenderedModal() . $js_function;

        $tpl = $DIC->ui()->mainTemplate();
        $tpl->setVariable('WEBDAV_MODAL', $webdav_modal_html);
    }
}
