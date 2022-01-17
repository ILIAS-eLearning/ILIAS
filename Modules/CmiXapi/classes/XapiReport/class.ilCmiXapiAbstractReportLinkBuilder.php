<?php

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
 * Class ilCmiXapiAbstractReportLinkBuilder
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
abstract class ilCmiXapiAbstractReportLinkBuilder
{
    /**
     * @var int
     */
    protected $objId;
    
    /**
     * @var string
     */
    protected $aggregateEndPoint;

    /**
     * @var ilCmiXapiStatementsReportFilter
     */
    protected $filter;
    
    /**
     * ilCmiXapiAbstractReportLinkBuilder constructor.
     * @param $objId
     * @param $userIdentMode
     * @param $aggregateEndPoint
     * @param ilCmiXapiStatementsReportFilter $filter
     */
    public function __construct(
        $objId,
        $aggregateEndPoint,
        ilCmiXapiStatementsReportFilter $filter
    ) {
        $this->objId = $objId;
        $this->aggregateEndPoint = $aggregateEndPoint;
        $this->filter = $filter;
    }
    
    public function getUrl(): string
    {
        $url = $this->aggregateEndPoint;
        $url = $this->appendRequestParameters($url);
        return $url;
    }
    
    /**
     * @param string $link
     */
    protected function appendRequestParameters($url): string
    {
        $url = ilUtil::appendUrlParameterString($url, $this->buildPipelineParameter());
        
        return $url;
    }
    
    protected function buildPipelineParameter(): string
    {
        $pipeline = urlencode(json_encode($this->buildPipeline()));
        return "pipeline={$pipeline}";
    }
    
    /**
     * @return array
     */
    abstract protected function buildPipeline() : array;
    
    public function getObjId(): int
    {
        return $this->objId;
    }
    
    public function getAggregateEndPoint(): string
    {
        return $this->aggregateEndPoint;
    }

    public function getObj(): \ilObjCmiXapi
    {
        return ilObjCmiXapi::getInstance($this->getObjId(),false);
    }
}
