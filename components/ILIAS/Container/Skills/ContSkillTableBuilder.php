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

class ContSkillTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected SkillInternalManagerService $manager_service,
        protected ContainerSkillManager $cont_skill_manager,
        protected int $container_obj_id,
        protected int $container_ref_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return "cont_skill";
    }

    protected function getTitle(): string
    {
        global $DIC;
        return $DIC->language()->txt("cont_cont_skills");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->manager_service->contSkillRetrieval(
            $this->cont_skill_manager
        );
    }

    protected function transformRow(array $data_row): array
    {
        $path = $this->parent_gui->getPathString($data_row["base_skill_id"], $data_row["tref_id"]);

        return [
            "id" => $data_row["base_skill_id"] . ":" . $data_row["tref_id"],
            "title" => $data_row["title"],
            "path" => $path,
            "profile_title" => $data_row["profile_title"] ?? "",
            "base_skill_id" => $data_row["base_skill_id"],
            "tref_id" => $data_row["tref_id"],
            "has_profile" => isset($data_row["profile_title"])
        ];
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        switch ($action) {
            case "confirmRemoveSelectedSkill":
                // Only allow removal for skills without profile
                return !isset($data_row["profile_title"]);
        }
        return true;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        global $DIC;
        $lng = $DIC->language();

        $table = $table
            ->textColumn("title", $lng->txt("cont_skill"))
            ->textColumn("path", $lng->txt("cont_path"))
            ->textColumn("profile_title", $lng->txt("cont_skill_profile"));

        // Add multi command for removing skills
        $table = $table->singleAction(
            "confirmRemoveSelectedSkill",
            $lng->txt("remove")
        );

        return $table;
    }
}
