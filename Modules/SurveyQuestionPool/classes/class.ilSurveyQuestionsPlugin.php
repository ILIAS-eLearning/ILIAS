<?php

/**
 * Abstract parent class for all question plugin classes.
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version $Id$
 *
 * @ingroup ServicesEventHandling
 */
abstract class ilSurveyQuestionsPlugin extends ilPlugin
{
    abstract public function getQuestionType();
}
