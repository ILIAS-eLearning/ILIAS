<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    
    /**
     * @return string
     */
    public function getUrl()
    {
        $url = $this->aggregateEndPoint;
        $url = $this->appendRequestParameters($url);
        return $url;
    }
    
    /**
     * @param string $link
     * @return string
     */
    protected function appendRequestParameters($url)
    {
        $url = ilUtil::appendUrlParameterString($url, $this->buildPipelineParameter());
        
        return $url;
    }
    
    /**
     * @return string
     */
    protected function buildPipelineParameter()
    {
        $pipeline = urlencode(json_encode($this->buildPipeline()));
        return "pipeline={$pipeline}";
    }
    
    /**
     * @return array
     */
    abstract protected function buildPipeline() : array;
    
    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }
    
    /**
     * @return string
     */
    public function getAggregateEndPoint()
    {
        return $this->aggregateEndPoint;
    }

    /**
     * @return ilObjCmiXapi
     */
    public function getObj()
    {
        return ilObjCmiXapi::getInstance($this->getObjId(),false);
    }
}
