<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\Messaging\Contract\Command;

/**
 * Interface for commands.
 *
 * A command is a strictly defined message. The Command is not more
 * than a Data Transfer Object which can be used by the Command
 * Handler. It represents the outside request structured in a well
 * formalized way. The command is not repsonsible for handle the
 * command!
 *
 * Commands are handled by exactly one CommandHandler
 */
interface Event {

}