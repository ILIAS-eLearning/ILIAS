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
 
namespace ILIAS\UI\Component\Input\Container\Filter;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * This is how a factory for filters looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      The standard filter is the default filter to be used in ILIAS. If there is no good reason
     *      using another filter instance in ILIAS, this is the one that should be used.
     *
     * rules:
     *   usage:
     *     1: Standard filters MUST be used if there is no good reason using another instance.
     *
     * ---
     * @param    string|Signal    $toggle_action_on
     * @param    string|Signal    $toggle_action_off
     * @param    string|Signal    $expand_action
     * @param    string|Signal    $collapse_action
     * @param    string|Signal    $apply_action
     * @param    string|Signal    $reset_action
     * @param    array<mixed,Input>    $inputs
     * @param    array<bool>    $is_input_rendered
     * @param    bool    $is_activated
     * @param    bool    $is_expanded
     * @return    \ILIAS\UI\Component\Input\Container\Filter\Standard
     */
    public function standard(
        $toggle_action_on,
        $toggle_action_off,
        $expand_action,
        $collapse_action,
        $apply_action,
        $reset_action,
        array $inputs,
        array $is_input_rendered,
        bool $is_activated = false,
        bool $is_expanded = false
    ) : Standard;
}
