<?php declare(strict_types=1);

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\UI;

class ilChatroomSetupAgent implements Setup\Agent
{
    const PORT_MIN = 1;
    const PORT_MAX = 65535;

    public static $LOG_LEVELS = [
        'emerg',
        'alert',
        'crit',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
        'silly'
    ];

    public static $INTERVALS = [
        'days',
        'weeks',
        'months',
        'years'
    ];

    /**
     * @var Refinery\Factory
     */
    protected $refinery;

    public function __construct(Refinery\Factory $refinery)
    {
        $this->refinery = $refinery;
    }

    /**
     * @inheritdoc
     */
    public function hasConfig() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getConfigInput(Setup\Config $config = null) : UI\Component\Input\Field\Input
    {
        throw new \LogicException("Not yet implemented.");
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        $levels = self::$LOG_LEVELS;
        $intervals = self::$INTERVALS;
        // TODO: clean this up
        return $this->refinery->custom()->transformation(function ($data) use ($levels, $intervals) {
            if (is_null($data)) {
                return new Setup\NullConfig();
            }

            $protocol = 'http';
            if (isset($data['https']) && count($data['https']) > 0) {
                $protocol = 'https';
            }

            $deletion_interval = false;
            if (isset($data['deletion_interval']) && count($data['deletion_interval']) > 0) {
                $deletion_interval = true;
            }

            $ilias_proxy = false;
            if (isset($data['ilias_proxy']) && count($data['ilias_proxy']) > 0) {
                $ilias_proxy = true;
            }

            $client_proxy = false;
            if (isset($data['client_proxy']) && count($data['client_proxy']) > 0) {
                $client_proxy = true;
            }

            if (!is_null($data['port']) && (int) $data['port'] < self::PORT_MIN || (int) $data['port'] > self::PORT_MAX) {
                throw new InvalidArgumentException(
                    $data['port'] . ' is not a valid value for port. Please check your config file.'
                );
            }

            if ($data['log'] != '') {
                if (!in_array($data['log_level'], $levels)) {
                    throw new InvalidArgumentException(
                        $data['log_level'] . ' is not a valid value for log_level. Please check your config file.'
                    );
                }
            }

            if ($deletion_interval) {
                if (!in_array($data['deletion_interval']['deletion_unit'], $intervals)) {
                    throw new InvalidArgumentException(
                        $data['deletion_interval']['deletion_unit'] . ' is not a valid value for deletion_unit. Please check your config file.'
                    );
                }
                if (!is_numeric($data['deletion_interval']['deletion_value'])) {
                    throw new InvalidArgumentException(
                        $data['deletion_interval']['deletion_value'] . ' is not a valid value for deletion_value. Please check your config file.'
                    );
                }
                if (!preg_match_all('/([01][0-9]|[2][0-3]):[0-5][0-9]/', $data['deletion_interval']['deletion_time'])) {
                    throw new InvalidArgumentException(
                        $data['deletion_interval']['deletion_time'] . ' is not a valid value for deletion_time. Please check your config file.'
                    );
                }
            }

            return new \ilChatroomSetupConfig(
                $data['address'] ?? '',
                (int) $data['port'] ?? 0,
                $data['sub_directory'] ?? '',
                $protocol,
                $data['https']['cert'] ?? '',
                $data['https']['key'] ?? '',
                $data['https']['dhparam'] ?? '',
                $data['log'] ?? '',
                $data['log_level'] ?? '',
                $data['error_log'] ?? '',
                $ilias_proxy,
                $data['ilias_proxy']['ilias_url'] ?? '',
                $client_proxy,
                $data['client_proxy']['client_url'] ?? '',
                $deletion_interval,
                $data['deletion_interval']['deletion_unit'] ?? '',
                (int) $data['deletion_interval']['deletion_value'] ?? 0,
                $data['deletion_interval']['deletion_time'] ?? ''
            );
        });
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        // null would not be valid here, because this agents strictly wants to have
        // a config.
        if ($config instanceof Setup\NullConfig) {
            return new Setup\Objective\NullObjective();
        }

        return new ilChatroomServerConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        // null would be valid here, because our user might just not have passed
        // one during update.
        if ($config === null || $config instanceof Setup\NullConfig) {
            return new Setup\Objective\NullObjective();
        }

        return new ilChatroomServerConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new ilChatroomMetricsCollectedObjective($storage);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }
}
