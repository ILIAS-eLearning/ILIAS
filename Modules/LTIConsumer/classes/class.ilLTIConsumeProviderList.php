<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLTIConsumeProviderList
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumeProviderList implements Iterator
{
    /**
     * @var ilLTIConsumeProvider[]
     */
    protected $providers = array();
    
    const SCOPE_GLOBAL = 'global';
    const SCOPE_USER = 'user';
    const SCOPE_BOTH = 'both';
    
    /**
     * @var array
     */
    protected $usagesUntrashed = array();
    
    /**
     * @var array
     */
    protected $usagesTrashed = array();
    
    /**
     * @var array
     */
    protected $idsFilter = array();
    
    /**
     * @var string
     */
    protected $scopeFilter = self::SCOPE_BOTH;
    
    /**
     * @var string
     */
    protected $availabilityFilter = '';
    
    /**
     * @var int
     */
    protected $creatorFilter = 0;
    
    /**
     * @var string
     */
    protected $titleFilter = '';
    
    /**
     * @var string
     */
    protected $categoryFilter = '';
    
    /**
     * @var string
     */
    protected $keywordFilter = '';
    
    /**
     * @var bool|null
     */
    protected $hasOutcomeFilter = null;
    
    /**
     * @var bool|null
     */
    protected $isExternalFilter = null;
    
    /**
     * @var bool|null
     */
    protected $isProviderKeyCustomizableFilter = null;
    
    /**
     * @return ilLTIConsumeProvider[]
     */
    public function getProviders()
    {
        return $this->providers;
    }
    
    /**
     * @param ilLTIConsumeProvider[] $providers
     */
    public function setProviders(array $providers)
    {
        $this->providers = $providers;
    }
    
    /**
     * @return int[]
     */
    public function getIdsFilter() : array
    {
        return $this->idsFilter;
    }
    
    /**
     * @param int[] $idsFilter
     */
    public function setIdsFilter(array $idsFilter)
    {
        $this->idsFilter = $idsFilter;
    }
    
    /**
     * @return string
     */
    public function getAvailabilityFilter() : string
    {
        return $this->availabilityFilter;
    }
    
    /**
     * @param string $availabilityFilter
     */
    public function setAvailabilityFilter(string $availabilityFilter)
    {
        $this->availabilityFilter = $availabilityFilter;
    }
    
    /**
     * @return string
     */
    public function getScopeFilter()
    {
        return $this->scopeFilter;
    }
    
    /**
     * @param string $scopeFilter
     */
    public function setScopeFilter($scopeFilter)
    {
        $this->scopeFilter = $scopeFilter;
    }
    
    /**
     * @return int
     */
    public function getCreatorFilter()
    {
        return $this->creatorFilter;
    }
    
    /**
     * @param int $creatorFilter
     */
    public function setCreatorFilter($creatorFilter)
    {
        $this->creatorFilter = $creatorFilter;
    }
    
    /**
     * @return string
     */
    public function getTitleFilter() : string
    {
        return $this->titleFilter;
    }
    
    /**
     * @param string $titleFilter
     */
    public function setTitleFilter(string $titleFilter)
    {
        $this->titleFilter = $titleFilter;
    }
    
    /**
     * @return string
     */
    public function getCategoryFilter() : string
    {
        return $this->categoryFilter;
    }
    
    /**
     * @param string $categoryFilter
     */
    public function setCategoryFilter(string $categoryFilter)
    {
        $this->categoryFilter = $categoryFilter;
    }
    
    /**
     * @return string
     */
    public function getKeywordFilter() : string
    {
        return $this->keywordFilter;
    }
    
    /**
     * @param string $keywordFilter
     */
    public function setKeywordFilter(string $keywordFilter)
    {
        $this->keywordFilter = $keywordFilter;
    }
    
    /**
     * @return bool|null
     */
    public function getHasOutcomeFilter()
    {
        return $this->hasOutcomeFilter;
    }
    
    /**
     * @param bool|null $hasOutcomeFilter
     */
    public function setHasOutcomeFilter($hasOutcomeFilter)
    {
        $this->hasOutcomeFilter = $hasOutcomeFilter;
    }
    
    /**
     * @return bool|null
     */
    public function getIsExternalFilter()
    {
        return $this->isExternalFilter;
    }
    
    /**
     * @param bool|null $isExternalFilter
     */
    public function setIsExternalFilter($isExternalFilter)
    {
        $this->isExternalFilter = $isExternalFilter;
    }
    
    /**
     * @return bool|null
     */
    public function getIsProviderKeyCustomizableFilter()
    {
        return $this->isProviderKeyCustomizableFilter;
    }
    
    /**
     * @param bool|null $isProviderKeyCustomizableFilter
     */
    public function setIsProviderKeyCustomizableFilter($isProviderKeyCustomizableFilter)
    {
        $this->isProviderKeyCustomizableFilter = $isProviderKeyCustomizableFilter;
    }

    /**
     * @param ilLTIConsumeProvider $provider
     */
    public function add(ilLTIConsumeProvider $provider)
    {
        $this->providers[] = $provider;
    }
    
    /**
     * @param int $providerId
     * @return ilLTIConsumeProvider
     */
    public function getById(int $providerId) : ilLTIConsumeProvider
    {
        foreach ($this as $provider) {
            if ($provider->getId() != $providerId) {
                continue;
            }
            
            return $provider;
        }
        
        throw new ilLtiConsumerException('provider does not exist in list! (id=' . $providerId . ')');
    }
    
    protected function getWhereExpression()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $conditions = [];
        
        if ($this->getIdsFilter()) {
            $conditions[] = $DIC->database()->in('id', $this->getIdsFilter(), false, 'integer');
        }
        
        if (strlen($this->getAvailabilityFilter())) {
            switch ($this->getAvailabilityFilter()) {
                case ilLTIConsumeProvider::AVAILABILITY_CREATE:
                case ilLTIConsumeProvider::AVAILABILITY_EXISTING:
                case ilLTIConsumeProvider::AVAILABILITY_NONE:
                    $conditions[] = "availability = " . $DIC->database()->quote(
                        $this->getAvailabilityFilter(),
                        'integer'
                    );
            }
        }
        
        switch ($this->getScopeFilter()) {
            case self::SCOPE_GLOBAL:
                $conditions[] = "global = " . $DIC->database()->quote(1, 'integer');
                break;
            case self::SCOPE_USER:
                $conditions[] = "global = " . $DIC->database()->quote(0, 'integer');
                break;
            case self::SCOPE_BOTH:
            default:
        }
        
        if ($this->getCreatorFilter()) {
            $conditions[] = "creator = " . $DIC->database()->quote($this->getCreatorFilter(), 'integer');
        }
        
        if ($this->getTitleFilter()) {
            $conditions[] = $DIC->database()->like('title', 'text', "%{$this->getTitleFilter()}%");
        }
        
        if ($this->getCategoryFilter()) {
            $conditions[] = "category = " . $DIC->database()->quote($this->getCategoryFilter(), 'text');
        }
        
        if ($this->getKeywordFilter()) {
            $conditions[] = $DIC->database()->like('keywords', 'text', "%{$this->getKeywordFilter()}%");
        }
        
        if ($this->getHasOutcomeFilter() !== null) {
            $conditions[] = "has_outcome = " . $DIC->database()->quote((int) $this->getHasOutcomeFilter(), 'integer');
        }
        
        if ($this->getIsExternalFilter() !== null) {
            $conditions[] = "external_provider = " . $DIC->database()->quote((int) $this->getIsExternalFilter(), 'integer');
        }
        
        if ($this->getIsProviderKeyCustomizableFilter() !== null) {
            $conditions[] = "provider_key_customizable = " . $DIC->database()->quote((int) $this->getIsProviderKeyCustomizableFilter(), 'integer');
        }

        
        if (!count($conditions)) {
            return '1 = 1';
        }
        
        return implode("\n\t\t\tAND ", $conditions);
    }
    
    protected function buildQuery()
    {
        $query = "
			SELECT *
			FROM lti_ext_provider
			WHERE {$this->getWhereExpression()}
		";
        
        return $query;
    }

    public function load()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $res = $DIC->database()->query($this->buildQuery());
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $provider = new ilLTIConsumeProvider();
            $provider->assignFromDbRow($row);
            $this->add($provider);
        }
    }
    
    public function loadUsages()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $res = $DIC->database()->query("
			SELECT 'untrashed' query, oset.provider_id, COUNT(oset.obj_id) cnt
			FROM lti_consumer_settings oset
			INNER JOIN object_reference oref
			ON oref.obj_id = oset.obj_id
			AND oref.deleted IS NULL
			GROUP BY oset.provider_id
			
			UNION
			
			SELECT 'trashed' query, oset.provider_id, COUNT(oset.obj_id) cnt
			FROM lti_consumer_settings oset
			INNER JOIN object_reference oref
			ON oref.obj_id = oset.obj_id
			AND oref.deleted IS NOT NULL
			GROUP BY oset.provider_id
		");

        while ($row = $DIC->database()->fetchAssoc($res)) {
            if ($row['query'] == 'untrashed') {
                $this->usagesUntrashed[ $row['provider_id'] ] = (int) $row['cnt'];
            } elseif ($row['query'] == 'trashed') {
                $this->usagesTrashed[ $row['provider_id'] ] = (int) $row['cnt'];
            }
        }
    }
    
    /**
     * @param int $providerId
     * @return bool
     */
    public function hasUsages(int $providerId) : bool
    {
        return $this->hasUntrashedUsages($providerId) || $this->hasTrashedUsages($providerId);
    }
    
    /**
     * @param int $providerId
     * @return bool
     */
    public function hasUntrashedUsages(int $providerId) : bool
    {
        return isset($this->usagesUntrashed[$providerId]) && $this->usagesUntrashed[$providerId];
    }
    
    /**
     * @param int $providerId
     * @return bool
     */
    public function hasTrashedUsages(int $providerId) : bool
    {
        return isset($this->usagesTrashed[$providerId]) && $this->usagesTrashed[$providerId];
    }
    
    /**
     * @return array
     */
    public function getTableData()
    {
        $this->loadUsages();
        
        $tableData = array();
        
        foreach ($this as $provider) {
            $tblRow = array();
            
            $tblRow['id'] = $provider->getId();
            $tblRow['title'] = $provider->getTitle();
            $tblRow['description'] = $provider->getDescription();
            $tblRow['category'] = $provider->getCategory();
            $tblRow['keywords'] = $provider->getKeywordsArray();
            $tblRow['outcome'] = $provider->getHasOutcome();
            $tblRow['external'] = $provider->isExternalProvider();
            $tblRow['provider_key_customizable'] = $provider->isProviderKeyCustomizable();
            $tblRow['availability'] = $provider->getAvailability();
            $tblRow['creator'] = $provider->getCreator();
            $tblRow['accepted_by'] = $provider->getAcceptedBy();
            
            if ($provider->getProviderIcon()->exists()) {
                $tblRow['icon'] = $provider->getProviderIcon()->getAbsoluteFilePath();
            }
            
            $tblRow['usages_untrashed'] = 0;
            if (isset($this->usagesUntrashed[$provider->getId()])) {
                $tblRow['usages_untrashed'] = $this->usagesUntrashed[$provider->getId()];
            }
            
            $tblRow['usages_trashed'] = 0;
            if (isset($this->usagesTrashed[$provider->getId()])) {
                $tblRow['usages_trashed'] = $this->usagesTrashed[$provider->getId()];
            }
            
            $tableData[] = $tblRow;
        }
        
        return $tableData;
    }

    public function getTableDataUsedBy()
    {
        $tableData = [];
        $i = 0;
        foreach ($this->getTableData() as $key => $tableRow) {
            if (!(bool) $tableRow['usages_trashed'] && !(bool) $tableRow['usages_untrashed']) {
                continue;
            }
            foreach ($this->loadUsedBy($tableRow['id']) as $usedByObjId => $usedByData) {
                $tableData[$i] = $tableRow;
                $tableData[$i]['usedByObjId'] = $usedByObjId;
                $tableData[$i]['usedByRefId'] = $usedByData['ref_id'];
                $tableData[$i]['usedByTitle'] = $usedByData['title'];
                $tableData[$i]['usedByIsTrashed'] = $usedByData['trashed'];
                $i++;
            } // EOF foreach( $this->loadUsedBy($tableRow['id'])
        } // EOF foreach($this->getTableData()
        return $tableData;
    }

    private function loadUsedBy($providerId)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $retArr = [];
        $pId = $DIC->database()->quote($providerId, 'integer');
        $res = $DIC->database()->query(
            "SELECT oset.obj_id AS obj_id, oref.ref_id AS ref_id, oref.deleted as trashed, odata.title AS title" .
            " FROM lti_consumer_settings oset, object_reference oref, object_data odata" .
            " WHERE oset.provider_id = " . $pId .
            " AND oref.obj_id = oset.obj_id" .
            " AND odata.obj_id = oset.obj_id"
        );
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $retArr[$row['obj_id']] = [
                'ref_id' => $row['ref_id'],
                'title' => $row['title'],
                'trashed' => null !== $row['trashed'] ? true : false
            ];
        }
        return $retArr;
    }

    public function current()
    {
        return current($this->providers);
    }
    public function next()
    {
        return next($this->providers);
    }
    public function key()
    {
        return key($this->providers);
    }
    public function valid()
    {
        return key($this->providers) !== null;
    }
    public function rewind()
    {
        return reset($this->providers);
    }
}
