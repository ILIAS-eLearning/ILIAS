<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction;

use ILIAS\BackgroundTasks\Task\UserInteraction\Option;

class UserInteractionOption implements Option
{

    /**
     * @var string
     */
    protected $lang_var;
    /**
     * @var
     */
    protected $value;


    /**
     * UserInteractionOption constructor.
     *
     * @param string $lang_var
     * @param        $value
     */
    public function __construct($lang_var, $value)
    {
        $this->lang_var = $lang_var;
        $this->value = $value;
    }


    /**
     * @return string
     */
    public function getLangVar()
    {
        return $this->lang_var;
    }


    /**
     * @param string $lang_var
     */
    public function setLangVar($lang_var)
    {
        $this->lang_var = $lang_var;
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
