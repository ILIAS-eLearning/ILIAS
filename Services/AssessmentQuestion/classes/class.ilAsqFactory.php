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
 * Class ilAsqQuestionAuthoringFactory
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @package    Services/AssessmentQuestion
 */
class ilAsqFactory
{
    /**
     * @return ilAsqService
     */
    public function service(): ilAsqService
    {
        return new ilAsqService();
    }

    /**
     * @param integer $parentObjectId
     * @return array
     */
    public function getQuestionDataArray($parentObjectId): array
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        /* @var ilComponentRepository $component_repository */
        $component_repository = $DIC['component.repository'];

        $list = new ilAssQuestionList($DIC->database(), $DIC->language(), $component_repository);
        $list->setParentObjIdsFilter([$parentObjectId]);
        $list->load();

        return $list->getQuestionDataArray(); // returns an array of arrays containing the question data

        /**
         * TBD: Should we return an iterator with ilAsqQuestion instances?
         * Issue: ilTable(2) does not support this kind object structure.
         */
    }

    /**
     * @param integer $parentObjectId
     * @return ilAsqQuestion[]
     */
    public function getQuestionInstances($parentObjectId): array
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        /* @var ilComponentRepository $component_repository */
        $component_repository = $DIC['component.repository'];

        $list = new ilAssQuestionList($DIC->database(), $DIC->language(), $component_repository);
        $list->setParentObjIdsFilter(array($parentObjectId));
        $list->load();

        $questionInstances = array();

        foreach ($list->getQuestionDataArray() as $questionId => $questionData) {
            $questionInstances[] = $this->getQuestionInstance($questionId);
        }

        return $questionInstances;
    }

    /**
     * @param ilAsqQuestion $questionInstance
     * @return ilAsqQuestionAuthoring
     */
    public function getAuthoringCommandInstance($questionInstance): ilAsqQuestionAuthoring
    {
        $authoringGUI; /* @var ilAsqQuestionAuthoring $authoringGUI */

        /**
         * initialise $authoringGUI as an instance of the question type corresponding authoring class
         * that implements ilAsqQuestionAuthoring depending on the given $questionInstance
         */

        $authoringGUI->setQuestion($questionInstance);

        return $authoringGUI;
    }

    /**
     * render purpose constants that are required to get corresponding presentation renderer
     */
    public const RENDER_PURPOSE_PLAYBACK = 'renderPurposePlayback'; // e.g. Test Player
    public const RENDER_PURPOSE_DEMOPLAY = 'renderPurposeDemoplay'; // e.g. Page Editing View in Test
    public const RENDER_PURPOSE_PREVIEW = 'renderPurposePreview'; // e.g. Preview Player
    public const RENDER_PURPOSE_PRINT_PDF = 'renderPurposePrintPdf'; // When used for PDF rendering
    public const RENDER_PURPOSE_INPUT_VALUE = 'renderPurposeInputValue'; // When used as RTE Input Content

    /**
     * @param ilAsqQuestion $questionInstance
     * @return ilAsqQuestionPresentation
     */
    public function getQuestionPresentationInstance($questionInstance, $renderPurpose): ilAsqQuestionPresentation
    {
        $presentationGUI; /* @var ilAsqQuestionPresentation $presentationGUI */

        /**
         * initialise $presentationGUI as an instance of the question type corresponding presentation class
         * that implements ilAsqQuestionPresentation depending on the given $questionInstance
         * and depending on the given render purpose.
         */

        $presentationGUI->setQuestion($questionInstance);

        return $presentationGUI;
    }

    /**
     * @param integer $questionId
     * @return ilAsqQuestion
     */
    public function getQuestionInstance($questionId): ilAsqQuestion
    {
        $questionInstance; /* @var ilAsqQuestion $questionInstance */

        /**
         * initialise $questionInstance as an instance of the question type corresponding object class
         * that implements ilAsqQuestion depending on the given $questionId
         */

        $questionInstance->setId($questionId);
        $questionInstance->load();

        return $questionInstance;
    }

    /**
     * @param string $questionId
     * @return ilAsqQuestion
     */
    public function getEmptyQuestionInstance($questionType): ilAsqQuestion
    {
        $questionInstance; /* @var ilAsqQuestion $questionInstance */

        /**
         * initialise $questionInstance as an instance of the question type corresponding object class
         * that implements ilAsqQuestion depending on the given $questionType
         */

        return $questionInstance;
    }

    /**
     * @param integer $questionId
     * @return ilAsqQuestion
     */
    public function getOfflineExportableQuestionInstance($questionId, $a_image_path = null, $a_output_mode = 'presentation'): ilAsqQuestion
    {
        $questionInstance; /* @var ilAsqQuestion $questionInstance */

        /**
         * initialise $questionInstance as an instance of the question type corresponding object class
         * that implements ilAsqQuestion depending on the given $questionId
         */

        $questionInstance->setId($questionId);
        $questionInstance->load();

        $questionInstance->setOfflineExportImagePath($a_image_path);
        $questionInstance->setOfflineExportPagePresentationMode($a_output_mode);

        return $questionInstance;
    }

    /**
     * @param ilAsqQuestion $offlineExportableQuestionInstance
     * @return ilAsqQuestionOfflinePresentationExporter
     */
    public function getQuestionOfflinePresentationExporter(ilAsqQuestion $offlineExportableQuestionInstance)
    {
        $qstOffPresentationExporter; /* @var ilAsqQuestionOfflinePresentationExporter $qstOffPresentationExporter */

        /**
         * initialise $qstOffPresentationExporter as an instance of the question type corresponding
         * object class that implements ilAsqQuestionOfflinePresentationExporter
         * depending on the given $offlineExportableQuestionInstance
         */

        $qstOffPresentationExporter->setQuestion($offlineExportableQuestionInstance);

        return $qstOffPresentationExporter;
    }

    /**
     * @return ilAsqQuestionResourcesCollector
     */
    public function getQuestionResourcesCollector()
    {
        /**
         * this collector is able to manage all kind resources that aredepencies
         * for the offline presentation of a question (like js/css, media files, mobs).
         */

        return new ilAsqQuestionResourcesCollector();
    }

    /**
     * @param integer $questionId
     * @param integer $solutionId
     * @return ilAsqQuestionSolution
     */
    public function getQuestionSolutionInstance($questionId, $solutionId): ilAsqQuestionSolution
    {
        $questionSolutionInstance; /* @var ilAsqQuestionSolution $questionSolutionInstance */

        /**
         * initialise $questionSolutionInstance as an instance of the question type corresponding object class
         * that implements ilAsqQuestionSolution depending on the given $questionId and $solutionId
         */
        $questionSolutionInstance->setQuestionId($questionId);
        $questionSolutionInstance->setSolutionId($solutionId);
        $questionSolutionInstance->load();

        return $questionSolutionInstance;
    }

    /**
     * @param integer $questionId
     * @return ilAsqQuestionSolution
     */
    public function getEmptyQuestionSolutionInstance($questionId): ilAsqQuestionSolution
    {
        $emptySolutionInstance; /* @var ilAsqQuestionSolution $questionSolutionInstance */

        /**
         * initialise $emptySolutionInstance as an instance of the question type corresponding object class
         * that implements ilAsqQuestionSolution depending on the given $questionId
         */

        $emptySolutionInstance->setQuestionId($questionId);

        return $emptySolutionInstance;
    }

    /**
     * @param ilAsqQuestion $questionInstance
     * @param ilAsqQuestionSolution $solutionInstance
     * @return ilAsqResultCalculator
     */
    public function getResultCalculator(ilAsqQuestion $questionInstance, ilAsqQuestionSolution $solutionInstance): ilAsqResultCalculator
    {
        $resultCalculator; /* @var ilAsqResultCalculator $resultCalculator */

        /**
         * initialise $resultCalculator as an instance of the question type corresponding object class
         * that implements ilAsqResultCalculator depending on the given $questionInstance and $solutionInstance
         */

        $resultCalculator->setQuestion($questionInstance);
        $resultCalculator->setSolution($solutionInstance);

        return $resultCalculator;
    }
}
