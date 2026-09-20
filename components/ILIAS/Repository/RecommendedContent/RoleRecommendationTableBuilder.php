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

namespace ILIAS\Repository\RecommendedContent;

use ILIAS\Repository\InternalDomainService;
use ILIAS\Repository\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class RoleRecommendationTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $role_id,
        protected \ilRecommendedContentManager $manager,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return 'objrolepd';
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('rep_recommended_content') .
            ', ' . $this->domain->lng()->txt('obj_role') . ': ' .
            \ilObjRole::_getTranslation(\ilObject::_lookupTitle($this->role_id));
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->recommendedContentRoleRetrieval($this->role_id, $this->manager);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            'id' => $data_row['id'],
            'title' => $data_row['title'],
            'path' => $data_row['path']
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn('title', $lng->txt('title'), true)
            ->textColumn('path', $lng->txt('path'))
            ->singleAction('confirmRemoveItem', $lng->txt('remove'), true);
    }

}
