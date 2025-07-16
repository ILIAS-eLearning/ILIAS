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

namespace ILIAS\Blog\Contributor;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class ContributorTableBuilder extends CommonTableBuilder
{
    protected array $local_roles = [];
    protected array $contributor_ids = [];

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected array $roles,
        object $parent_gui,
        string $parent_cmd
    ) {
        $this->local_roles = $roles;
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "contributor";
    }

    protected function getTitle(): string
    {
        if ($this->contributor_ids) {
            return $this->domain->lng()->txt("blog_contributor_container_add");
        } else {
            return $this->domain->lng()->txt("blog_contributors");
        }
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new ContributorRetrieval(
            $this->domain->rbac()->review(),
            $this->local_roles
        );
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "name" => $data_row["name"],
            "role" => implode(", ", $data_row["role"])
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn("name", $lng->txt("name"), true)
            ->textColumn("role", $lng->txt("obj_role"));

        if ($this->contributor_ids) {
            $table = $table->multiAction(
                "addContributorContainerAction",
                $lng->txt("add")
            );
        } else {
            $table = $table->multiAction(
                "confirmRemoveContributor",
                $lng->txt("remove")
            );
        }

        return $table;
    }
}
