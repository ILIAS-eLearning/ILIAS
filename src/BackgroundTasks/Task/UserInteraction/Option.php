<?php

namespace ILIAS\BackgroundTasks\Task\UserInteraction;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Interface Option
 * @package ILIAS\BackgroundTasks\Task
 *          Whenever a user is asked about the further course of his tasks (userinteraction),
 *          options will show up
 */
interface Option
{
    public function getLangVar() : string;
    
    public function setLangVar(string $lang_var);
    
    public function getValue() : string;
    
    public function setValue(string $value);
}
