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

use ILIAS\Setup\Environment;
use ILIAS\Setup\UnachievableException;

class ilSettingsValueDatabaseTypeSwitch extends ilSetupObjective
{
    public const VARCHAR_TO_CLOB = 'varchar-to-clob';
    public const CLOB_TO_VARCHAR = 'clob-to-varchar';

    private string $mode;

    public function __construct(string $mode)
    {
        parent::__construct(new \ILIAS\Setup\NullConfig());
        $this->mode = $mode;
    }

    public function getHash() : string
    {
        return hash('sha256', self::class);
    }

    public function getLabel() : string
    {
        if ($this->mode === self::CLOB_TO_VARCHAR) {
            return 'The field type of settings:value type is switched to VARCHAR';
        }

        return 'The field type of settings:value is switched to CLOB';
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment) : array
    {
        return [
            new ilSettingsFactoryExistsObjective(),
            new ilDatabaseInitializedObjective(),
        ];
    }

    public function achieve(Environment $environment) : Environment
    {
        $settings_factory = $environment->getResource(Environment::RESOURCE_SETTINGS_FACTORY);

        /** @var ilSetting $settings */
        $settings = $settings_factory->settingsFor('common');

        $is_clob = $settings->getValueDbType() === 'clob';

        if ($this->mode === self::CLOB_TO_VARCHAR && $is_clob) {
            $settings->changeValueDbType('text');
        } elseif ($this->mode === self::VARCHAR_TO_CLOB && !$is_clob) {
            $settings->changeValueDbType('clob');
        } else {
            throw new UnachievableException('The database field type does already match the desired field type');
        }

        return $environment;
    }

    public function isApplicable(Environment $environment) : bool
    {
        $settings_factory = $environment->getResource(Environment::RESOURCE_SETTINGS_FACTORY);

        /** @var ilSetting $settings */
        $settings = $settings_factory->settingsFor('common');

        $is_clob = $settings->getValueDbType() === 'clob';

        if ($this->mode === self::VARCHAR_TO_CLOB) {
            return !$is_clob;
        }

        return $is_clob && $settings->getLimitExceedingValues() === [];
    }
}
