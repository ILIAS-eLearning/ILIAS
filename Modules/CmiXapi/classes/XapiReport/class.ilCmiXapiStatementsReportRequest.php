<?php declare(strict_types=1);

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
    protected ilCmiXapiStatementsReportLinkBuilder $linkBuilder;
    
    /**
     * ilCmiXapiStatementsReportRequest constructor.
     */
    public function __construct(string $basicAuth, ilCmiXapiStatementsReportLinkBuilder $linkBuilder)
    {
        parent::__construct($basicAuth);
        $this->linkBuilder = $linkBuilder;
    }
    
    public function queryReport(int $objId) : \ilCmiXapiStatementsReport
    {
        $reportResponse = $this->sendRequest($this->linkBuilder->getUrl());
        
        return new ilCmiXapiStatementsReport($reportResponse, $objId);
    }
}
