<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilRuntime
 * @author  Michael Jansen <mjansen@databay.de>
 * @package Services/Environment
 */
final class ilRuntime
{
    private static ?self $instance = null;

    /**
     * The runtime is a constant state during one request, so please use the public static getInstance() to instantiate the runtime
     */
    private function __construct()
    {
    }

    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function isHHVM() : bool
    {
        return defined('HHVM_VERSION');
    }

    public function isPHP() : bool
    {
        return !$this->isHHVM();
    }

    public function isFPM() : bool
    {
        return PHP_SAPI === 'fpm-fcgi';
    }

    public function getVersion() : string
    {
        if ($this->isHHVM()) {
            return HHVM_VERSION;
        }

        return PHP_VERSION;
    }

    public function getName() : string
    {
        if ($this->isHHVM()) {
            return 'HHVM';
        }

        return 'PHP';
    }

    public function __toString() : string
    {
        return $this->getName() . ' ' . $this->getVersion();
    }

    /**
     * @return int
     */
    public function getReportedErrorLevels()
    {
        if ($this->isHHVM()) {
            return ini_get('hhvm.log.runtime_error_reporting_level');
        }

        return ini_get('error_reporting');
    }

    public function shouldLogErrors() : bool
    {
        if ($this->isHHVM()) {
            return (bool) ini_get('hhvm.log.use_log_file');
        }

        return (bool) ini_get('log_errors');
    }

    public function shouldDisplayErrors() : bool
    {
        if ($this->isHHVM()) {
            return (bool) ini_get('hhvm.debug.server_error_message');
        }

        return (bool) ini_get('display_errors');
    }
}
