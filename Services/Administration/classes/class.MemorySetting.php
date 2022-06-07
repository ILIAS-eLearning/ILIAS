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

namespace ILIAS\Administration;

/**
 * In memory setting class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class MemorySetting implements Setting
{
    protected static array $setting = [];
    public string $module = "";

    public function __construct(
        string $a_module = "common"
    ) {
        $this->module = $a_module;
    }

    public function clear() : void
    {
        self::$setting = [];
    }

    public function getModule() : string
    {
        return $this->module;
    }

    public function read() : void
    {
    }

    public function get(
        string $a_keyword,
        ?string $a_default_value = null
    ) : ?string {
        if ($a_keyword === "ilias_version") {
            return ILIAS_VERSION;
        }
        return self::$setting[$this->module][$a_keyword] ??
            $a_default_value;
    }

    public function deleteAll() : void
    {
        if (isset(self::$setting[$this->module])) {
            self::$setting[$this->module] = array();
        }
    }

    public function delete(string $a_keyword) : void
    {
        unset(self::$setting[$this->module][$a_keyword]);
    }

    public function getAll() : array
    {
        return self::$setting[$this->module] ?? [];
    }

    public function set(string $a_key, string $a_val) : void
    {
        $this->delete($a_key);
        self::$setting[$this->module][$a_key] = $a_val;
    }

    public static function _lookupValue(
        string $a_module,
        string $a_keyword
    ) : ?string {
        return self::$setting[$a_module][$a_keyword] ?? null;
    }
}
