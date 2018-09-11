<?php
/*
  +----------------------------------------------------------------------------+
  | ILIAS open source                                                          |
  +----------------------------------------------------------------------------+
  | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
  |                                                                            |
  | This program is free software; you can redistribute it and/or              |
  | modify it under the terms of the GNU General Public License                |
  | as published by the Free Software Foundation; either version 2             |
  | of the License, or (at your option) any later version.                     |
  |                                                                            |
  | This program is distributed in the hope that it will be useful,            |
  | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
  | GNU General Public License for more details.                               |
  |                                                                            |
  | You should have received a copy of the GNU General Public License          |
  | along with this program; if not, write to the Free Software                |
  | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
  +----------------------------------------------------------------------------+
*/

/**
 * Class ilCertificateMigrationInformationObject
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
class ilCertificateMigrationInformationObject
{
    /** @var int */
    private $id;

    /** @var int */
    private $usr_id;

    /** @var bool */
    private $lock;

    /** @var int */
    private $found_items;

    /** @var int */
    private $processed_items;

    /** @var int */
    private $progress;

    /** @var string */
    private $state;

    /** @var int */
    private $starting_time;

    /** @var int */
    private $finished_time;

    /**
     * ilCertificateMigrationInformationObject constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->setDataByArray($data);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return isset($this->id) ? $this->id : 0;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->usr_id;
    }

    /**
     * @return bool
     */
    public function getLock()
    {
        return $this->lock;
    }

    /**
     * @return int
     */
    public function getProgressedItems()
    {
        return $this->processed_items;
    }

    /**
     * @return int
     */
    public function getFoundItems()
    {
        return $this->found_items;
    }

    /**
     * @return int
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return int
     */
    public function getStartingTime()
    {
        return isset($this->starting_time) ? $this->starting_time : 0;
    }

    /**
     * @return int
     */
    public function getFinishedTime()
    {
        return isset($this->finished_time) ? $this->finished_time : 0;
    }

    /**
     * @return int
     */
    public function getProcessingTime()
    {
        return $this->getFinishedTime() - $this->getStartingTime();
    }

    /**
     * @return array
     */
    public function getDataAsArray()
    {
        return [
            'id' => $this->getId(),
            'usr_id' => $this->getUserId(),
            'lock' => (int)$this->getLock(),
            'found_items' => $this->getFoundItems(),
            'processed_items' => $this->getProgressedItems(),
            'progress' => $this->getProgress(),
            'state' => $this->getState(),
            'started_ts' => $this->getStartingTime(),
            'finished_ts' => $this->getFinishedTime(),
        ];
    }

    /**
     * @param $data
     * @return void
     */
    private function setDataByArray($data)
    {
        $this->id = $data['id'];
        $this->usr_id = $data['usr_id'];
        $this->lock = ($data['lock'] === true || $data['lock'] === 1);
        $this->found_items = $data['found_items'];
        $this->processed_items = $data['processed_items'];
        $this->progress = $data['progress'];
        $this->state = $data['state'];
        $this->starting_time = $data['started_ts'];
        $this->finished_time = $data['finished_ts'];
    }
}