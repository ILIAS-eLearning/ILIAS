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
 * Class ilCmiXapiReportLinkBuilder
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilXapiCompliantStatementsReportLinkBuilder
{
    protected ilObjCmiXapi $object;
    
    protected ilCmiXapiLrsType $lrsType;

    protected ilCmiXapiStatementsReportFilter $filter;
    
    public function __construct(ilObjCmiXapi $object, ilCmiXapiStatementsReportFilter $filter)
    {
        $this->object = $object;
        $this->lrsType = $object->getLrsType();
        $this->filter = $filter;
    }
    
    public function getUrl() : string
    {
        $link = $this->lrsType->getLrsEndpointStatementsLink();
        return $this->appendRequestParameters($link);
    }
    
    protected function appendRequestParameters(string $link) : string
    {
        if ($this->filter->getLimit() !== 0) {
            $link = ilUtil::appendUrlParameterString($link, $this->buildParamLimit());
        }
        
        if ($this->filter->getActor() !== null) {
            $link = ilUtil::appendUrlParameterString($link, $this->buildParamAgent());
        }
        
        if ($this->filter->getVerb()) {
            $link = ilUtil::appendUrlParameterString($link, $this->buildParamVerb());
        }
        
        if ($this->filter->getStartDate() !== null) {
            $link = ilUtil::appendUrlParameterString($link, $this->buildParamSince());
        }
        
        if ($this->filter->getEndDate() !== null) {
            $link = ilUtil::appendUrlParameterString($link, $this->buildParamUntil());
        }
        
        $link = ilUtil::appendUrlParameterString($link, $this->buildParamRelatedAgents());
        $link = ilUtil::appendUrlParameterString($link, $this->buildParamRelatedActivities());
        
        return ilUtil::appendUrlParameterString($link, $this->buildParamActivity());
    }

    protected function buildParamAgent() : string
    {
        $agent = json_encode([
            'objectType' => 'Agent',
            'mbox' => 'mailto:' . $this->filter->getActor()->getUsrIdent()
        ]);
        
        return "agent={$agent}";
    }

    protected function buildParamVerb() : string
    {
        $verb = urlencode($this->filter->getVerb());
        return "verb={$verb}";
    }

    protected function buildParamSince() : string
    {
        $since = urlencode($this->filter->getStartDate()->toXapiTimestamp());
        return "since={$since}";
    }
    
    protected function buildParamUntil() : string
    {
        $until = urlencode($this->filter->getEndDate()->toXapiTimestamp());
        return "until={$until}";
    }
    
    protected function buildParamActivity() : string
    {
        return "activity={$this->object->getActivityId()}";
    }
    
    protected function buildParamRelatedAgents() : string
    {
        return "related_agents=false";
    }
    
    protected function buildParamRelatedActivities() : string
    {
        return "related_activities=false";
    }
    
    protected function buildParamLimit() : string
    {
        return "limit={$this->filter->getLimit()}";
    }
}
