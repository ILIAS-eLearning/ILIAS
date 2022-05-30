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
 * Factory for user action contexts
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserActionContextFactory
{
    protected static array $contexts = array(
        array(
            "component" => "Services/Awareness",
            "class" => "ilAwarenessUserActionContext"
        ),
        array(
            "component" => "Services/User/Gallery",
            "class" => "ilGalleryUserActionContext"
        )
    );

    /**
     * Get all action contexts
     *
     * @return array[ilUserActionContext] all providers
     */
    public static function getAllActionContexts() : array
    {
        $contexts = array();

        foreach (self::$contexts as $p) {
            $dir = $p["dir"] ?? "classes";
            $contexts[] = new $p["class"]();
        }

        return $contexts;
    }
}
