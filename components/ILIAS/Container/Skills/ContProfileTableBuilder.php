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

namespace ILIAS\Container\Skills;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Skill\Service\SkillProfileService;

class ContProfileTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected SkillInternalManagerService $manager_service,
        protected SkillProfileService $profile_service,
        protected \ilSkillManagementSettings $skmg_settings,
        protected int $cont_ref_id,
        protected int $cont_member_role_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "cont_profile";
    }

    protected function getTitle(): string
    {
        global $DIC;
        return $DIC->language()->txt("cont_skill_ass_profiles");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->manager_service->contProfileRetrieval(
            $this->profile_service,
            $this->skmg_settings,
            $this->cont_member_role_id
        );
    }

    protected function transformRow(array $data_row): array
    {
        global $DIC;
        $lng = $DIC->language();

        if ($this->profile_service->lookupProfileRefId($data_row["profile_id"]) > 0) {
            $context = $lng->txt("skmg_context_local");
        } else {
            $context = $lng->txt("skmg_context_global");
        }

        return [
            "id" => $data_row["profile_id"],
            "title" => $data_row["title"],
            "context" => $context
        ];
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        switch ($action) {
            case "confirmDeleteSingleLocalProfile":
            case "editProfile":
                return ($data_row["profile_ref_id"] > 0);
            case "confirmRemoveSingleGlobalProfile":
                return ($data_row["profile_ref_id"] === 0);
        }
        return true;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        global $DIC;
        $lng = $DIC->language();

        $table = $table
            ->textColumn("title", $lng->txt("cont_skill_profile"))
            ->textColumn("context", $lng->txt("context"));

        $DIC->ctrl()->setParameterByClass("ilskillprofilegui", "local_context", true);

        // Add single actions for different profile types
        $table = $table
            ->singleRedirectAction(
                "editProfile",
                $lng->txt("edit"),
                ["ilSkillProfileGUI"],
                "showLevelsWithLocalContext",
                "sprof_id"
            )
            ->singleAction(
                "confirmDeleteSingleLocalProfile",
                $lng->txt("delete")
            )
            ->singleAction(
                "confirmRemoveSingleGlobalProfile",
                $lng->txt("remove")
            );

        return $table;
    }
}
