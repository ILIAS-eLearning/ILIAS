<?php declare (strict_types=1);

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
