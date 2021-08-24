<?php

use ILIAS\Setup\Agent\NullAgent;

class ilTestQuestionPoolSetupAgent extends NullAgent
{
    public function getMigrations() : array
    {
        return [
            "8.0" => new TQP80Migration()
        ];
    }
}