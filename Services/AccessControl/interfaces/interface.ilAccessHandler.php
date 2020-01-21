<?php

/**
 * Interface ilAccessHandler
 *
 * This interface combines all available interfaces which can be called via global $ilAccess
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilAccessHandler extends ilRBACAccessHandler, ilOrgUnitPositionAccessHandler, ilOrgUnitPositionAndRBACAccessHandler
{
}
