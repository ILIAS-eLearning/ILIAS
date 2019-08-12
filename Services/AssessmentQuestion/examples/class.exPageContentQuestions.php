<?php
declare(strict_types=1);

/**
 * For components that needs to integrate the assessment question service in the way,
 * that questions act as independent assessment items client side, the former implementation
 * is mostly kept but has been moved to the assessment question service.
 *
 * Future visions tries to unify the assessment and the offline presentation
 * like known from the learning module, but up to now there is no technical concept available.
 * Existing visions for feature requests to support offline and/or cached test scenarios
 * will address the requirement of a presentation implementation that acts similar to the presentation
 * in the learning module, but connected to a qualified ajax backend for solution submissions.
 */
class exPageContentQuestions
{

    /**
     * exPageContentQuestions constructor.
     */
    public function __construct()
    {
        global $DIC;
        /* @var ILIAS\DI\Container $DIC */

        $this->question_processing = $DIC->assessment()->questionProcessing($DIC->user()->getId());

        //TODO
        //	$this->questions_ressource_collector =
    }


    /**
     * @param $a_no_interaction // enables a kind of preview mode
     * @param $a_mode           // currently required by content pages
     *
     * @return array an array with a htmloffline presentation per question
     */
    function getQuestionOfflinePresentations($a_no_interaction, $a_mode)
    {
        //TODO
    }
}