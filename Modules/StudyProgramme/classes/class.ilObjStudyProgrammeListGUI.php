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

declare(strict_types=1);

use ILIAS\UI\Component\Button\Shy;

class ilObjStudyProgrammeListGUI extends ilObjectListGUI
{
    private ILIAS\UI\Renderer $renderer;
    protected ILIAS\UI\Factory $factory;

    public function __construct()
    {
        global $DIC;
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        parent::__construct();
        $this->lng->loadLanguageModule("prg");
    }

    public function init(): void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = false;
        $this->info_screen_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;

        $this->type = "prg";
        $this->gui_class_name = "ilobjstudyprogrammegui";

        // general commands array
        $this->commands = ilObjStudyProgrammeAccess::_getCommands();
    }

    /**
     * no timing commands needed for program.
     */
    public function insertTimingsCommand(): void
    {
    }

    /**
     * no social commands needed in program.
     */
    public function insertCommonSocialCommands($header_actions = false): void
    {
    }

    /**
     * @inheritdoc
     */
    public function getCommandLink(string $cmd): string
    {
        $this->ctrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $this->ref_id);

        return $this->ctrl->getLinkTargetByClass("ilobjstudyprogrammegui", $cmd);
    }

    public function getAsListItem(int $ref_id, int $obj_id, string $type, string $title, string $description): ?\ILIAS\UI\Component\Item\Item
    {
        $this->initItem(
            $ref_id,
            $obj_id,
            $type,
            $title,
            $description
        );
        $this->insertCommands(true, true);

        $prg = new ilObjStudyProgramme($ref_id);
        $assignments = $prg->getAssignments();
        if ($this->getCheckboxStatus() && count($assignments) > 0) {
            $this->setAdditionalInformation($this->lng->txt("prg_can_not_manage_in_repo"));
            $this->enableCheckbox(false);
        } else {
            $this->setAdditionalInformation(null);
        }
        /** @var ilStudyProgrammeUserTable $user_table */
        $user_table = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserTable'];
        $user_table->disablePermissionCheck(true);
        $data = $user_table->fetchData($prg->getId(), [$this->user->getId()]);
        $properties = [];
        if (count($data) === 1) {
            $data = $data[0];

            $min = $data->getPointsRequired();
            $max = $data->getPointsReachable();
            $cur = $data->getPointsCurrent();
            $required_string = $min;
            if ((float) $max < (float) $min) {
                $required_string .= ' ' . $this->lng->txt('prg_dash_label_unreachable') . ' (' . $max . ')';
            }

            $properties = [
                [$this->lng->txt('prg_dash_label_minimum') => $required_string],
                [$this->lng->txt('prg_dash_label_gain') => $cur],
                [$this->lng->txt('prg_dash_label_status') => $data->getStatus()],
            ];

            if (in_array(
                $data->getStatusRaw(),
                [ilPRGProgress::STATUS_COMPLETED, ilPRGProgress::STATUS_ACCREDITED],
                true
            )) {
                $validity = $data->getExpiryDate() ?: $data->getValidity();
                $properties[] = [$this->lng->txt('prg_dash_label_valid') => $validity];
            } else {
                $properties[] = [$this->lng->txt('prg_dash_label_finish_until') => $data->getDeadline()];
            }

            $validator = new ilCertificateDownloadValidator();
            if ($validator->isCertificateDownloadable($data->getUsrId(), $data->getNodeId())) {
                $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $prg->getRefId());
                $cert_url = $this->ctrl->getLinkTargetByClass(ilRepositoryGUI::class, 'deliverCertificate');
                $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', null);
                $cert_link = $this->factory->link()->standard($this->lng->txt('download_certificate'), $cert_url);
                $properties[] = [$this->lng->txt('certificate') => $this->renderer->render($cert_link)];
            }
        }


        $commands = array_map(
            fn (array $command): Shy => $this->factory->button()->shy(
                $command['title'],
                $command['link']
            ),
            $this->current_selection_list->getItems()
        );

        $link = $this->getCommandLink('');
        $title_btn = $this->factory->button()->shy($title, $link);
        $max = (int) $this->settings->get("rep_shorten_description_length");
        if ($max !== 0 && $this->settings->get("rep_shorten_description")) {
            $description = ilStr::shortenTextExtended($description, $max, true);
        }

        $icon = $this->factory->symbol()->icon()->standard('prg', $title, 'medium');
        return  $this->factory->item()->standard($title_btn)
                       ->withProperties(array_merge(...$properties))
                       ->withDescription($description)
                       ->withLeadIcon($icon);
    }

    /**
    * @inheritdoc
    */
    public function getListItemHTML(
        int $ref_id,
        int $obj_id,
        string $title,
        string $description,
        bool $use_async = false,
        bool $get_async_commands = false,
        string $async_url = "",
        int $context = self::CONTEXT_REPOSITORY
    ): string {
        return $this->renderer->render($this->getAsListItem(
            $ref_id,
            $obj_id,
            $this->type,
            $title,
            $description
        ));
    }
}
