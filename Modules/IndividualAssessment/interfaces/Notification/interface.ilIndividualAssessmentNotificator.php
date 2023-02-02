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

/**
 * Basic method collection should be implemented by a notificator
 * used by Individual assessment.
 */
interface ilIndividualAssessmentNotificator
{
    /**
     * Define the member, that should receive the message.
     */
    public function withReceiver(ilIndividualAssessmentMember $member): ilIndividualAssessmentNotificator;

    /**
     * Set message mode to failed.
     */
    public function withOccasionFailed(): ilIndividualAssessmentNotificator;

    /**
     * Set message mode to complete.
     */
    public function withOccasionCompleted(): ilIndividualAssessmentNotificator;

    /**
     * Send message.
     */
    public function send(): void;
}
