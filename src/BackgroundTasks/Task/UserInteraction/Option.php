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
 
namespace ILIAS\BackgroundTasks\Task\UserInteraction;

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
