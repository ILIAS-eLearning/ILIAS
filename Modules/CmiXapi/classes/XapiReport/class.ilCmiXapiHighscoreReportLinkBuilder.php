<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiHighscoreReportLinkBuilder
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiHighscoreReportLinkBuilder extends ilCmiXapiAbstractReportLinkBuilder
{
    /**
     * @return array
     */
    protected function buildPipeline() : array
    {
        $pipeline = [];
        
        $pipeline[] = $this->buildFilterStage();
        $pipeline[] = $this->buildOrderStage();

        
        $obj = $this->getObj();
        $id = null;
        if ($obj->getContentType() == ilObjCmiXapi::CONT_TYPE_GENERIC)
        {
            $id = '$statement.actor.mbox';
        }
        if ($obj->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5 && !$obj->isMixedContentType())
        {
            $id = '$statement.actor.account.name';
        }
        $pipeline[] = ['$group' => [
            '_id' => $id,
            'mbox' => [ '$last' => '$statement.actor.mbox' ],
            'account' => [ '$last' => '$statement.actor.account.name'],
            'username' => [ '$last' => '$statement.actor.name' ],
            'timestamp' => [ '$last' => '$statement.timestamp' ],
            'duration' => [ '$push' => '$statement.result.duration' ],
            'score' => [ '$last' => '$statement.result.score' ]
        ]];
        return $pipeline;
    }
    
    protected function buildFilterStage()
    {
        $stage = array();
        
        $stage['statement.object.objectType'] = 'Activity';
        $stage['statement.actor.objectType'] = 'Agent';

        $stage['statement.object.id'] = $this->filter->getActivityId();
        
        $stage['statement.result.score.scaled'] = [
            '$exists' => 1
        ];
        
        $obj = $this->getObj();
        if (($obj->getContentType() == ilObjCmiXapi::CONT_TYPE_GENERIC) || $obj->isMixedContentType())
        {
            $stage['$or'] = $this->getUsersStack();
        }
        
        return [
            '$match' => $stage
        ];
    }
    
    protected function buildOrderStage()
    {
        return [ '$sort' => [
            'statement.timestamp' => 1
        ]];
    }
    
    // not used in cmi5 see above
    protected function getUsersStack()
    {
        $users = [];
        $obj = $this->getObj();
        if ($obj->isMixedContentType())
        {
            foreach (ilCmiXapiUser::getUsersForObject($this->getObjId()) as $cmixUser) 
            {
                $users[] = ['statement.actor.mbox' => "mailto:{$cmixUser->getUsrIdent()}"];
                $users[] = ['statement.actor.account.name' => "{$cmixUser->getUsrIdent()}"];
            }
        }
        else
        {
            foreach (ilCmiXapiUser::getUsersForObject($this->getObjId()) as $cmixUser) 
            {
                $users[] = [
                    'statement.actor.mbox' => "mailto:{$cmixUser->getUsrIdent()}"
                ];
            }
        }
        return $users;
    }
    
    public function getPipelineDebug()
    {
        return '<pre>' . json_encode($this->buildPipeline(), JSON_PRETTY_PRINT) . '</pre>';
    }
}
