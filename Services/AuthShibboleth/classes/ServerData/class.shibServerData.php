<?php
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
 * Class shibServerData
 * @deprecated
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class shibServerData extends shibConfig
{
    /**
     * @var \shibServerData|null
     */
    protected static $server_cache;

    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    protected function __construct(array $data)
    {
        $shib_config = shibConfig::getInstance();
        foreach (array_keys(get_class_vars(shibConfig::class)) as $field) {
            $str = $shib_config->getValueByKey($field);
            if ($str !== null) {
                $this->{$field} = $data[$str] ?? '';
            }
        }
    }

    public static function getInstance(): \shibConfig
    {
        if (!isset(self::$server_cache)) {
            self::$server_cache = new self($_SERVER);
        }

        return self::$server_cache;
    }
}
