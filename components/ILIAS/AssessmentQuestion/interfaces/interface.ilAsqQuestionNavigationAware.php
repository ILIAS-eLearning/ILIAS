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
 * Interface ilAsqQuestionNavigationAware
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components/ILIAS/AssessmentQuestion
 */
interface ilAsqQuestionNavigationAware
{
    /**
     * @return \ILIAS\UI\Component\Component
     */
    public function getQuestionButtonsHTML(): \ILIAS\UI\Component\Component;

    /**
     * @return \ILIAS\UI\Component\Component
     */
    public function getQuestionPlayerActionsHTML(): \ILIAS\UI\Component\Component;

    /**
     * @return \ILIAS\UI\Component\Link\Link
     */
    public function getQuestionActionHandlingLink(): \ILIAS\UI\Component\Link\Link;
}
