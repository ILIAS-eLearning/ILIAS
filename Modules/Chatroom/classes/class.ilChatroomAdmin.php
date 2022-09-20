<?php

declare(strict_types=1);

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

/**
 * Class ilChatroomAdmin
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomAdmin
{
    private static string $settingsTable = 'chatroom_admconfig';

    private int $config_id;
    private ?stdClass $settings;

    public function __construct(int $config_id, stdClass $settings = null)
    {
        $this->config_id = $config_id;
        $this->settings = $settings;
    }

    /**
     * Instantiates and returns ilChatroomAdmin object using instance_id and settings
     * from settingsTable.
     * @return self
     */
    public static function getDefaultConfiguration(): self
    {
        global $DIC;

        $DIC->database()->setLimit(1);
        $query = 'SELECT * FROM ' . self::$settingsTable;

        $rset = $DIC->database()->query($query);
        if ($row = $DIC->database()->fetchObject($rset)) {
            return new self((int) $row->instance_id, $row);
        }

        throw new LogicException('Could not determine any default configuration');
    }

    /**
     * Instantiates ilChatroomServerSettings object, sets data using
     * $this->settings->server_settings and returns object.
     * @return ilChatroomServerSettings
     */
    public function getServerSettings(): ilChatroomServerSettings
    {
        return ilChatroomServerSettings::loadDefault();
    }

    /**
     * Saves given $settings into settingsTable.
     * @param stdClass $settings
     */
    public function saveGeneralSettings(stdClass $settings): void
    {
        global $DIC;

        $res = $DIC->database()->queryF(
            "SELECT* FROM chatroom_admconfig WHERE instance_id = %s",
            ['integer'],
            [$this->config_id]
        );

        $row = $DIC->database()->fetchAssoc($res);

        $DIC->database()->manipulateF(
            "DELETE FROM chatroom_admconfig WHERE instance_id = %s",
            ['integer'],
            [$this->config_id]
        );

        $def_conf = '{}';
        $clnt_set = '{}';
        if (is_array($row)) {
            if ($row['default_config'] !== null) {
                $def_conf = $row['default_config'];
            }

            if ($row['client_settings'] !== null) {
                $clnt_set = $row['client_settings'];
            }
        }

        $DIC->database()->manipulateF(
            "
			INSERT INTO		chatroom_admconfig
							(instance_id, server_settings, default_config, client_settings)
			VALUES			(%s, %s, %s, %s)",
            ['integer', 'text', 'integer', 'text'],
            [$this->config_id, json_encode($settings, JSON_THROW_ON_ERROR), $def_conf, $clnt_set]
        );
    }

    /**
     * Saves given client $settings into settingsTable.
     * @param stdClass $settings
     */
    public function saveClientSettings(stdClass $settings): void
    {
        global $DIC;

        $res = $DIC->database()->queryF(
            "SELECT * FROM chatroom_admconfig WHERE instance_id = %s",
            ['integer'],
            [$this->config_id]
        );

        $row = $DIC->database()->fetchAssoc($res);

        $DIC->database()->manipulateF(
            "DELETE FROM chatroom_admconfig WHERE instance_id = %s",
            ['integer'],
            [$this->config_id]
        );

        $row['default_config'] !== null ? $def_conf = $row['default_config'] : $def_conf = "{}";
        $row['server_settings'] !== null ? $srv_set = $row['server_settings'] : $srv_set = "{}";

        $DIC->database()->manipulateF(
            "
			INSERT INTO		chatroom_admconfig
							(instance_id, server_settings, default_config, client_settings)
			VALUES			(%s, %s, %s, %s)",
            [
                'integer',
                'text',
                'integer',
                'text'
            ],
            [
                $this->config_id,
                $srv_set,
                $def_conf,
                json_encode($settings, JSON_THROW_ON_ERROR)
            ]
        );
    }

    public function loadGeneralSettings(): array
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$settingsTable .
            ' WHERE instance_id = ' . $DIC->database()->quote($this->config_id, 'integer');

        if (($row = $DIC->database()->fetchAssoc($DIC->database()->query($query))) && $row['server_settings']) {
            $settings = json_decode($row['server_settings'], true, 512, JSON_THROW_ON_ERROR);

            if (!isset($settings['protocol'])) {
                $settings['protocol'] = 'http';
            }

            if (!isset($settings['log_level'])) {
                $settings['log_level'] = 'info';
            }

            return $settings;
        }

        return [];
    }

    public function loadClientSettings(): array
    {
        global $DIC;

        $query = 'SELECT * FROM ' . self::$settingsTable .
            ' WHERE instance_id = ' . $DIC->database()->quote($this->config_id, 'integer');
        if (($row = $DIC->database()->fetchAssoc($DIC->database()->query($query))) && $row['client_settings']) {
            $settings = json_decode($row['client_settings'], true, 512, JSON_THROW_ON_ERROR);

            if (!isset($settings['client']) || !is_string($settings['client']) || $settings['client'] === '') {
                $settings['client'] = CLIENT_ID;
            }

            $settings['client_name'] = (string) $settings['name'];
            if (!$settings['client_name']) {
                $settings['client_name'] = CLIENT_ID;
            }

            if (is_numeric($settings['conversation_idle_state_in_minutes'])) {
                $settings['conversation_idle_state_in_minutes'] = max(1, $settings['conversation_idle_state_in_minutes']);
            } else {
                $settings['conversation_idle_state_in_minutes'] = 1;
            }

            return $settings;
        }

        return [];
    }
}
