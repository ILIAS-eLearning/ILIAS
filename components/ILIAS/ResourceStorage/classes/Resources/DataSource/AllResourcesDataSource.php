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

namespace ILIAS\Services\ResourceStorage\Resources\DataSource;

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Services\ResourceStorage\Resources\Listing\SortDirection;
use ILIAS\Services\ResourceStorage\Resources\UI\BaseToComponent;
use ILIAS\UI\Factory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class AllResourcesDataSource extends BaseTableDataSource implements TableDataSource
{
    public function __construct()
    {
        global $DIC;
        parent::__construct(
            $DIC->resourceStorage()->collection()->id() // ad hoc collection
        );
    }

    public function process(): void
    {
        $r = $this->db->query($this->getCountQuery());
        $this->filtered_amount_of_items = (int)$this->db->fetchAssoc($r)['cnt'];

        $r = $this->db->query($this->getDataQuery());

        $rid_strings = [];
        while ($d = $this->db->fetchAssoc($r)) {
            $rid_strings[] = $d['rid'] ?? '';
        }
        $this->irss->preload($rid_strings);
        foreach ($rid_strings as $rid_string) {
            $rid_instance = $this->irss->manage()->find($rid_string);
            if (!$rid_instance instanceof \ILIAS\ResourceStorage\Identification\ResourceIdentification) {
                continue;
            }
            $this->collection->add($rid_instance);
        }
    }

    public function getSortationsMapping(): array
    {
        return [
            SortDirection::BY_TITLE_ASC => 'revision_title ASC', // Default
            SortDirection::BY_TITLE_DESC => 'revision_title DESC',
            SortDirection::BY_SIZE_ASC => 'file_size ASC',
            SortDirection::BY_SIZE_DESC => 'file_size DESC',
            SortDirection::BY_CREATION_DATE_ASC => 'creation_date ASC',
            SortDirection::BY_CREATION_DATE_DESC => 'creation_date DESC',
        ];
    }

    /**
     * @return array{title: \ILIAS\UI\Component\Input\Field\Text, size: \ILIAS\UI\Component\Input\Field\Numeric, stakeholder: \ILIAS\UI\Component\Input\Field\Select}
     */
    public function getFilterItems(
        Factory $ui_factory,
        \ilLanguage $lng
    ): array {
        $stakeholders = [];
        $r = $this->db->query('SELECT DISTINCT * FROM il_resource_stkh');
        while ($d = $this->db->fetchAssoc($r)) {
            $class_name = $d['class_name'] ?? '';
            if (!class_exists($class_name)) {
                continue;
            }
            /** @var ResourceStakeholder $stakeholder */
            $stakeholder = new $class_name();

            $stakeholders[$d['id']] = $stakeholder->getConsumerNameForPresentation();
        }


        return [
            'title' => $ui_factory->input()->field()->text(
                $lng->txt('title'),
            ),
            'size' => $ui_factory->input()->field()->numeric(
                $lng->txt('file_size_bigger_than')
            ),
            'stakeholder' => $ui_factory->input()->field()->select(
                $lng->txt('stakeholders'),
                $stakeholders
            ),
//            'revisions' => $ui_factory->input()->field()->numeric(
//                $lng->txt('revisions_more_than'),
//            ),
        ];
    }

    private function getCountQuery(): string
    {
        return $this->getBaseQuery(true);
    }

    private function getDataQuery(): string
    {
        return $this->getBaseQuery(false) . " LIMIT " . $this->offset . ", " . $this->limit;
    }



    private function getBaseQuery(bool $count_only): string
    {
        // SELECT
        $q = $this->buildSelect($count_only);
        // FROM AND JOINS
        $q .= " FROM il_resource_revision
         JOIN il_resource_info ON il_resource_revision.rid = il_resource_info.rid AND
                                  il_resource_info.version_number = il_resource_revision.version_number
         JOIN il_resource_stkh_u ON il_resource_revision.rid = il_resource_stkh_u.rid";

        // WHERE
        $q .= $this->buildQueryFilter();

        if (!$count_only) {
            $q .= " GROUP BY il_resource_revision.rid ";
        }

//        $q .= $this->buildHaving();

        if (!$count_only) {
            $q .= $this->buildSortation();
        }

        return $q;
    }


    private function buildSortation(): string
    {
        return " ORDER BY " . ($this->getSortationsMapping()[$this->sort_direction]
                ?? $this->getSortationsMapping()[SortDirection::BY_TITLE_ASC]);
    }

    private function buildSelect(bool $count_only): string
    {
        if ($count_only) {
            return "SELECT COUNT(il_resource_revision.rid) AS cnt";
        }

        return "SELECT il_resource_revision.rid
        , MAX(il_resource_revision.version_number) AS max_revision
        , il_resource_revision.title               AS revision_title
        , il_resource_info.title                   AS file_title
        , il_resource_info.size AS file_size
        , il_resource_info.creation_date AS creation_date
        , il_resource_stkh_u.stakeholder_id AS stakeholder";
    }

    private function buildQueryFilter(): string
    {
        $filters = [];
        $filter_values = [];
        $query = '';
        if ($this->filter_values !== null) {
            if (isset($this->filter_values['title']) && $this->filter_values['title'] !== '') {
                $filter_values[] = $this->db->quote("%" . $this->filter_values['title'] . "%", 'text');
                $filters[] = " il_resource_revision.title LIKE %s ";
            }
            if (isset($this->filter_values['size']) && $this->filter_values['size'] !== '') {
                $greater_than = (int)$this->filter_values['size'] * BaseToComponent::SIZE_FACTOR * BaseToComponent::SIZE_FACTOR;
                $filter_values[] = $this->db->quote($greater_than, 'integer');
                $filters[] = " il_resource_info.size > %s ";
            }

            if (isset($this->filter_values['stakeholder']) && $this->filter_values['stakeholder'] !== '') {
                $filter_values[] = $this->db->quote("" . $this->filter_values['stakeholder'] . "", 'text');
                $filters[] = " il_resource_stkh_u.stakeholder_id = %s ";
            }
        }
        if ($filters !== []) {
            $query = " WHERE " . implode(" AND ", $filters);
            $query = vsprintf($query, $filter_values);
        }

        return $query;
    }
    private function buildHaving(): string
    {
        $filters = [];
        $filter_values = [];
        $query = '';
        if ($this->filter_values !== null) {
            if (isset($this->filter_values['revisions']) && $this->filter_values['revisions'] !== '') {
                $filter_values[] = $this->db->quote($this->filter_values['revisions'], 'integer');
                $filters[] = "MAX(il_resource_revision.version_number) > %s ";
            }
        }
        if ($filters !== []) {
            $query = " HAVING " . implode(" AND ", $filters);
            $query = vsprintf($query, $filter_values);
        }

        return $query;
    }
}
