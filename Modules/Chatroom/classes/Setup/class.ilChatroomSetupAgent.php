<?php declare(strict_types=1);

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

use ILIAS\Refinery;
use ILIAS\Setup;
use ILIAS\UI;

class ilChatroomSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    private const PORT_MIN = 1;
    private const PORT_MAX = 65535;

    /** @var string[] */
    public static array $LOG_LEVELS = [
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

    /** @var string[] */
    public static array $INTERVALS = [
        'days',
        'weeks',
        'months',
        'years'
    ];

    protected Refinery\Factory $refinery;

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
        throw new LogicException("Not yet implemented.");
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        $levels = self::$LOG_LEVELS;
        $intervals = self::$INTERVALS;

        return $this->refinery->custom()->transformation(static function ($data) use (
            $levels,
            $intervals
        ) : Setup\Config {
            if (is_null($data)) {
                return new Setup\NullConfig();
            }

            $protocol = 'http';
            if (isset($data['https']) && is_array($data['https']) && $data['https'] !== []) {
                $protocol = 'https';
            }

            $deletion_interval = false;
            if (
                isset($data['deletion_interval']) &&
                is_array($data['deletion_interval']) && $data['deletion_interval'] !== []
            ) {
                $deletion_interval = true;
            }

            $ilias_proxy = false;
            if (isset($data['ilias_proxy']) && is_array($data['ilias_proxy']) && $data['ilias_proxy'] !== []) {
                $ilias_proxy = true;
            }

            $client_proxy = false;
            if (isset($data['client_proxy']) && is_array($data['client_proxy']) && $data['client_proxy'] !== []) {
                $client_proxy = true;
            }

            if (isset($data['address']) && !is_string($data['address'])) {
                throw new InvalidArgumentException(sprintf(
                    '%s is not a valid value for address (must be a string). Please check your config file.',
                    $data['address'],
                ));
            }

            if (
                isset($data['port']) && (
                    !is_numeric($data['port']) ||
                    ((int) $data['port'] < self::PORT_MIN || (int) $data['port'] > self::PORT_MAX)
                )
            ) {
                throw new InvalidArgumentException(sprintf(
                    '%s is not a valid value for port (must be between %s and %s). Please check your config file.',
                    $data['port'] ?? '',
                    self::PORT_MIN,
                    self::PORT_MAX
                ));
            }

            if (isset($data['sub_directory']) && !is_string($data['sub_directory'])) {
                throw new InvalidArgumentException(sprintf(
                    '%s is not a valid value for sub_directory (must be a string). Please check your config file.',
                    $data['sub_directory'],
                ));
            }

            if (isset($data['log']) && !is_string($data['log'])) {
                throw new InvalidArgumentException(sprintf(
                    '%s is not a valid value for log (must be a string). Please check your config file.',
                    $data['log'],
                ));
            }

            if (isset($data['error_log']) && !is_string($data['error_log'])) {
                throw new InvalidArgumentException(sprintf(
                    '%s is not a valid value for error_log (must be a string). Please check your config file.',
                    $data['error_log'],
                ));
            }

            if (
                isset($data['log']) && $data['log'] !== '' &&
                !in_array((string) ($data['log_level'] ?? ''), $levels, true)
            ) {
                throw new InvalidArgumentException(sprintf(
                    '%s is not a valid value for log_level (must be one of: %s). Please check your config file.',
                    $data['log_level'] ?? '',
                    implode(', ', $levels)
                ));
            }

            if ($deletion_interval) {
                if (!in_array($data['deletion_interval']['deletion_unit'] ?? null, $intervals, true)) {
                    throw new InvalidArgumentException(sprintf(
                        '%s is not a valid value for deletion_unit (must be one of: %s). Please check your config file.',
                        $data['deletion_interval']['deletion_unit'] ?? '',
                        implode(', ', $intervals)
                    ));
                }
                if (
                    !isset($data['deletion_interval']['deletion_value']) ||
                    !is_numeric($data['deletion_interval']['deletion_value'])
                ) {
                    throw new InvalidArgumentException(sprintf(
                        '%s is not a valid value for deletion_value. Please check your config file.',
                        $data['deletion_interval']['deletion_value'] ?? ''
                    ));
                }
                if (
                    !isset($data['deletion_interval']['deletion_time']) ||
                    !is_string($data['deletion_interval']['deletion_time']) ||
                    !preg_match('/([01][0-9]|[2][0-3]):[0-5][0-9]/', $data['deletion_interval']['deletion_time'])
                ) {
                    throw new InvalidArgumentException(sprintf(
                        '%s is not a valid value for deletion_time. Please check your config file.',
                        $data['deletion_interval']['deletion_time'] ?? ''
                    ));
                }
            }

            return new ilChatroomSetupConfig(
                $data['address'] ?? '',
                (int) ($data['port'] ?? 0),
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
                (int) ($data['deletion_interval']['deletion_value'] ?? 0),
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
