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
 * Class ilCmiXapiStatmentsAggregateLinkBuilder
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiStatementsReportLinkBuilder extends ilCmiXapiAbstractReportLinkBuilder
{
    /**
     * @return array<int, array<string|mixed[]>>
     */
    protected function buildPipeline(): array
    {
        $pipeline = [];

        $pipeline[] = $this->buildFilterStage();

        $pipeline[] = $this->buildOrderingStage();

        $pipeline[] = ['$facet' => [
            'stage1' => [
                ['$group' => ['_id' => null, 'count' => ['$sum' => 1]]]
            ],
            'stage2' => $this->buildLimitStage()
        ]
        ];

        $pipeline[] = ['$unwind' => '$stage1'];

        $pipeline[] = [
            '$project' => [
                'maxcount' => '$stage1.count',
                'statements' => '$stage2.statement'
            ]
        ];

        $log = ilLoggerFactory::getLogger('cmix');
        //$log->debug("aggregation pipeline:\n" . json_encode($pipeline, JSON_PRETTY_PRINT));

        return $pipeline;
    }

    /**
     * @return array<int, mixed[]>
     */
    protected function buildLimitStage(): array
    {
        $stage = array(
            array('$skip' => (int) $this->filter->getOffset())
        );

        if ($this->filter->getLimit() !== 0) {
            $stage[] = array('$limit' => (int) $this->filter->getLimit());
        }
        return $stage;
    }

    /**
     * @return mixed[][]
     */
    protected function buildFilterStage(): array
    {
        $cmi5_extensions_query = false;

        $stage = array();
        $stage['statement.object.objectType'] = 'Activity';
        $stage['statement.actor.objectType'] = 'Agent';
        if ($this->filter->getVerb()) {
            $stage['statement.verb.id'] = $this->filter->getVerb();
        }

        if ($this->filter->getStartDate() || $this->filter->getEndDate()) {
            $stage['statement.timestamp'] = array();

            if ($this->filter->getStartDate() !== null) {
                $stage['statement.timestamp']['$gt'] = $this->filter->getStartDate()->toXapiTimestamp();
            }

            if ($this->filter->getEndDate() !== null) {
                $stage['statement.timestamp']['$lt'] = $this->filter->getEndDate()->toXapiTimestamp();
            }
        }

        $obj = $this->getObj();
        $activityId = array();

        if ($cmi5_extensions_query == true && $obj->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5 && !$obj->isMixedContentType()) {
            // https://github.com/AICC/CMI-5_Spec_Current/blob/quartz/cmi5_spec.md#963-extensions
            $activityId['statement.context.extensions.https://ilias&46;de/cmi5/activityid'] = $obj->getActivityId();
        } else {
            // for case-insensive: '$regex' => '(?i)^' . preg_quote($this->filter->getActivityId()) . ''
            $activityQuery = [
                '$regex' => '^' . preg_quote($this->filter->getActivityId()) . ''
            ];
            $activityId['$or'] = [];
            // ToDo : restriction to exact activityId?
            // query existing activityId in grouping? we have not enough control over acticityId in xapi statements
            // another way put the obj_id into a generated registration, but we are not sure that content will put this into statement context
            // $activityId['$or'][] = ['statement.object.id' => "{$this->filter->getActivityId()}"];
            $activityId['$or'][] = ['statement.object.id' => $activityQuery];
            $activityId['$or'][] = ['statement.context.contextActivities.parent.id' => $activityQuery];
        }

        $actor = array();

        // mixed
        if ($obj->isMixedContentType()) {
            if ($this->filter->getActor()) {
                // could be registration query but so what...
                foreach (ilCmiXapiUser::getUserIdents($this->getObjId(), $this->filter->getActor()->getUsrId()) as $usrIdent) {
                    $actor['$or'][] = ['statement.actor.mbox' => "mailto:{$usrIdent}"]; // older statements
                    $actor['$or'][] = ['statement.actor.account.name' => "{$usrIdent}"];
                }
                // not launched yet?
                if (count($actor) == 0) {
                    $actor['$or'][] = ['statement.actor.mbox' => "mailto:{$this->filter->getActor()->getUsrIdent()}"]; // older statements
                    $actor['$or'][] = ['statement.actor.account.name' => "{$this->filter->getActor()->getUsrIdent()}"];
                }
            } else {
                $actor['$or'] = [];
                foreach (ilCmiXapiUser::getUsersForObject($this->getObjId()) as $cmixUser) {
                    $actor['$or'][] = ['statement.actor.mbox' => "mailto:{$cmixUser->getUsrIdent()}"];
                    $actor['$or'][] = ['statement.actor.account.name' => "{$cmixUser->getUsrIdent()}"];
                }
            }
        } elseif ($obj->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) { // new
            if ($this->filter->getActor()) {
                $cmixUser = $this->filter->getActor();
                $actor['statement.context.registration'] = $cmixUser->getRegistration();
            }
        } else {
            if ($this->filter->getActor()) {
                foreach (ilCmiXapiUser::getUserIdents($this->getObjId(), $this->filter->getActor()->getUsrId()) as $usrIdent) {
                    $actor['$or'][] = ['statement.actor.mbox' => "mailto:{$usrIdent}"];
                }
                // not launched yet?
                if (count($actor) == 0) {
                    $actor['statement.actor.mbox'] = $this->filter->getActor()->getUsrIdent();
                }
            }
            /**
             * i don't think this will work with user >~ 100
             * this will blow up the GET request
             * GET Queries are sometimes limited to an amount of characters
             */
            else {
                $actor['$or'] = [];
                foreach (ilCmiXapiUser::getUsersForObject($this->getObjId()) as $cmixUser) {
                    $actor['$or'][] = ['statement.actor.mbox' => "mailto:{$cmixUser->getUsrIdent()}"];
                }
            }
        }
        $stage['$and'] = [];
        $stage['$and'][] = $activityId;
        if (count($actor) > 0) {
            $stage['$and'][] = $actor;
        }
        return array('$match' => $stage);
    }

    /**
     * @return array<string, int[]>
     */
    protected function buildOrderingStage(): array
    {
        $obj = $this->getObj();
        $actor = '';
        if ($obj->getPrivacyName() != ilObjCmiXapi::PRIVACY_NAME_NONE) {
            $actor = 'statement.actor.name';
        } else {
            if ($obj->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) {
                if ($obj->getPublisherId() == '') { // old
                    $actor = 'statement.actor.mbox';
                } else {
                    $actor = 'statement.actor.account.name';
                }
            } else {
                $actor = 'statement.actor.mbox';
            }
        }
        switch ($this->filter->getOrderField()) {
            case 'object': // definition/description are displayed in the Table if not empty => sorting not alphabetical on displayed fields
                $field = 'statement.object.id';
                break;

            case 'verb':
                $field = 'statement.verb.id';
                break;

            case 'actor':
                $field = $actor;
                break;

            case 'date':
            default:
                $field = 'statement.timestamp';
                break;
        }

        $orderingFields = array(
            $field => $this->filter->getOrderDirection() == 'desc' ? -1 : 1
        );

        return ['$sort' => $orderingFields];
    }
}
