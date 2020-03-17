<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Handles calendar authentication tokens for external calendar subscriptions
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarAuthenticationToken
{
    const SELECTION_NONE = 0;
    const SELECTION_PD = 1;
    const SELECTION_CATEGORY = 2;
    const SELECTION_CALENDAR = 3;
    
    private $user = null;
    
    private $token = '';
    private $selection_type = 0;
    private $calendar = 0;
    
    private $ical = null;
    private $ical_ctime = null;
    
    /**
     * Constructor
     * @param int $a_user_id
     * @param string $a_hash
     * @return ilCalendarAuthenticationKey
     */
    public function __construct($a_user_id, $a_token = '')
    {
        $this->user = $a_user_id;
        $this->token = $a_token;
        
        $this->read();
    }
    
    public static function lookupAuthToken($a_user_id, $a_selection, $a_calendar = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM cal_auth_token " .
            "WHERE user_id = " . $ilDB->quote($a_user_id, 'integer') . ' ' .
            "AND selection = " . $ilDB->quote($a_selection, 'integer') . ' ' .
            "AND calendar = " . $ilDB->quote($a_calendar, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->hash;
        }
        return false;
    }
    
    /**
     * Lookup user by hash
     * @param object $a_token
     * @return
     */
    public static function lookupUser($a_token)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM cal_auth_token " .
            "WHERE hash = " . $ilDB->quote($a_token, 'text');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->user_id;
        }
        return 0;
    }
    
    /**
     * get selection type for key
     * @return int selection type
     */
    public function getSelectionType()
    {
        return $this->selection_type;
    }
    

    /**
     * Get current user
     * @return int user
     */
    public function getUserId()
    {
        return $this->user;
    }
    
    /**
     * set selection type
     * @param int $a_type
     * @return
     */
    public function setSelectionType($a_type)
    {
        $this->selection_type = $a_type;
    }
    
    /**
     * set calendar id
     * @param object $a_cal
     * @return
     */
    public function setCalendar($a_cal)
    {
        $this->calendar = $a_cal;
    }
    
    public function getCalendar()
    {
        return $this->calendar;
    }
    
    public function setIcal($ical)
    {
        $this->ical = $ical;
    }
    
    /**
     * get stored ical
     * @return
     */
    public function getIcal()
    {
        return $this->ical;
    }
    
    
    /**
     * get token
     * @return
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * store ical
     * @return
     */
    public function storeIcal()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $ilDB->update(
            'cal_auth_token',
            array(
                'ical' => array('clob',$this->getIcal()),
                'c_time' => array('integer',time())
            ),
            array(
                'user_id' => array('integer',$this->getUserId()),
                'hash' => array('text',$this->getToken())
            )
        );
    }
    
    /**
     * Check if cache is disabled or expired
     * @return
     */
    public function isIcalExpired()
    {
        return true;

        include_once './Services/Calendar/classes/class.ilCalendarSettings.php';
        
        if (!ilCalendarSettings::_getInstance()->isSynchronisationCacheEnabled()) {
            return true;
        }
        if (!ilCalendarSettings::_getInstance()->getSynchronisationCacheMinutes()) {
            return true;
        }
        return time() > ($this->ical_ctime + 60 * ilCalendarSettings::_getInstance()->getSynchronisationCacheMinutes());
    }
    
    /**
     * Add token
     * @return
     */
    public function add()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->createToken();
        
        $query = "INSERT INTO cal_auth_token (user_id,hash,selection,calendar) " .
            "VALUES ( " .
            $ilDB->quote($this->getUserId(), 'integer') . ', ' .
            $ilDB->quote($this->getToken(), 'text') . ', ' .
            $ilDB->quote($this->getSelectionType(), 'integer') . ', ' .
            $ilDB->quote($this->getCalendar(), 'integer') . ' ' .
            ')';
        $ilDB->manipulate($query);
        
        return $this->getToken();
    }
    
    /**
     * Create a new token
     * @return
     */
    protected function createToken()
    {
        $this->token = md5($this->getUserId() . $this->getSelectionType() . rand());
    }
    
    /**
     * Read key
     * @return
     */
    protected function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!$this->getToken()) {
            $query = "SELECT * FROM cal_auth_token " .
                "WHERE user_id = " . $ilDB->quote($this->getUserId(), 'integer');
        } else {
            $query = 'SELECT * FROM cal_auth_token ' .
                'WHERE user_id = ' . $ilDB->quote($this->getUserId(), 'integer') . ' ' .
                'AND hash = ' . $ilDB->quote($this->getToken(), 'text');
        }
            
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->token = $row->hash;
            $this->selection_type = $row->selection;
            $this->calendar = $row->calendar;
            $this->ical = $row->ical;
            $this->ical_ctime = $row->c_time;
        }
        return true;
    }
}
