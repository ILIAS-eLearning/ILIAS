<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiHighscoreReport
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiHighscoreReport
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @var array
     */
    private $tableData = [];

    /**
     * @var null|int
     */
    private $userRank = null;
    
    /**
     * @var ilCmiXapiUser[]
     */
    protected $cmixUsersByIdent;
    
    /**
     * @var int
     */
    protected $objId;
    /**
     * ilCmiXapiHighscoreReport constructor.
     * @param string $responseBody
     */
    public function __construct(string $responseBody, $objId)
    {
        $this->objId = $objId;
        $responseBody = json_decode($responseBody, true);
        
        if (count($responseBody)) {
            $this->response = $responseBody;
        } else {
            $this->response = array();
        }
        
        foreach (ilCmiXapiUser::getUsersForObject($objId) as $cmixUser) {
            $this->cmixUsersByIdent[$cmixUser->getUsrIdent()] = $cmixUser;
        }
    }

    /**
     * @return bool
     */
    public function initTableData()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $rows = [];
        $obj = ilObjCmiXapi::getInstance($this->objId,false);

        if ($obj->isMixedContentType())
        {
            foreach ($this->response as $item) {
                $userIdent = str_replace('mailto:', '', $item['mbox']);
                if (empty($userIdent))
                {
                    $userIdent =  $item['account'];
                }
                $cmixUser = $this->cmixUsersByIdent[$userIdent];
                $rows[] = [
                    'user_ident' => $userIdent,
                    'user' => '',
                    'date' => $this->formatRawTimestamp($item['timestamp']),
                    'duration' => $this->fetchTotalDuration($item['duration']),
                    'score' => $item['score']['scaled'],
                    'ilias_user_id' => $cmixUser->getUsrId()
                ];
            }
        }
        elseif ($obj->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5)
        {
            foreach ($this->response as $item) {
                $userIdent = $item['account'];
                $cmixUser = $this->cmixUsersByIdent[$userIdent];
                $rows[] = [
                    'user_ident' => $userIdent,
                    'user' => '',
                    'date' => $this->formatRawTimestamp($item['timestamp']),
                    'duration' => $this->fetchTotalDuration($item['duration']),
                    'score' => $item['score']['scaled'],
                    'ilias_user_id' => $cmixUser->getUsrId()
                ];
            }
        }
        else
        {
            foreach ($this->response as $item) {
                $userIdent = str_replace('mailto:', '', $item['mbox']);
                $cmixUser = $this->cmixUsersByIdent[$userIdent];
                $rows[] = [
                    'user_ident' => $userIdent,
                    'user' => '',
                    'date' => $this->formatRawTimestamp($item['timestamp']),
                    'duration' => $this->fetchTotalDuration($item['duration']),
                    'score' => $item['score']['scaled'],
                    'ilias_user_id' => $cmixUser->getUsrId()
                ];
            }
        }
        usort($rows, function ($a, $b) {
            return $a['score'] != $b['score'] ? $a['score'] > $b['score'] ? -1 : 1 : 0;
        });

        $i = 0;
        $prevScore = null;
        //$userRank = null;
        $retArr = [];
        foreach ($rows as $key => $item) {
            if ($prevScore !== $item['score']) {
                $i++;
            }
            $rows[$key]['rank'] = $i;
            $prevScore = $rows[$key]['score'];
            /* instantiate userObj until loginUserRank is unknown */
            if (null === $this->userRank) {
                /* just boolean */
                $userIdent = str_replace('mailto:', '', $rows[$key]['user_ident']);
                $cmixUser = $this->cmixUsersByIdent[$userIdent];
                if ($cmixUser->getUsrId() == $DIC->user()->getId()) {
                    $this->userRank = $key; //$rows[$key]['rank'];
                    $userObj = ilObjectFactory::getInstanceByObjId($cmixUser->getUsrId());
                    $rows[$key]['user'] = $userObj->getFullname();
                }
                $retArr[$key] = $rows[$key];
            } else {
                /* same same */
                $rows[$key]['user_ident'] = false;
                $retArr[$key] = $rows[$key];
            } // EOF if( null === $this->userRank )
        } // EOF foreach ($rows as $key => $item)
        $this->tableData = $retArr;
        return true;
    }

    private function identUser($userIdent)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
    
        $cmixUser = $this->cmixUsersByIdent[$userIdent];
        
        if ($cmixUser->getUsrId() == $DIC->user()->getId()) {
            return true;
        }
        return false;
    }
    
    protected function fetchTotalDuration($allDurations)
    {
        $totalDuration = 0;
        
        foreach ($allDurations as $duration) {
            $totalDuration += ilObjSCORM2004LearningModule::_ISODurationToCentisec($duration) / 100;
        }

        $hours = floor($totalDuration / 3600);
        $hours = strlen($hours) < 2 ? "0" . $hours : $hours;
        $totalDuration = $hours . ":" . date('i:s', $totalDuration);

        return $totalDuration;
    }

    private function formatRawTimestamp($rawTimestamp)
    {
        $dateTime = ilCmiXapiDateTime::fromXapiTimestamp($rawTimestamp);
        return ilDatePresentation::formatDate($dateTime);
    }

    public function getTableData()
    {
        return $this->tableData;
    }

    public function getUserRank()
    {
        return $this->userRank;
    }

    public function getResponseDebug()
    {
        /*
        foreach($this->response as $key => $item)
        {
            $user = ilCmiXapiUser::getUserFromIdent(
                ilObjectFactory::getInstanceByRefId($_GET['ref_id']),
                $tableRowData['mbox']
            );

            $this->response[$key]['realname'] = $user->getFullname();
        }
        */
        return '<pre>' . json_encode($this->response, JSON_PRETTY_PRINT) . '</pre>';
    }
}
