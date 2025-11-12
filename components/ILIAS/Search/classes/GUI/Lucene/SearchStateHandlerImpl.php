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

namespace ILIAS\Search\GUI\Lucene;

use ilSearchFilterGUI;
use ilUserSearchCache;
use ILIAS\Data\URI;
use ILIAS\Search\GUI\AbstractSearchStateHandlerImpl;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\Refinery\Factory as Refinery;
use ilSearchSettings;
use ilLuceneQueryParser;
use ILIAS\MetaData\Services\ServicesInterface as LOMServices;

class SearchStateHandlerImpl extends AbstractSearchStateHandlerImpl
{
    public function __construct(
        protected ilSearchSettings $settings,
        protected LOMServices $lom_services,
        HTTP $http,
        Refinery $refinery
    ) {
        parent::__construct($http, $refinery);
    }

    public function fetchRequestedRemoteSearchTerm(): string
    {
        if ($this->http->wrapper()->post()->has('queryString')) {
            $term = $this->http->wrapper()->post()->retrieve(
                'queryString',
                $this->refinery->kindlyTo()->string()
            );
            $qp = new ilLuceneQueryParser($term);
            $qp->parseAutoWildcard();
            return $qp->getQuery();
        }
        return '';
    }

    public function fetchFilter(URI $action): ilSearchFilterGUI
    {
        return new ilSearchFilterGUI($action, true);
    }

    public function fetchCache(int $usr_id): ilUserSearchCache
    {
        $cache = ilUserSearchCache::_getInstance($usr_id);
        $cache->switchSearchType(ilUserSearchCache::LUCENE_DEFAULT);
        return $cache;
    }

    public function loadFilterToCache(ilSearchFilterGUI $filter, ilUserSearchCache $cache): void
    {
        $search_filter_data = $filter->getData();

        $cache->setRoot((int) ($search_filter_data['search_scope'] ?? ROOT_FOLDER_ID));

        $creation_filter = [];
        if (
            $this->settings->isDateFilterEnabled() &&
            isset($search_filter_data['search_date'])
        ) {
            $creation_filter['date_start'] = $search_filter_data['search_date'][0];
            $creation_filter['date_end'] = $search_filter_data['search_date'][1];
        }
        $cache->setCreationFilter($creation_filter);

        $types_from_filter = (array) ($search_filter_data['search_type'] ?? []);

        $enabled_types = [];
        foreach ($this->settings->getEnabledLuceneItemFilterDefinitions() as $type => $data) {
            if (in_array($type, $types_from_filter)) {
                $enabled_types[$type] = 1;
            }
        }
        $cache->setItemFilter($enabled_types);

        $enabled_mime_types = [];
        foreach ($this->settings->getEnabledLuceneMimeFilterDefinitions() as $mime_type => $data) {
            if (in_array($mime_type, $types_from_filter)) {
                $enabled_mime_types[$mime_type] = 1;
            }
        }
        $cache->setMimeFilter($enabled_mime_types);

        $copyright_filter = [];
        if (
            $this->lom_services->copyrightHelper()->isCopyrightSelectionActive() &&
            isset($search_filter_data['search_copyright'])
        ) {
            $copyright_filter = $search_filter_data['search_copyright'];
        }
        $cache->setCopyrightFilter(...$copyright_filter);
    }
}
