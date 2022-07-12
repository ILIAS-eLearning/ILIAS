<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
namespace ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction;

use ILIAS\BackgroundTasks\Task\UserInteraction\Option;

class UserInteractionOption implements Option
{
    protected string $lang_var;
    protected string $value;
    
    /**
     * UserInteractionOption constructor.
     */
    public function __construct(string $lang_var, string $value)
    {
        $this->lang_var = $lang_var;
        $this->value = $value;
    }
    

    public function getLangVar() : string
    {
        return $this->lang_var;
    }
    
    public function setLangVar(string $lang_var) : void
    {
        $this->lang_var = $lang_var;
    }
    

    public function getValue() : string
    {
        return $this->value;
    }
    

    public function setValue(string $value) : void
    {
        $this->value = $value;
    }
}
