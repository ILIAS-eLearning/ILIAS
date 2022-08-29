<?php

declare(strict_types=1);

/* Copyright (c) 2018 - Denis Klöpfer <denis.kloepfer@concepts-and-training.de> - Extended GPL, see LICENSE */

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
