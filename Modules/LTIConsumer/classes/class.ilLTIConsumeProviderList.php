<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    protected array $providers = array();
    
    const SCOPE_GLOBAL = 'global';
    const SCOPE_USER = 'user';
    const SCOPE_BOTH = 'both';
    
    /**
     * @var array
     */
    protected array $usagesUntrashed = array();
    
    /**
     * @var array
     */
    protected array $usagesTrashed = array();
    
    /**
     * @var array
     */
    protected array $idsFilter = array();
    
    /**
     * @var string
     */
    protected string $scopeFilter = self::SCOPE_BOTH;
    
    /**
     * @var string
     */
    protected string $availabilityFilter = '';
    
    /**
     * @var int
     */
    protected int $creatorFilter = 0;
    
    /**
     * @var string
     */
    protected string $titleFilter = '';
    
    /**
     * @var string
     */
    protected string $categoryFilter = '';
    
    /**
     * @var string
     */
    protected string $keywordFilter = '';
    
    /**
     * @var bool|null
     */
    protected ?bool $hasOutcomeFilter = null;
    
    /**
     * @var bool|null
     */
    protected ?bool $isExternalFilter = null;
    
    /**
     * @var bool|null
     */
    protected ?bool $isProviderKeyCustomizableFilter = null;
    
    /**
     * @return ilLTIConsumeProvider[]
     */
    public function getProviders() : array
    {
        return $this->providers;
    }
    
    /**
     * @param ilLTIConsumeProvider[] $providers
     */
    public function setProviders(array $providers) : void
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
    public function setIdsFilter(array $idsFilter) : void
    {
        $this->idsFilter = $idsFilter;
    }
    
    public function getAvailabilityFilter() : string
    {
        return $this->availabilityFilter;
    }
    
    public function setAvailabilityFilter(string $availabilityFilter) : void
    {
        $this->availabilityFilter = $availabilityFilter;
    }
    
    public function getScopeFilter() : string
    {
        return $this->scopeFilter;
    }
    
    public function setScopeFilter(string $scopeFilter) : void
    {
        $this->scopeFilter = $scopeFilter;
    }
    
    public function getCreatorFilter() : int
    {
        return $this->creatorFilter;
    }
    
    public function setCreatorFilter(int $creatorFilter) : void
    {
        $this->creatorFilter = $creatorFilter;
    }
    
    public function getTitleFilter() : string
    {
        return $this->titleFilter;
    }
    
    public function setTitleFilter(string $titleFilter) : void
    {
        $this->titleFilter = $titleFilter;
    }
    
    public function getCategoryFilter() : string
    {
        return $this->categoryFilter;
    }
    
    public function setCategoryFilter(string $categoryFilter) : void
    {
        $this->categoryFilter = $categoryFilter;
    }
    
    public function getKeywordFilter() : string
    {
        return $this->keywordFilter;
    }
    
    public function setKeywordFilter(string $keywordFilter) : void
    {
        $this->keywordFilter = $keywordFilter;
    }
    
    public function getHasOutcomeFilter() : ?bool
    {
        return $this->hasOutcomeFilter;
    }
    
    public function setHasOutcomeFilter(?bool $hasOutcomeFilter) : void
    {
        $this->hasOutcomeFilter = $hasOutcomeFilter;
    }
    
    public function getIsExternalFilter() : ?bool
    {
        return $this->isExternalFilter;
    }
    
    public function setIsExternalFilter(?bool $isExternalFilter) : void
    {
        $this->isExternalFilter = $isExternalFilter;
    }
    
    public function getIsProviderKeyCustomizableFilter() : ?bool
    {
        return $this->isProviderKeyCustomizableFilter;
    }
    
    public function setIsProviderKeyCustomizableFilter(?bool $isProviderKeyCustomizableFilter) : void
    {
        $this->isProviderKeyCustomizableFilter = $isProviderKeyCustomizableFilter;
    }

    public function add(ilLTIConsumeProvider $provider) : void
    {
        $this->providers[] = $provider;
    }

    /**
     * @throws ilException
     */
    public function getById(int $providerId) : ilLTIConsumeProvider
    {
        foreach ($this as $provider) {
            if ($provider->getId() != $providerId) {
                continue;
            }
            
            return $provider;
        }
        
        throw new ilException('provider does not exist in list! (id=' . $providerId . ')');
    }
    
    protected function getWhereExpression() : string
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

        
        if (count($conditions) === 0) {
            return '1 = 1';
        }
        
        return implode("\n\t\t\tAND ", $conditions);
    }
    
    protected function buildQuery() : string
    {
        return "
			SELECT *
			FROM lti_ext_provider
			WHERE {$this->getWhereExpression()}
		";
    }

    public function load() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $res = $DIC->database()->query($this->buildQuery());
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $provider = new ilLTIConsumeProvider();
            $provider->assignFromDbRow($row);
            $this->add($provider);
        }
    }
    
    public function loadUsages() : void
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
    
    public function hasUsages(int $providerId) : bool
    {
        return $this->hasUntrashedUsages($providerId) || $this->hasTrashedUsages($providerId);
    }
    
    public function hasUntrashedUsages(int $providerId) : bool
    {
        return isset($this->usagesUntrashed[$providerId]) && $this->usagesUntrashed[$providerId];
    }
    
    public function hasTrashedUsages(int $providerId) : bool
    {
        return isset($this->usagesTrashed[$providerId]) && $this->usagesTrashed[$providerId];
    }
    
    public function getTableData() : array
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

    /**
     * @return mixed[]
     */
    public function getTableDataUsedBy() : array
    {
        $tableData = [];
        $i = 0;
        foreach ($this->getTableData() as $tableRow) {
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

    /**
     * @return array<int|string, array<string, mixed>>
     */
    private function loadUsedBy(int $providerId) : array
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

    /**
     * @return false|ilLTIConsumeProvider|mixed
     */
    public function current()
    {
        return current($this->providers);
    }

    /**
     * @return false|ilLTIConsumeProvider|void
     */
    public function next()
    {
        return next($this->providers);
    }

    /**
     * @return bool|float|int|mixed|string|null
     */
    public function key()
    {
        return key($this->providers);
    }

    public function valid() : bool
    {
        return key($this->providers) !== null;
    }

    /**
     * @return false|ilLTIConsumeProvider|void
     */
    public function rewind()
    {
        return reset($this->providers);
    }
}
