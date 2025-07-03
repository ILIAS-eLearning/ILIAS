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

class ContSkillMemberTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected SkillInternalManagerService $manager_service,
        protected ContainerSkillManager $cont_skill_manager,
        protected \ilContainer $container,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, true);
    }

    protected function getId(): string
    {
        return "cont_skll_mem_" . $this->container->getId();
    }

    protected function getTitle(): string
    {
        global $DIC;
        return $DIC->language()->txt("cont_cont_skills");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->manager_service->contSkillMemberRetrieval(
            $this->cont_skill_manager,
            $this->container
        );
    }

    protected function transformRow(array $data_row): array
    {
        global $DIC;
        $lng = $DIC->language();

        // Format skills display
        $skills_display = "";
        if (!empty($data_row["skills"])) {
            $skill_texts = [];
            /** @var ContainerMemberSkill $skill */
            foreach ($data_row["skills"] as $skill) {
                $path = $this->parent_gui->getPathString($skill->getBaseSkillId(), $skill->getTrefId());
                $skill_title = \ilBasicSkill::_lookupTitle($skill->getBaseSkillId(), $skill->getTrefId());
                $level_title = \ilBasicSkill::lookupLevelTitle($skill->getLevelId());

                $skill_text = $skill_title . ": " . $level_title;
                if ($path !== "") {
                    $skill_text .= " (" . $path . ")";
                }
                $skill_texts[] = $skill_text;
            }
            $skills_display = implode("<br>", $skill_texts);
        }

        return [
            "id" => $data_row["id"],
            "name" => $data_row["name"],
            "login" => $data_row["login"],
            "cont_mem_skills" => $skills_display,
            "cont_published" => $data_row["published"] ? $lng->txt("yes") : $lng->txt("no"),
            "published" => $data_row["published"]
        ];
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        $container_publish_setting = \ilContainer::_lookupContainerSetting(
            $this->container->getId(),
            "cont_skill_publish",
            '0'
        );

        switch ($action) {
            case "assignCompetences":
                // Available when not published OR publish setting disabled
                return !$data_row["published"] || $container_publish_setting === '0';
            case "publishAssignments":
                // Available when not published
                return !$data_row["published"];
            case "deassignCompetencesConfirm":
                // Always available
                return true;
        }
        return true;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        global $DIC;
        $lng = $DIC->language();

        $table = $table
            ->textColumn("name", $lng->txt("name"))
            ->textColumn("login", $lng->txt("login"))
            ->textColumn("cont_mem_skills", $lng->txt("cont_mem_skills"))
            ->textColumn("cont_published", $lng->txt("cont_published"));

        // Add single actions
        $table = $table
            ->singleAction("assignCompetences", $lng->txt("cont_assign_competence"))
            ->standardAction("deassignCompetencesConfirm", $lng->txt("cont_deassign_competence"));

        // Add multi commands based on container settings
        $container_publish_setting = \ilContainer::_lookupContainerSetting(
            $this->container->getId(),
            "cont_skill_publish",
            '0'
        );

        if ($container_publish_setting === '1') {
            $table = $table->standardAction("publishAssignments", $lng->txt("cont_publish_assignment"));
        }

        return $table;
    }
}
