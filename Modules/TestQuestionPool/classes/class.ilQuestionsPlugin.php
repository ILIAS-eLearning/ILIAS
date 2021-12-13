<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * Abstract parent class for all question plugin classes.
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version $Id$
 *
 * @ingroup ServicesEventHandling
 */
abstract class ilQuestionsPlugin extends ilPlugin
{
    abstract public function getQuestionType();

    abstract public function getQuestionTypeTranslation() : string;
}
