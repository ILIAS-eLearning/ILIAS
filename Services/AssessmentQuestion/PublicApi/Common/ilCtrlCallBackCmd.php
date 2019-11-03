<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Common;



/**
 * Class ilCtrlCallBackCmd
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>$
 */
class ilCtrlCallBackCmd
{
    /**
     * @var array $ctrl_stack
     */
    protected $ctrl_stack = [];
    /**
     * @var null|string
     */
    protected $command;

    public function __construct($ctrl_stack,$command) {
        $this->ctrl_stack = $ctrl_stack;
        $this->command = $command;
    }


    /**
     * @return array
     */
    public function getCtrlStack() : array
    {
        return $this->ctrl_stack;
    }


    /**
     * @return string|null
     */
    public function getCommand() : ?string
    {
        return $this->command;
    }
}
?>

