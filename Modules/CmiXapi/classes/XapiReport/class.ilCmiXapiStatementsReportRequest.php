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
    public function queryReport($objId): \ilCmiXapiStatementsReport
    {
        $reportResponse = $this->sendRequest($this->linkBuilder->getUrl());
        
        $report = new ilCmiXapiStatementsReport($reportResponse, $objId);
        
        return $report;
    }
}
