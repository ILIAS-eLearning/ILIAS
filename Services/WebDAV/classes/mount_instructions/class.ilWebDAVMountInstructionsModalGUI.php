<?php


class ilWebDAVMountInstructionsModalGUI
{
    public function __construct(ilWebDAVMountInstructions $a_mount_instructions
        , ILIAS\UI\Factory $a_ui_factory
        , ILIAS\UI\Renderer $a_ui_renderer
        , ilLanguage $a_lng)
    {
        $this->mount_instructions = $a_mount_instructions;
        $this->ui_factory = $a_ui_factory;
        $this->ui_renderer = $a_ui_renderer;
        $this->lng = $a_lng;
    }

    public function getMountInstructionsModal(): ILIAS\UI\Component\Modal\Lightbox
    {
        $instructions_array = $this->mount_instructions->getMountInstructionsAsArray();

        $rendered_instructions = $this->createRenderedMountInstructions($instructions_array);

        $modal_page = $this->ui_factory->modal()->lightboxTextPage($rendered_instructions, $this->lng->txt('webdav_mount_instructions_title'));
        $modal = $this->ui_factory->modal()->lightbox($modal_page);
        return $modal;
    }

    public function printMountInstructionModalAndExit()
    {
        // Create and render modal
        $modal = $this->getMountInstructionsModal();
        $html_modal = $this->ui_renderer->renderAsync($modal);

        // Print and exit
        echo $html_modal;
        exit;
    }

    protected function createRenderedMountInstructions(array $instructions_array)
    {
        $view_control_actions = array();
        $components = array();
        $aria_label = "change_displayed_mount_instructions";

        foreach($instructions_array as $title => $instructions)
        {
            $legacy = $this->ui_factory->legacy($instructions);
            $components[] = $legacy;

            // TODO: Replace this with signal as soon as it is implemented (currently not possible...)
            $view_control_actions[$title] = "https://ilias.de";
        }
        $view_control = $this->ui_factory->viewControl()->mode($view_control_actions, $aria_label);

        // Add view control and separator before legacy components
        array_unshift($components, $this->ui_factory->divider()->horizontal());
        array_unshift($components, $view_control);

        return $this->ui_renderer->render($components);
    }

    public function getAsAsyncModal(string $uri)
    {
        $page = $this->ui_factory->modal()->lightboxTextPage('', '');
        $modal = $this->ui_factory->modal()->lightbox($page)->withAsyncRenderUrl($uri);
        return $modal;
    }
}