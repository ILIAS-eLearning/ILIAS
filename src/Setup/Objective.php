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

namespace ILIAS\Setup;

/**
 * An objective is a desired state of the system that is supposed to be created
 * by the setup.
 *
 * This interface would benefit from generics, in fact it would be parametrized
 * with a Config-type.
 */
interface Objective
{
    /**
     * Get a hash for this objective.
     *
     * The hash of two objectives must be the same, if they are the same objective, with
     * the same config on the same environment, i.e. if the one is achieved the
     * other is achieved as well because they are the same.
     */
    public function getHash(): string;

    /**
     * Get a label that describes this objective.
     */
    public function getLabel(): string;

    /**
     * Get to know if this is an interesting objective for a human.
     */
    public function isNotable(): bool;

    /**
     * Objectives might depend on other objectives.
     *
     * @throw UnachievableException if the objective is not achievable
     *
     * @return Objective[]
     */
    public function getPreconditions(Environment $environment): array;

    /**
     * Objectives can be achieved. They might add resources to the environment when
     * they have been achieved.
     *
     * This method needs to be idempotent for a given environment. That means: if
     * this is executed a second time, nothing new should happen. Or the other way
     * round: if the environment already looks like desired, the objective should
     * not take any further actions when this is called.
     *
     * @throw \LogicException if there are unfullfilled preconditions.
     * @throw \RuntimeException if there are missing resources.
     */
    public function achieve(Environment $environment): Environment;

    /**
     * Get to know whether the objective is applicable.
     *
     * Don't change the environment or cause changes on services in the environment.
     * Just check if this objective needs to be achieved, either currently or at all.
     * In case of doubt whether the objective is applicable or not return true.
     */
    public function isApplicable(Environment $environment): bool;
}
