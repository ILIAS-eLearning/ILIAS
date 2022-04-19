<?php

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * A factory that builds ilSettings that can be used for DI.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilSettingsFactory
{
    protected ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Get setting instance for module
     */
    public function settingsFor(string $a_module = "common") : ilSetting
    {
        $tmp_dic = $GLOBALS["DIC"] ?? null;
        try {
            // ilSetting pulls the database once in the constructor, we force it to
            // use ours.
            $DIC = new ILIAS\DI\Container();
            $DIC["ilDB"] = $this->db;
            $DIC["ilBench"] = null;
            $GLOBALS["DIC"] = $DIC;

            // Always load from db, as caching could be implemented as a
            // decorator to this.
            $settings = new ilSetting($a_module, true);

            // Provoke a setting to populate the value_type in ilSettings,
            // use a field that is likely to exist.
            // ... code was strange in < ILIAS 8 (wrong parameter count, module name for keyword, ...)
            // use dummy instead
            $settings->set(
                "dummy",
                $settings->get("dummy", "dummy")
            );
        } finally {
            $GLOBALS["DIC"] = $tmp_dic;
        }

        return $settings;
    }
}
