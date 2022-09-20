<?php

declare(strict_types=1);

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
     * @return array<int, array<mixed[]>>
     */
    protected function buildPipeline(): array
    {
        $pipeline = [];

        $pipeline[] = $this->buildFilterStage();
        $pipeline[] = $this->buildOrderStage();


        $obj = $this->getObj();
        $id = null;
        if ($obj->getContentType() == ilObjCmiXapi::CONT_TYPE_GENERIC) {
            $id = '$statement.actor.mbox';
        }
        if ($obj->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5 && !$obj->isMixedContentType()) {
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

    /**
     * @return mixed[][]
     */
    protected function buildFilterStage(): array
    {
        $stage = array();

        $stage['statement.object.objectType'] = 'Activity';
        $stage['statement.actor.objectType'] = 'Agent';

        $stage['statement.object.id'] = $this->filter->getActivityId();

        $stage['statement.result.score.scaled'] = [
            '$exists' => 1
        ];

        $obj = $this->getObj();
        if (($obj->getContentType() == ilObjCmiXapi::CONT_TYPE_GENERIC) || $obj->isMixedContentType()) {
            $stage['$or'] = $this->getUsersStack();
        }

        return [
            '$match' => $stage
        ];
    }

    /**
     * @return array<string, array<string, int>>
     */
    protected function buildOrderStage(): array
    {
        return [ '$sort' => [
            'statement.timestamp' => 1
        ]];
    }

    // not used in cmi5 see above
    /**
     * @return array<string, string>[]
     */
    protected function getUsersStack(): array
    {
        $users = [];
        $obj = $this->getObj();
        if ($obj->isMixedContentType()) {
            foreach (ilCmiXapiUser::getUsersForObject($this->getObjId()) as $cmixUser) {
                $users[] = ['statement.actor.mbox' => "mailto:{$cmixUser->getUsrIdent()}"];
                $users[] = ['statement.actor.account.name' => "{$cmixUser->getUsrIdent()}"];
            }
        } else {
            foreach (ilCmiXapiUser::getUsersForObject($this->getObjId()) as $cmixUser) {
                $users[] = [
                    'statement.actor.mbox' => "mailto:{$cmixUser->getUsrIdent()}"
                ];
            }
        }
        return $users;
    }

    public function getPipelineDebug(): string
    {
        return '<pre>' . json_encode($this->buildPipeline(), JSON_PRETTY_PRINT) . '</pre>';
    }
}
