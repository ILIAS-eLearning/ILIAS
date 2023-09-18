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
 * Class arConnectorMap
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class arConnectorMap
{
    protected static array $map = [];

    public static function register(ActiveRecord $activeRecord, arConnector $arConnector): void
    {
        self::$map[$activeRecord::class] = $arConnector;
    }

    public static function get(ActiveRecord $activeRecord): \arConnector
    {
        if (!isset(self::$map[$activeRecord::class])) {
            return new arConnectorDB();
        }
        if (!self::$map[$activeRecord::class] instanceof arConnector) {
            return new arConnectorDB();
        }
        return self::$map[$activeRecord::class];
    }
}
