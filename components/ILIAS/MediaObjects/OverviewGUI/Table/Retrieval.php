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

namespace ILIAS\MediaObjects\OverviewGUI\Table;

use Generator;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\RetrievalBase;
use ILIAS\MediaObjects\OverviewGUI\SubObjectRetrieval;
use ilObjMediaObject;
use ilObject;
use ILIAS\Data\Factory as DataFactory;
use DateTimeImmutable;
use ILIAS\MediaObjects\InternalDomainService;

class Retrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected array $internal_data;

    public function __construct(
        protected SubObjectRetrieval $sub_object_retrieval,
        protected InternalDomainService $domain,
        protected DataFactory $data_factory
    ) {
    }

    /**
     * get data only with internal usages and
     * properties of the media objects
     */
    protected function getInternalData(array $filter): array
    {
        $media_object_manager = $this->domain->mediaObject();
        $lom = $this->domain->learningObjectMetadata();

        if (isset($this->internal_data)) {
            return $this->internal_data;
        }
        $this->internal_data = [];
        foreach ($this->sub_object_retrieval->getPossibleTypes() as $type) {
            foreach ($this->sub_object_retrieval->getAllIDsForType($type) as $id) {
                $title = $this->sub_object_retrieval->getTitleOfSubObject($type, $id);
                $link = $this->sub_object_retrieval->getLinkToSubObject($type, $id);
                foreach (ilObjMediaObject::_getMobsOfObject($type, $id, 0) as $mob_id) {
                    /*
                     * type and id are only needed here internally to separate
                     * internal from external usages, see addExternalData
                     */
                    $this->internal_data[$mob_id]['internal_usages'][$type . ':' . $id] = [
                        'title' => $title,
                        'link' => $link
                    ];
                }
            }
        }
        foreach ($this->internal_data as $mob_id => $value) {
            if (!ilObjMediaObject::_exists($mob_id)) {
                unset($this->internal_data[$mob_id]);
                continue;
            }
            $title = ilObject::_lookupTitle($mob_id);
            $last_update = $media_object_manager->getLastChangeTimestamp($mob_id);

            $reader = $lom->read(0, $mob_id, 'mob', $lom->paths()->copyright());
            $preset_copyright = $lom->copyrightHelper()->readPresetCopyright($reader);

            if ($this->isDatumExcludedByFilter($title, $preset_copyright->identifier(), $last_update, $filter)) {
                unset($this->internal_data[$mob_id]);
                continue;
            }

            $this->internal_data[$mob_id]['id'] = $mob_id;
            $this->internal_data[$mob_id]['title'] = $title;
            $this->internal_data[$mob_id]['last_update'] = $last_update;
            $this->internal_data[$mob_id]['copyright_identifier'] = $preset_copyright->identifier();
            $this->internal_data[$mob_id]['copyright'] = $lom->copyrightHelper()->hasPresetCopyright($reader) ?
                $preset_copyright->presentAsString() :
                $lom->copyrightHelper()->readCustomCopyright($reader);
        }
        return $this->internal_data;
    }

    /**
     * add external usages to data,
     * requiring additional access checks
     */
    protected function addExternalData(array $internal_data): array
    {
        $access = $this->domain->access();
        $static_url = $this->domain->staticUrl();

        $data = $internal_data;
        foreach ($data as $key => $datum) {
            $mob_id = $datum['id'];
            $already_collected_obj_ids = [];
            foreach (ilObjMediaObject::lookupUsages($mob_id, false) as $usage) {
                /*
                 * filter out already collected usages, see getInternalData
                 */
                if (in_array(
                    $usage['type'] . ':' . $usage['id'],
                    array_keys($datum['internal_usages'])
                )) {
                    continue;
                }

                $parent_obj_id = ilObjMediaObject::getParentObjectIdForUsage($usage);
                if (!$parent_obj_id || in_array($parent_obj_id, $already_collected_obj_ids)) {
                    continue;
                }

                $parent_ref_id = null;
                $show_link = false;
                foreach (ilObject::_getAllReferences($parent_obj_id) as $ref_id) {
                    if (!$access->checkAccess('visible', '', $ref_id)) {
                        continue;
                    }
                    $parent_ref_id = $ref_id;
                    $show_link = $access->checkAccess('read', '', $parent_ref_id);
                    break;
                }
                if (!$parent_ref_id) {
                    continue;
                }

                $parent_title = ilObject::_lookupTitle($parent_obj_id);
                $parent_type = ilObject::_lookupType($parent_obj_id);
                $link_to_parent = '';
                if ($show_link) {
                    $link_to_parent = (string) $static_url->builder()->build(
                        $parent_type,
                        $this->data_factory->refId($parent_ref_id)
                    );
                }

                $already_collected_obj_ids[] = $parent_obj_id;
                if ($parent_type === 'mep') {
                    $data[$key]['mep_usages'][] = [
                        'title' => $parent_title,
                        'link' => $link_to_parent
                    ];
                    continue;
                }
                $data[$key]['external_usages'][] = [
                    'title' => $parent_title,
                    'link' => $link_to_parent
                ];
            }
        }
        return $data;
    }

    protected function isDatumExcludedByFilter(
        string $title,
        string $copyright_identifier,
        int $last_update,
        array $filter
    ): bool {
        if (
            ($filter['title'] ?? '') !== '' &&
            !str_contains(strtolower($title), strtolower($filter['title']))
        ) {
            return true;
        }

        if (
            ($filter['copyright'] ?? []) !== [] &&
            !in_array($copyright_identifier, $filter['copyright'])
        ) {
            return true;
        }

        $last_update = new DateTimeImmutable('@' . $last_update);
        if (
            (isset($filter['last_update'][0]) && $last_update < new DateTimeImmutable($filter['last_update'][0])) ||
            (isset($filter['last_update'][1]) && $last_update > new DateTimeImmutable($filter['last_update'][1]))
        ) {
            return true;
        }

        return false;
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): Generator {
        $data = $this->getInternalData($filter);

        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        /*
         * read out other usages after applying order and range,
         * to avoid unnecessary access checks
         */
        $data = $this->addExternalData($data);

        yield from $data;
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        return count($this->getInternalData($filter));
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === 'last_update';
    }
}
