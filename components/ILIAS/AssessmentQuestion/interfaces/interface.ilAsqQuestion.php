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
 * Interface ilAsqQuestion
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components/ILIAS/AssessmentQuestion
 */
interface ilAsqQuestion
{
    /**
     * @param $parentId
     */
    public function setParentId($parentId);

    /**
     * @return int
     */
    public function getParentId(): int;

    /**
     * @param int $questionId
     */
    public function setId($questionId);

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return string
     */
    public function getComment(): string;

    /**
     * @return int
     */
    public function getOwner(): int;

    /**
     * @return string
     */
    public function getQuestionType(): string;

    /**
     * @return string
     */
    public function getQuestionText(): string;

    /**
     * @return float
     */
    public function getPoints(): float;

    /**
     * @return string
     */
    public function getEstimatedWorkingTime(): string;

    /**
     * Loads question data
     */
    public function load();

    /**
     * Save question data
     */
    public function save();

    /**
     * Delete question
     */
    public function delete();

    /**
     * @param ilQTIItem $qtiItem
     */
    public function fromQtiItem(ilQTIItem $qtiItem);

    /**
     * @return string
     */
    public function toQtiXML(): string;

    /**
     * @return bool
     */
    public function isComplete(): bool;

    /**
     * @return ilAsqQuestionSolution
     */
    public function getBestSolution(): ilAsqQuestionSolution;

    /**
     * @return \ILIAS\UI\Component\Component
     */
    public function getSuggestedSolutionOutput(): \ILIAS\UI\Component\Component;

    /**
     * @return string
     */
    public function toJSON(): string;

    /**
     * @param string $offlineExportImagePath
     */
    public function setOfflineExportImagePath($offlineExportImagePath = null);

    /**
     * @param string $offlineExportPagePresentationMode
     */
    public function setOfflineExportPagePresentationMode($offlineExportPagePresentationMode = 'presentation');
}
