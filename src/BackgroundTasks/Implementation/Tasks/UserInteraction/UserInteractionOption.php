<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction;

use ILIAS\BackgroundTasks\Task\UserInteraction\Option;

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
class UserInteractionOption implements Option
{
    protected string $lang_var;
    /**
     * @var
     */
    protected $value;
    
    /**
     * UserInteractionOption constructor.
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
    
    public function setLangVar(string $lang_var)
    {
        $this->lang_var = $lang_var;
    }
    
    /**
     * @return mixed|string
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * @param mixed $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }
}
