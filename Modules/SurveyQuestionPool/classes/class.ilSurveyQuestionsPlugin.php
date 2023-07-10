<?php

/**
 * Abstract parent class for all question plugin classes.
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @deprecated
 */
abstract class ilSurveyQuestionsPlugin extends ilPlugin
{
    abstract public function getQuestionType(): string;
}
