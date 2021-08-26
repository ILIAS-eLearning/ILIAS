<?php

/**
 * Interface ilCtrlCommandHandler is responsible for exceptional
 * command manipulations.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This interface describes how a command handler must look like. It's
 * Lifespan is temporarily though, because with the recent refactoring
 * of ilCtrl, several $_POST and $_GET manipulations were made directly
 * within @see ilCtrlInterface::getCmd() which will be prohibited in
 * the future. Therefore this method now accepts an additional Handler
 * of this interface for the transitioning phase.
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