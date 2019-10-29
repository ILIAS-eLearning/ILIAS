<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Taxonomy GS tool provider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilExerciseGSToolProvider extends AbstractDynamicToolProvider
{

    const SHOW_EXC_ASSIGNMENT_INFO = 'show_exc_assignment_info';
    const EXC_ASS_IDS = 'exc_ass_ids';
    const EXC_ASS_BUTTONS = "exc_ass_buttons";

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->main();
    }


    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule("exc");

        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_EXC_ASSIGNMENT_INFO, true)) {

            $iff = function ($id) { return $this->identification_provider->identifier($id); };
            $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };
            $tools[] = $this->factory->tool($iff("exc_ass_info"))
                ->withTitle($lng->txt("exc_assignment"))
                ->withContent($l($this->getAssignmentInfo(
                        $additional_data->get(self::EXC_ASS_IDS),
                        $additional_data->get(self::EXC_ASS_BUTTONS)
                    ))
                );
        }

        return $tools;
    }

    /**
     * @param $ass_id
     * @return string
     */
    private function getAssignmentInfo($ass_ids, $buttons) : string {
        global $DIC;

        $lng = $DIC->language();
        $user = $DIC->user();
        $ui = $DIC->ui();
        $access = $DIC->access();


        foreach ($ass_ids as $ass_id) {

            $info = new ilExAssignmentInfo($ass_id, $user->getId());
            $exc_id = ilExAssignment::lookupExerciseId($ass_id);
            foreach (ilObject::_getAllReferences($exc_id) as $ref_id) {
                if ($access->checkAccess("read", "", $ref_id)) {
                    $readable_ref_id = $ref_id;
                }
            }

            $tpl = new ilTemplate("tpl.ass_info_tool.html", true, true, "Modules/Exercise");
            $assignment = new ilExAssignment($ass_id);

            $title = ilObject::_lookupTitle($exc_id).": ".$assignment->getTitle();
            if ($readable_ref_id > 0) {
                $title = $ui->renderer()->render(
                    $ui->factory()->link()->standard($title, ilLink::_getLink($readable_ref_id))
                );
            }

            $this->addSection($tpl, $lng->txt("exc_assignment"), $title);

            // schedule info
            $schedule = $info->getScheduleInfo();
            $list = $ui->factory()->listing()->unordered(array_map(function($i) {
                return $i["txt"].": ".$i["value"];
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
            if (is_array($buttons[$ass_id])) {
                $tpl->setVariable("BUTTONS", implode(" ", array_map(function ($b) use ($ui) {
                    return $ui->renderer()->render($b);
                }, $buttons[$ass_id])));
            }

            $tpl->setCurrentBlock("ass_info");
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * Add section
     *
     * @param ilTemplate $tpl
     * @param string $title
     * @param string $content
     */
    protected function addSection(ilTemplate $tpl, string $title, string $content)
    {
        $tpl->setCurrentBlock("section");
        $tpl->setVariable("TITLE", $title);
        $tpl->setVariable("CONTENT", $content);
        $tpl->parseCurrentBlock();
    }

}
