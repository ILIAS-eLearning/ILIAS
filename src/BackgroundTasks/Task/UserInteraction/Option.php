<?php

namespace ILIAS\BackgroundTasks\Task\UserInteraction;

/**
 * Interface Option
 *
 * @package ILIAS\BackgroundTasks\Task
 *
 *          Whenever a user is asked about the further course of his tasks (userinteraction),
 *          options will show up
 */
interface Option
{

    /**
     * @return string
     */
    public function getLangVar();


    /**
     * @param string $lang_var
     */
    public function setLangVar($lang_var);


    /**
     * @return string
     */
    public function getValue();


    /**
     * @param string $value
     */
    public function setValue($value);
}