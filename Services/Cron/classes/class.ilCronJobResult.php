<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron job result data container
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCron
 */
class ilCronJobResult
{
    public const STATUS_INVALID_CONFIGURATION = 1;
    public const STATUS_NO_ACTION = 2;
    public const STATUS_OK = 3;
    public const STATUS_CRASHED = 4;
    public const STATUS_RESET = 5;
    public const STATUS_FAIL = 6;

    public const CODE_NO_RESULT = 'job_no_result';
    public const CODE_MANUAL_RESET = 'job_manual_reset';
    public const CODE_SUPPOSED_CRASH = 'job_auto_deactivation_time_limit';
    
    protected int $status = self::STATUS_NO_ACTION;
    protected string $message = '';
    protected string $code = self::CODE_NO_RESULT;
    protected string $duration = '0';

    /**
     * @return string[]
     */
    public static function getCoreCodes() : array
    {
        return [
            self::CODE_NO_RESULT,
            self::CODE_MANUAL_RESET,
            self::CODE_SUPPOSED_CRASH,
        ];
    }
    
    public function getStatus() : int
    {
        return $this->status;
    }
    
    public function setStatus(int $a_value) : void
    {
        if (in_array($a_value, $this->getValidStatus(), true)) {
            $this->status = $a_value;
        }
    }

    /**
     * @return int[]
     */
    protected function getValidStatus() : array
    {
        return [
            self::STATUS_INVALID_CONFIGURATION,
            self::STATUS_NO_ACTION,
            self::STATUS_OK,
            self::STATUS_CRASHED,
            self::STATUS_FAIL,
        ];
    }
    
    public function getMessage() : string
    {
        return $this->message;
    }
    
    public function setMessage(string $a_value) : void
    {
        $this->message = trim($a_value);
    }
    
    public function getCode() : string
    {
        return $this->code;
    }
    
    public function setCode($a_value) : void
    {
        $this->code = $a_value;
    }
    
    public function getDuration() : float
    {
        return (float) $this->duration;
    }
    
    public function setDuration(float $a_value) : void
    {
        $this->duration = number_format($a_value, 3, ".", "");
    }
}
