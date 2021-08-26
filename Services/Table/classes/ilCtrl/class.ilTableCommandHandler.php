<?php

use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Class ilTableCommandHandler
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilTableCommandHandler implements ilCtrlCommandHandler
{
    /**
     * Holds the current POST request wrapper.
     *
     * @var RequestWrapper
     */
    private RequestWrapper $request;

    /**
     * @var Refinery
     */
    private Refinery $refinery;

    /**
     * ilTableCommandHandler constructor.
     */
    public function __construct()
    {
        /**
         * @var $DIC \ILIAS\DI\Container
         */
        global $DIC;

        $this->request = $DIC->http()->wrapper()->post();
        $this->refinery = $DIC->refinery();
    }

    /**
     * @TODO: resolve direct $_POST and $_GET manipulations.
     *
     * @inheritdoc
     */
    public function handle(string $get_command, array $post_commands = null) : ?string
    {
        $command = null;

        if (null === $command && $this->request->has('table_top_cmd')) {
            $command = $this->request->retrieve(
                'table_top_cmd',
                $this->refinery->to()->string()
            );

            $_POST[$_POST["cmd_sv"][$command]] = $_POST[$_POST["cmd_sv"][$command] . "_2"];
        }

        if (null === $command && $this->request->has('select_cmd2')) {
            if ($this->request->has('select_cmd_all2')) {
                $_POST["select_cmd_all"] = $_POST["select_cmd_all2"];
            } else {
                $_POST["select_cmd_all"] = $_POST["select_cmd_all2"] = null;
            }

            $command = $this->request->retrieve(
                'select_cmd2',
                $this->refinery->to()->string()
            );
        }

        if (null === $command && $this->request->has('select_cmd')) {
            if ($this->request->has('select_cmd_all')) {
                $_POST["select_cmd_all2"] = $_POST["select_cmd_all"];
            } else {
                $_POST["select_cmd_all"] = $_POST["select_cmd_all2"] = null;
            }

            $command = $this->request->retrieve(
                'select_cmd',
                $this->refinery->to()->string()
            );
        }

        return $command;
    }
}