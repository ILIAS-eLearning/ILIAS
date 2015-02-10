<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("./Services/Exceptions/classes/class.ilException.php");

/**
 * Exception is thrown when invariants on the program tree would be violated
 * by manipulation of tree.
 */
class ilTrainingProgrammeTreeException extends ilException {
}