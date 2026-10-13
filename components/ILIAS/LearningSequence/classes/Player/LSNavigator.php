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

declare(strict_types=1);

namespace ILIAS\LearningSequence\Player;

/**
 * Abstraction of the navigation between the objects of a learning sequence.
 *
 * The linear (index based) and the adaptive (condition based) navigation both
 * implement this interface so that the player itself does not need to know the
 * concrete strategy.
 */
interface LSNavigator
{
    /**
     * @param \LSLearnerItem[] $items
     * @return \LSLearnerItem[] the objects one may continue with from $current
     */
    public function getSuccessors(array $items, \LSLearnerItem $current): array;

    /**
     * All objects that are connected to $current by an outgoing edge,
     * regardless of whether they may currently be entered. Needed by views
     * (e.g. the map) that have to show blocked objects as well.
     *
     * @param \LSItem[] $items
     * @return \LSItem[]
     */
    public function getStructuralSuccessors(array $items, \LSItem $current): array;

    /**
     * @param \LSLearnerItem[] $items
     * @return \LSLearnerItem[] the objects one came from before $current
     */
    public function getPredecessors(array $items, \LSLearnerItem $current): array;

    /**
     * Whether the current object may be left (output-condition).
     */
    public function canLeave(\LSLearnerItem $current): bool;

    /**
     * Whether the target object may be entered (input-condition).
     */
    public function canEnter(\LSLearnerItem $target): bool;

    /**
     * Whether the target object may be entered, ignoring the input-conditions
     * that merely encode the graph edges (they describe alternative paths and
     * must not be AND-combined).
     */
    public function canEnterIgnoringEdges(\LSLearnerItem $target): bool;

    /**
     * Whether the target object may be entered coming from $current, i.e. only
     * the condition of the edge actually used is evaluated. Several incoming
     * edges are alternative paths and must not be AND-combined.
     */
    public function canEnterFrom(\LSLearnerItem $current, \LSLearnerItem $target): bool;

    /**
     * Loads everything the navigator needs for the given items up front, so
     * walking the graph does not hit the database once per item. Navigators
     * without any state to load may implement this as a no-op.
     *
     * @param \LSItem[] $items
     */
    public function preload(array $items): void;

    /**
     * The ids of all conditions that must be met to enter the given object.
     * Used by the map data layer; empty for navigators without conditions.
     *
     * @return int[]
     */
    public function getInputConditionIds(\LSLearnerItem $item): array;

    /**
     * The ids of all conditions that must be met to leave the given object.
     * Used by the map data layer; empty for navigators without conditions.
     *
     * @return int[]
     */
    public function getOutputConditionIds(\LSLearnerItem $item): array;
}
