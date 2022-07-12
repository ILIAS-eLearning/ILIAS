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
 * Setting interface
 *
 * @author Alexander Killing <killing@leifos.de>
 */
interface Setting
{
    // Get current module
    public function getModule() : string;

    // Read current module
    public function read() : void;

    // Get a setting
    public function get(
        string $a_keyword,
        ?string $a_default_value = null
    ) : ?string;

    // Delete all settings of current module
    public function deleteAll() : void;

    // Delete setting
    public function delete(string $a_keyword) : void;

    // Get all settings as array
    public function getAll() : array;

    // Set a setting
    public function set(string $a_key, string $a_val) : void;

    // Lookup a setting
    public static function _lookupValue(
        string $a_module,
        string $a_keyword
    ) : ?string;
}
