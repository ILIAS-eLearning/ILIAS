<?php declare(strict_types=1);

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
 
namespace ILIAS\CI\Rector\ReplaceWithDIC;

class Example
{
    public function __construct()
    {
    }
    
    protected function foo()
    {
        \ilUtil::sendFailure('my_text', true);
        \ilUtil::sendSuccess('my_text', true);
        \ilUtil::sendQuestion('my_text', true);
    }
    
    protected static function bar()
    {
        \ilUtil::sendInfo('my_text', true);
    }
}
