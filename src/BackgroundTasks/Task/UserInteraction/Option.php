<?php

namespace ILIAS\BackgroundTasks\Task\UserInteraction;

/**
 * Interface Option
 * @package ILIAS\BackgroundTasks\Task
 *          Whenever a user is asked about the further course of his tasks (userinteraction),
 *          options will show up
 */
interface Option
{
    
    /**
     * @return string
     */
    public function getLangVar();
    
    public function setLangVar(string $lang_var);
    
    /**
     * @return string
     */
    public function getValue();
    
    public function setValue(string $value);
}
