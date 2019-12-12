<?php

/**
 * Class ilWebDAVMountInstructionsGUI
 *
 * This class delivers or prints a representation of the mount instructions
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVMountInstructionsGUI
{
    
    /**
     *
     * @var $mount_instruction ilWebDAVObjectMountInstructions
     */
    protected $protocol_prefixes;
    protected $base_url;
    protected $ref_id;
    protected $mount_instruction;
    protected $il_lang;
    protected $ui;
    
    public function __construct(ilWebDAVBaseMountInstructions $a_mount_instruction)
    {
        global $DIC;

        $this->uri_builder = new ilWebDAVUriBuilder($DIC->http()->request());
        $this->mount_instruction = $a_mount_instruction;
        $this->il_lang = $DIC->language();
        $this->ui = $DIC->ui();
    }

    public function buildGUIFromGivenMountInstructions($a_mount_instructions, $a_render_async = false)
    {
        $f = $this->ui->factory();
        $r = $this->ui->renderer();

        // List of all components to render
        $comps = array();

        // This is an additional legacy component. It contains the java script function to substitute the shown instructions
        $js_function_legacy = $f->legacy('<script>'
            . 'il.UI.showMountInstructions = function (e, id){'
            // e['target'] is the id for the button which was clicked (e.g. 'button#il_ui_fw_1234')
            . "obj = $(e['target']);"
            // Sets all buttons to the "unclicked" state
            . "obj.siblings().removeClass('engaged disabled ilSubmitInactive').attr('aria-pressed', 'false');"
            . "obj.siblings().removeAttr('disabled');"
            // Sets the clicked button into the "clicked" state
            . "obj.addClass('engaged disabled ilSubmitInactive').attr('aria-pressed', 'true');"
            . "obj.attr('disabled', 'disabled');"
            // Hide all instruction divs at first
            . '$(".instructions").hide();'
            // Show the div which is given as an argument
            . '$("#"+id).show();}</script>');

        /*
         * The document might just contain a single entry, then we don't need a view control and just return it.
         */
        if (count($a_mount_instructions) === 1) {
            $content = $f->legacy("<div class='instructions'>" . array_shift($a_mount_instructions) . "</div>");
            
            return $a_render_async ? $r->renderAsync($content) : $r->render($content);
        }
        
        /*
         * This is an associative array. The key is the title of the button, the value the used signal. E.g.:
         * array(
         *      "WINDOWS" => signal_for_windows_legacy_component,
         *      "MAC" => signal_for_mac_legacy_component,
         *      "LINUX" => signal_for_linux_legacy_component);
         */
        $view_control_actions = array();

        // Used to hide all <div>'s except for the first one
        $hidden = "";
        foreach ($a_mount_instructions as $title => $text) {
            // Create legacy component for mount instructions. Mount instructions text is wrapped in a <div>-tag
            $legacy = $f->legacy("<div id='$title' class='instructions' $hidden>$text</div>")
                ->withCustomSignal($title, "il.UI.showMountInstructions(event, '$title');");

            // Add to the list of components to render
            $comps[] = $legacy;

            // Add signal to the list for the view control
            $view_control_actions[$title] = $legacy->getCustomSignal($title);

            // Used to hide all <div>'s except for the first one
            if ($hidden == "") {
                $hidden = 'style="display: none;"';
            }
        }

        $view_control = $f->viewControl()->mode($view_control_actions, "mount-instruction-buttons");

        // Add view control and legacy add the beginning of the array (so they will be rendered first)
        $header_comps = array(
            $f->legacy("<div style='text-align: center'>"),
            $view_control,
            $f->legacy("</div>"),
            $js_function_legacy);

        $comps = array_merge($header_comps, $comps);

        return $a_render_async ? $r->renderAsync($comps) : $r->render($comps);
    }

    public function renderMountInstructionsContent()
    {
        try {
            $instructions = $this->mount_instruction->getMountInstructionsAsArray();
        } catch (InvalidArgumentException $e) {
            $document_processor = new ilWebDAVMountInstructionsHtmlDocumentProcessor(new ilWebDAVMountInstructionsDocumentPurifier());
            $instructions = $document_processor->processMountInstructions($this->il_lang->txt('webfolder_instructions_text'));
            $instructions = $this->mount_instruction->getMountInstructionsAsArray($instructions);
            if ($instructions == '' || $instructions == '-webfolder_instructions_text-') {
                $instructions = ["<div class='alert alert-danger'>" . $this->il_lang->txt('error') . ": " . $this->il_lang->txt('webdav_missing_lang') . "</div>"];
            }
        }

        echo $this->buildGUIFromGivenMountInstructions($instructions, true);
        exit;
    }
}
