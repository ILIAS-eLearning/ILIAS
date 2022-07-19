<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiStatementsReportRequest
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiStatementsReportRequest extends ilCmiXapiAbstractRequest
{
    /**
     * @var ilCmiXapiStatementsReportLinkBuilder
     */
    protected $linkBuilder;
    
    /**
     * ilCmiXapiStatementsReportRequest constructor.
     * @param string $basicAuth
     * @param ilCmiXapiStatementsReportLinkBuilder $linkBuilder
     */
    public function __construct(string $basicAuth, ilCmiXapiStatementsReportLinkBuilder $linkBuilder)
    {
        parent::__construct($basicAuth);
        $this->linkBuilder = $linkBuilder;
    }
    
    /**
     * @return ilCmiXapiStatementsReport $report
     */
    public function queryReport($obj)
    {
        $reportResponse = $this->sendRequest($this->linkBuilder->getUrl());

        $report = new ilCmiXapiStatementsReport($reportResponse, $obj);

        return $report;
    }
}
