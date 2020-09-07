<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestRandomQuestionSetNonAvailablePool
{
    const UNAVAILABILITY_STATUS_LOST = 'lost';
    const UNAVAILABILITY_STATUS_TRASHED = 'trashed';
    
    /**
     * @var string
     */
    protected $unavailabilityStatus;
    
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $path;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
    
    /**
     * @return string
     */
    public function getUnavailabilityStatus()
    {
        return $this->unavailabilityStatus;
    }
    
    /**
     * @param string $unavailabilityStatus
     */
    public function setUnavailabilityStatus($unavailabilityStatus)
    {
        $this->unavailabilityStatus = $unavailabilityStatus;
    }

    /**
     * @param array $row
     */
    public function assignDbRow($row)
    {
        foreach ($row as $field => $value) {
            switch ($field) {
                case 'pool_fi': $this->setId($value); break;
                case 'pool_title': $this->setTitle($value); break;
                case 'pool_path': $this->setPath($value); break;
            }
        }
    }
}
