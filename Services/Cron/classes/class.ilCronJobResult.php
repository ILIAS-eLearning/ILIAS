<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron job result data container
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCron
 */
class ilCronJobResult
{
    const STATUS_INVALID_CONFIGURATION = 1;
    const STATUS_NO_ACTION = 2;
    const STATUS_OK = 3;
    const STATUS_CRASHED = 4;
    const STATUS_RESET = 5;
    const STATUS_FAIL = 6;
    
    const CODE_NO_RESULT      = 'job_no_result';
    const CODE_MANUAL_RESET   = 'job_manual_reset';
    const CODE_SUPPOSED_CRASH = 'job_auto_deactivation_time_limit';
    
    protected $status; // [int]
    protected $message; // [string]
    protected $code; // [string]
    protected $duration; // [float]

    /**
     * @return array
     */
    public static function getCoreCodes()
    {
        return array(
            self::CODE_NO_RESULT, self::CODE_MANUAL_RESET, self::CODE_SUPPOSED_CRASH
        );
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function setStatus($a_value)
    {
        $a_value = (int) $a_value;
        if (in_array($a_value, $this->getValidStatus())) {
            $this->status = $a_value;
        }
    }
    
    protected function getValidStatus()
    {
        return array(self::STATUS_INVALID_CONFIGURATION, self::STATUS_NO_ACTION,
            self::STATUS_OK, self::STATUS_CRASHED, self::STATUS_FAIL);
    }
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function setMessage($a_value)
    {
        $this->message = trim($a_value);
    }
    
    public function getCode()
    {
        return $this->code;
    }
    
    public function setCode($a_value)
    {
        $this->code = $a_value;
    }
    
    public function getDuration()
    {
        return $this->duration;
    }
    
    public function setDuration($a_value)
    {
        $this->duration = number_format($a_value, 3, ".", "");
    }
}
