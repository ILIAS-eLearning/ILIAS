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
 * Interface ilAsqQuestionPresentation
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components/ILIAS/AssessmentQuestion
 */
interface ilAsqQuestionOfflinePresentationExporter
{
    /**
     * @param ilAsqQuestion $questionInstance
     */
    public function setQuestion(ilAsqQuestion $questionInstance);

    /**
     * @param ilAsqQuestionResourcesCollector $resourcesCollector
     * @param bool $a_no_interaction
     * @return \ILIAS\UI\Component\Component
     */
    public function exportQuestion(ilAsqQuestionResourcesCollector $resourcesCollector, $a_no_interaction): \ILIAS\UI\Component\Component;
}
