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

namespace ILIAS\Container\Content\Filter;

use ILIAS\Container\Content\DomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class FilterManager
{
    protected \ILIAS\Container\Content\RepoService $repo_service;
    protected DomainService $domain_service;
    protected bool $results_on_filter_only;
    protected ?\ilContainerUserFilter $container_user_filter = null;
    protected array $objects;

    /**
     * @param array[] $objects each array must contain the keys "obj_id" and "type"
     */
    public function __construct(
        DomainService $domain_service,
        \ILIAS\Container\Content\RepoService $repo_service,
        array $objects,
        ?\ilContainerUserFilter $container_user_filter,
        bool $results_on_filter_only = false
    ) {
        $this->domain_service = $domain_service;
        $this->repo_service = $repo_service;
        $this->objects = $objects;
        $this->container_user_filter = $container_user_filter;
        $this->results_on_filter_only = $results_on_filter_only;
    }

    /**
     * Apply container user filter on objects
     * @throws \ilException
     */
    public function apply(): array
    {
        $container_user_filter = $this->container_user_filter;
        $obj_repo = $this->repo_service->filter()->object();
        $member_repo = $this->repo_service->filter()->member();
        $metadata_repo = $this->repo_service->filter()->metadata();

        if (is_null($container_user_filter)) {
            return $this->objects;
        }

        if ($container_user_filter->isEmpty() && $this->results_on_filter_only) {
            return [];
        }

        $result = null;

        $obj_ids = array_map(function ($i) {
            return $i["obj_id"];
        }, $this->objects);
        $filter_data = $container_user_filter->getData();
        if (is_array($filter_data)) {
            foreach ($filter_data as $key => $val) {
                if (count($obj_ids) === 0) {    // stop if no object ids are left
                    continue;
                }
                if (!in_array(substr($key, 0, 4), ["adv_", "std_"])) {
                    continue;
                }
                if ($val == "") {
                    continue;
                }
                $field_id = substr($key, 4);
                $val = \ilUtil::stripSlashes($val);
                $query_parser = new \ilQueryParser($val);
                if (strpos($key, "std_") === 0) {
                    switch ($field_id) {
                        case \ilContainerFilterField::STD_FIELD_OBJECT_TYPE:
                            $result = null;
                            $obj_ids = $obj_repo->filterObjIdsByType($obj_ids, $val);
                            break;

                        case \ilContainerFilterField::STD_FIELD_ONLINE:
                            if (in_array($val, ["1", "2"], true)) {
                                if ($val === "1") {
                                    $obj_ids = $obj_repo->filterObjIdsByOnline($obj_ids);
                                } else {
                                    $obj_ids = $obj_repo->filterObjIdsByOffline($obj_ids);
                                }
                                $obj_ids = $this->legacyOnlineFilter($obj_ids, $this->objects, $val);
                            }
                            break;

                        case \ilContainerFilterField::STD_FIELD_TUTORIAL_SUPPORT:
                            $result = null;
                            $obj_ids = $member_repo->filterObjIdsByTutorialSupport($obj_ids, $val);
                            break;

                        case \ilContainerFilterField::STD_FIELD_COPYRIGHT:
                            $result = null;
                            $obj_ids = $metadata_repo->filterObjIdsByCopyright($obj_ids, $val);
                            break;

                        default:
                            $query_parser->setCombination(\ilQueryParser::QP_COMBINATION_OR);
                            $query_parser->parse();
                            $meta_search = \ilObjectSearchFactory::_getAdvancedSearchInstance($query_parser);
                            switch ($field_id) {
                                case \ilContainerFilterField::STD_FIELD_TITLE_DESCRIPTION:
                                    $meta_search->setMode('title_description');
                                    break;
                                case \ilContainerFilterField::STD_FIELD_DESCRIPTION:
                                    $meta_search->setMode('description');
                                    break;
                                case \ilContainerFilterField::STD_FIELD_TITLE:
                                    $meta_search->setMode('title');
                                    break;
                                case \ilContainerFilterField::STD_FIELD_KEYWORD:
                                    $meta_search->setMode('keyword_all');
                                    break;
                                case \ilContainerFilterField::STD_FIELD_AUTHOR:
                                    $meta_search->setMode('contribute');
                                    break;
                            }
                            $result = $meta_search->performSearch();
                            break;
                    }
                } else {

                    // advanced metadata search
                    $field = \ilAdvancedMDFieldDefinition::getInstance((int) $field_id);

                    $field_form = \ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance(
                        $field->getADTDefinition(),
                        true,
                        false
                    );
                    $field_form->setElementId("query[" . $key . "]");
                    $field_form->validate();

                    /**
                     * Workaround:
                     * Only text fields take care of $parser_value being passed through
                     * new ilQueryParser($parser_value), thus other fields pass values by setting
                     * directly in the ADT objects. This could go to a new bridge.
                     */
                    if ($field instanceof \ilAdvancedMDFieldDefinitionSelectMulti) {
                        $field_form->getADT()->setSelections([$val]);
                    }
                    if ($field instanceof \ilAdvancedMDFieldDefinitionSelect) {
                        $adt = $field_form->getADT();
                        if ($adt instanceof \ilADTMultiEnumText) {
                            $field_form->getADT()->setSelections([$val]);
                        } else {
                            $field_form->getADT()->setSelection($val);
                        }
                    }

                    $adv_md_search = \ilObjectSearchFactory::_getAdvancedMDSearchInstance($query_parser);
                    //$adv_md_search->setFilter($this->filter);	// this could be set to an array of object types
                    $adv_md_search->setDefinition($field);            // e.g. ilAdvancedMDFieldDefinitionSelectMulti
                    $adv_md_search->setIdFilter(array(0));
                    $adv_md_search->setSearchElement($field_form);    // e.g. ilADTEnumSearchBridgeMulti
                    $result = $adv_md_search->performSearch();
                }

                // intersect results
                if ($result instanceof \ilSearchResult) {
                    $result_obj_ids = array_map(
                        function ($i) {
                            return $i["obj_id"];
                        },
                        $result->getEntries()
                    );
                    $obj_ids = array_intersect($obj_ids, $result_obj_ids);
                }
            }
        }
        $objects = array_filter($this->objects, function ($o) use ($obj_ids) {
            return in_array($o["obj_id"], $obj_ids);
        });

        return $objects;
    }

    /**
     * Legacy online filter
     *
     * This can be removed, once all objects use the central online/offline property
     * @param int[] $obj_ids
     * @param array $objects
     * @param int   $val
     * @return int[]
     */
    protected function legacyOnlineFilter(
        array $obj_ids,
        array $objects,
        int $val
    ): array {
        $legacy_types = ["glo", "wiki", "qpl", "book", "dcl", "prtt"];
        foreach ($legacy_types as $type) {
            $lobjects = array_filter($objects, static function ($o) use ($type) {
                return ($o["type"] === $type);
            });
            $lobj_ids = array_map(static function ($i) {
                return $i["obj_id"];
            }, $lobjects);
            $status = [];
            switch ($type) {
                case "glo":
                    $status = \ilObjGlossaryAccess::_lookupOnlineStatus($lobj_ids);
                    break;
                case "wiki":
                    $status = \ilObjWikiAccess::_lookupOnlineStatus($lobj_ids);
                    break;
                case "book":
                    $status = \ilObjBookingPoolAccess::_lookupOnlineStatus($lobj_ids);
                    break;
                case "qpl":
                    foreach ($lobj_ids as $lid) {
                        $status[$lid] = \ilObjQuestionPoolAccess::isOnline($lid);
                    }
                    break;
                case "dcl":
                    foreach ($lobj_ids as $lid) {
                        $status[$lid] = \ilObjDataCollectionAccess::_lookupOnline($lid);
                    }
                    break;
                case "prtt":
                    $status = \ilObjPortfolioTemplateAccess::_lookupOnlineStatus($lobj_ids);
                    break;
            }
            foreach ($status as $obj_id => $online) {
                if (($val == 1 && !$online) || ($val == 2 && $online)) {
                    if (($key = array_search($obj_id, $obj_ids)) !== false) {
                        unset($obj_ids[$key]);
                    }
                } elseif (!in_array($obj_id, $obj_ids)) {
                    $obj_ids[] = $obj_id;
                }
            }
        }
        return $obj_ids;
    }
}
