<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiHighscoreReportRequest
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiHighscoreReportRequest extends ilCmiXapiAbstractRequest
{
    /**
     * @var ilCmiXapiLrsType
     */
    protected $lrsType;
    
    /**
     * @var ilCmiXapiStatementsReportLinkBuilder
     */
    protected $linkBuilder;
    
    /**
     * ilCmiXapiHighscoreReportRequest constructor.
     * @param string $basicAuth
     * @param ilCmiXapiHighscoreReportLinkBuilder $linkBuilder
     */
    public function __construct(string $basicAuth, ilCmiXapiHighscoreReportLinkBuilder $linkBuilder)
    {
        parent::__construct($basicAuth);
        $this->linkBuilder = $linkBuilder;
    }
    
    /**
     * @return ilCmiXapiHighscoreReport
     */
    public function queryReport($obj)
    {
        $reportResponse = $this->sendRequest($this->linkBuilder->getUrl());
        
        $report = new ilCmiXapiHighscoreReport($reportResponse, $obj);
        
        return $report;
    }
}
