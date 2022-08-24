<?php

declare(strict_types=1);

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

use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services;

/**
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVMountInstructionsGUI
{
    protected ilWebDAVUriBuilder $uri_builder;
    protected ilWebDAVBaseMountInstructions $mount_instruction;
    protected ilLanguage$lang;
    protected UIServices $ui;
    protected Services $http;

    public function __construct(ilWebDAVBaseMountInstructions $mount_instruction, ilLanguage $lang, UIServices $ui, Services $http)
    {
        $this->uri_builder = new ilWebDAVUriBuilder($http->request());
        $this->mount_instruction = $mount_instruction;
        $this->lang = $lang;
        $this->ui = $ui;
        $this->http = $http;
    }

    /**
     * @param mixed[] $mount_instructions
     */
    public function buildGUIFromGivenMountInstructions(array $mount_instructions, bool $render_async = false): string
    {
        $os = $this->determineOSfromUserAgent();

        $f = $this->ui->factory();
        $r = $this->ui->renderer();

        $components = [];

        $js_function_legacy = $f->legacy('<script>'
            . 'il.UI.showMountInstructions = function (e, id){'
            . "obj = $(e['target']);"
            . "obj.siblings().removeClass('engaged disabled ilSubmitInactive').attr('aria-pressed', 'false');"
            . "obj.siblings().removeAttr('disabled');"
            . "obj.addClass('engaged ilSubmitInactive').attr('aria-pressed', 'true');"
            . '$(".instructions").hide();'
            . '$("#"+id).show();}</script>');

        if (count($mount_instructions) === 1) {
            $content = $f->legacy("<div class='instructions'>" . array_shift($mount_instructions) . "</div>");

            return $render_async ? $r->renderAsync($content) : $r->render($content);
        }

        $view_control_actions = [];

        $selected = array_key_first($mount_instructions);

        foreach ($mount_instructions as $title => $text) {
            foreach ($os as $os_string) {
                if (stristr($title, $os_string) !== false) {
                    $selected = $title;
                    break 2;
                }
            }
        }

        foreach ($mount_instructions as $title => $text) {
            if ($title == $selected) {
                $hidden = '';
            } else {
                $hidden = 'style="display: none;"';
            }

            $legacy = $f->legacy("<div id='$title' class='instructions' $hidden>$text</div>")
                ->withCustomSignal($title, "il.UI.showMountInstructions(event, '$title');");

            $view_control_actions[$title] = $legacy->getCustomSignal($title);

            $components[] = $legacy;
        }

        $view_control = $f->viewControl()->mode($view_control_actions, "mount-instruction-buttons")->withActive($selected);

        // Add view control and legacy add the beginning of the array (so they will be rendered first)
        $header_components = [
            $f->legacy("<div class='webdav-view-control'>"),
            $view_control,
            $f->legacy("</div>"),
            $js_function_legacy];

        $components = array_merge($header_components, $components);

        return $render_async ? $r->renderAsync($components) : $r->render($components);
    }

    public function renderMountInstructionsContent(): void
    {
        try {
            $instructions = $this->mount_instruction->getMountInstructionsAsArray();
        } catch (InvalidArgumentException $e) {
            $document_processor = new ilWebDAVMountInstructionsHtmlDocumentProcessor(new ilWebDAVMountInstructionsDocumentPurifier());
            $instructions = $document_processor->processMountInstructions($this->lang->txt('webfolder_instructions_text'));
            $instructions = $this->mount_instruction->getMountInstructionsAsArray($instructions);
            if ($instructions == '' || $instructions == '-webfolder_instructions_text-') {
                $instructions = ["<div class='alert alert-danger'>" . $this->lang->txt('error') . ": " . $this->lang->txt('webdav_missing_lang') . "</div>"];
            }
        }

        echo $this->buildGUIFromGivenMountInstructions($instructions, true);
        exit;
    }

    private function determineOSfromUserAgent(): array
    {
        $ua = $this->http->request()->getHeader('User-Agent')[0];

        if (stristr($ua, 'windows') !== false
            || strpos($ua, 'microsoft') !== false) {
            return ['win'];
        }

        if (stristr($ua, 'darwin') !== false
            || stristr($ua, 'macintosh') !== false) {
            return ['mac', 'osx'];
        }

        if (stristr($ua, 'linux') !== false
            || stristr($ua, 'solaris') !== false
            || stristr($ua, 'aix') !== false
            || stristr($ua, 'unix') !== false
            || stristr($ua, 'gvfs') !== false // nautilus browser uses this ID
            ) {
            return ['linux'];
        }

        return ['unknown'];
    }
}
