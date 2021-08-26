<?php

/**
 * Interface ilCtrlCommandHandler is responsible for exceptional
 * command determinations.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This interface describes how a command-handler must look like. It's
 * only purpose is to handle exceptional command determination cases
 * temporarily, until $_POST and $_GET modifications are prohibited
 * altogether. This is mostly in regards to @see ilTable2GUI of which
 * $_POST manipulations were made within ilCtrl itself.
 *
 * @see ilCtrlInterface::getCmd() now accepts ilCtrlCommandHandler's
 *                                as an optional parameter.
 */
interface ilCtrlCommandHandler
{
    /**
     * This method MUST either return NULL or the determined command.
     * If NULL is returned, getCmd() will use $a_fallback_cmd as return.
     *
     * @param string                     $get_command
     * @param array<string, string>|null $post_commands
     * @return string|null
     */
    public function handle(string $get_command, array $post_commands = null) : ?string;
}