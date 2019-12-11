<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiDateTimeDurationInputGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiDateDurationInputGUI extends ilDateDurationInputGUI
{
    public function getValue()
    {
        $duration = array();
        
        if ($this->getStart() instanceof ilDateTime) {
            $duration['start'] = $this->getStart()->get(IL_CAL_UNIX);
        }
        
        if ($this->getEnd() instanceof ilDateTime) {
            $duration['end'] = $this->getEnd()->get(IL_CAL_UNIX);
        }
        
        return $duration;
    }
    
    /**
     * @return ilCmiXapiDateTime|null
     */
    public function getStartXapiDateTime()
    {
        if ($this->getStart() instanceof ilDateTime) {
            try {
                $xapiDateTime = ilCmiXapiDateTime::fromIliasDateTime($this->getStart());
            } catch (ilDateTimeException $e) {
                return null;
            }
        }
        
        return $xapiDateTime;
    }
    
    /**
     * @return ilCmiXapiDateTime|null
     */
    public function getEndXapiDateTime()
    {
        if ($this->getEnd() instanceof ilDateTime) {
            try {
                $xapiDateTime = ilCmiXapiDateTime::fromIliasDateTime($this->getEnd());
            } catch (ilDateTimeException $e) {
                return null;
            }
        }
        
        return $xapiDateTime;
    }
}
