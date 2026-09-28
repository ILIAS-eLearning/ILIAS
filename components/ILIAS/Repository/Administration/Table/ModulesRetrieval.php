<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed under the GPL-3.0,
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

namespace ILIAS\Repository\Administration\Table;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class ModulesRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected \ilObjectDefinition $object_definition,
        protected \ilSetting $settings,
        protected \ilComponentRepository $component_repository,
        protected \ilLanguage $lng,
        protected int $group_id
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->collectData();
        $data = $this->applyRange($data, $range);

        yield from $data;
    }

    public function count(array $filter, array $parameters): int
    {
        return count($this->collectData());
    }

    public function isFieldNumeric(string $field): bool
    {
        return false;
    }

    protected function collectData(): array
    {
        $group_positions = [0 => 9999];
        foreach (\ilObjRepositorySettings::getNewItemGroups() as $item) {
            $group_positions[(int) $item['id']] = (int) $item['pos'];
        }

        $object_types = [];
        foreach ($this->object_definition->getAllRepositoryTypes(false) as $id) {
            if (!$this->object_definition->isAllowedInRepository($id) ||
                $this->object_definition->isSystemObject($id) ||
                in_array($id, ['lng', 'rolt', 'sty', 'tax', 'usr', 'gdtr'], true)) {
                continue;
            }

            $object_types[$id] = [
                'caption' => $this->lng->txt('obj_' . $id),
                'subdir' => '',
                'default_pos' => $this->object_definition->getPositionByType($id)
            ];
        }

        $object_types = $this->getPluginComponents($object_types);
        $data = [];
        foreach ($object_types as $object_type => $item) {
            $position = $this->settings->get('obj_add_new_pos_' . $object_type);
            if (!(int) $position) {
                $position = $item['default_pos'];
            }
            if (strlen((string) $position) < 8) {
                $position = $group_positions[0] . str_pad((string) $position, 4, '0', STR_PAD_LEFT);
            }

            $position_group = (int) $this->settings->get(
                'obj_add_new_pos_grp_' . $object_type,
                '0'
            );
            if ($position_group !== $this->group_id) {
                continue;
            }

            $data[] = [
                'id' => $object_type,
                'caption' => $item['caption'],
                'subdir' => $item['subdir'],
                'pos' => (int) substr((string) $position, 4),
                'creation' => !(int) $this->settings->get(
                    'obj_dis_creation_' . $object_type,
                    '0'
                ),
                'sort_key' => (int) $position
            ];
        }

        usort(
            $data,
            static fn(array $left, array $right): int => $left['sort_key'] <=> $right['sort_key']
        );

        return $data;
    }

    protected function getPluginComponents(array $object_types): array
    {
        foreach ($this->component_repository->getPluginSlotById('robj')->getActivePlugins() as $plugin) {
            $object_types[$plugin->getId()] = [
                'caption' => \ilObjectPlugin::lookupTxtById($plugin->getId(), 'obj_' . $plugin->getId()),
                'subdir' => $this->lng->txt('cmps_plugin'),
                'default_pos' => 2000
            ];
        }

        foreach ($this->component_repository->getPluginSlotById('orguext')->getActivePlugins() as $plugin) {
            $object_types[$plugin->getId()] = [
                'caption' => \ilObjectPlugin::lookupTxtById($plugin->getId(), 'obj_' . $plugin->getId()),
                'subdir' => $this->lng->txt('cmps_plugin'),
                'default_pos' => 2000
            ];
        }

        return $object_types;
    }
}
