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
 ********************************************************************
 */

/**
 * Administration class for plugins. Handles basic data from plugin.php files.
 *
 * @deprecated Please use ilComponentRepository or ilComponentFactory instead.
 *
 * This class currently needs refactoring. There are a lot of methods which are related to some specific slots.
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ingroup ServicesComponent
 */
class ilPluginAdmin
{
    protected ilComponentRepository $component_repository;

    public function __construct(ilComponentRepository $component_repository)
    {
        $this->component_repository = $component_repository;
    }

    protected function getPluginInfo($a_ctype, $a_cname, $a_slot_id, $a_pname): \ilPluginInfo
    {
        return $this->component_repository
            ->getComponentByTypeAndName(
                $a_ctype,
                $a_cname
            )
            ->getPluginSlotById(
                $a_slot_id
            )
            ->getPluginByName(
                $a_pname
            );
    }

    /**
     * Checks whether plugin is active (include version checks)
     *
     * ATTENTION: If one tries to remove this, the task doesn't look very hard initially.
     * `grep -r "isActive([^)]*,.*)" Modules/` (or in Services) only reveals a handful
     * of locations that actually use this function. But: If you attempt to remove these
     * locations, you run into a dependency hell in the T&A. The T&A uses dependency
     * injection, but not container. If you add ilComponentRepository as dependency, you need
     * to inject it ("courier anti pattern") in the classes above. This is super cumbersome
     * and I started to loose track soon. This should be removed, but currently my
     * concentration is not enough to do so.
     *
     * @deprecated
     *
     * @param string $a_ctype   Component Type
     * @param string $a_cname   Component Name
     * @param string $a_slot_id Slot ID
     * @param string $a_pname   Plugin Name
     *
     * @return bool
     */
    public function isActive($a_ctype, $a_cname, $a_slot_id, $a_pname): bool
    {
        trigger_error("DEPRECATED: ilPluginAdmin::isActive is deprecated. Remove your usages of the method.");
        try {
            return $this->getPluginInfo($a_ctype, $a_cname, $a_slot_id, $a_pname)->isActive();
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }
}
