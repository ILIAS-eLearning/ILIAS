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

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Button\Button;

/**
 * Taxonomy GS tool provider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilExerciseGSToolProvider extends AbstractDynamicToolProvider
{
    public const SHOW_EXC_ASSIGNMENT_INFO = 'show_exc_assignment_info';
    public const EXC_ASS_IDS = 'exc_ass_ids';
    public const EXC_ASS_BUTTONS = "exc_ass_buttons";

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main()->main();
    }

    public function getToolsForContextStack(
        CalledContexts $called_contexts
    ): array {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule("exc");

        $title = $lng->txt("exc_assignment");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("exc", $title);

        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_EXC_ASSIGNMENT_INFO, true)) {
            $iff = function ($id) {
                return $this->identification_provider->contextAwareIdentifier($id);
            };
            $l = function (string $content) {
                return $this->dic->ui()->factory()->legacy($content);
            };
            $tools[] = $this->factory->tool($iff("exc_ass_info"))
                ->withTitle($title)
                ->withSymbol($icon)
                ->withContentWrapper(
                /**
                 * @throws ilExcUnknownAssignmentTypeException
                 * @throws ilDateTimeException
                 */
                function () use ($l, $additional_data) {
                    $buttons = $additional_data->exists(self::EXC_ASS_BUTTONS)
                        ? $additional_data->get(self::EXC_ASS_BUTTONS)
                        : [];
                    return $l($this->getAssignmentInfo(
                        $additional_data->get(self::EXC_ASS_IDS),
                        $buttons
                    ));
                }
                );
        }

        return $tools;
    }

    /**
     * @param int[]   $ass_ids
     * @param Button[][] $buttons
     * @return string
     * @throws ilDateTimeException
     * @throws ilExcUnknownAssignmentTypeException
     */
    private function getAssignmentInfo(
        array $ass_ids,
        array $buttons
    ): string {
        global $DIC;

        $lng = $DIC->language();
        $user = $DIC->user();
        $ui = $DIC->ui();
        $access = $DIC->access();

        $html = "";

        foreach ($ass_ids as $ass_id) {
            $info = new ilExAssignmentInfo($ass_id, $user->getId());
            $exc_id = ilExAssignment::lookupExerciseId($ass_id);
            $readable_ref_id = 0;
            foreach (ilObject::_getAllReferences($exc_id) as $ref_id) {
                if ($access->checkAccess("read", "", $ref_id)) {
                    $readable_ref_id = $ref_id;
                }
            }

            $tpl = new ilTemplate("tpl.ass_info_tool.html", true, true, "Modules/Exercise");
            $assignment = new ilExAssignment($ass_id);

            $title = ilObject::_lookupTitle($exc_id) . ": " . $assignment->getTitle();
            if ($readable_ref_id > 0) {
                $title = $ui->renderer()->render(
                    $ui->factory()->link()->standard($title, ilLink::_getLink($readable_ref_id))
                );
            }

            $this->addSection($tpl, $lng->txt("exc_assignment"), $title);

            // schedule info
            $schedule = $info->getScheduleInfo();
            $list = $ui->factory()->listing()->unordered(array_map(function ($i) {
                return $i["txt"] . ": " . $i["value"];
            }, $schedule));
            $this->addSection($tpl, $lng->txt("exc_schedule"), $ui->renderer()->render($list));

            // latest submission
            $subm = $info->getSubmissionInfo();
            if (isset($subm["submitted"])) {
                $this->addSection($tpl, $subm["submitted"]["txt"], $subm["submitted"]["value"]);
            }

            // instruction
            $inst = $info->getInstructionInfo();
            if (count($inst)) {
                $this->addSection($tpl, $inst["instruction"]["txt"], $inst["instruction"]["value"]);
            }

            // instruction files
            $files = $info->getInstructionFileInfo($readable_ref_id);
            if (is_array($files)) {
                $list = $ui->factory()->listing()->unordered(array_map(function ($i) use ($ui) {
                    $v = $i["txt"];
                    if ($i["value"] != "") {
                        $v = $ui->renderer()->render($ui->factory()->button()->shy($v, $i["value"]));
                    }
                    return $v;
                }, $files));
                $this->addSection($tpl, $lng->txt("exc_instruction_files"), $ui->renderer()->render($list));
            }

            // buttons
            if (isset($buttons[$ass_id])) {
                $tpl->setVariable("BUTTONS", implode(" ", array_map(function ($b) use ($ui) {
                    return $ui->renderer()->render($b);
                }, $buttons[$ass_id])));
            }

            $tpl->setCurrentBlock("ass_info");
            $tpl->parseCurrentBlock();
            $html .= $tpl->get();
        }
        return $html;
    }

    protected function addSection(
        ilTemplate $tpl,
        string $title,
        string $content
    ): void {
        $tpl->setCurrentBlock("section");
        $tpl->setVariable("TITLE", $title);
        $tpl->setVariable("CONTENT", $content);
        $tpl->parseCurrentBlock();
    }
}
