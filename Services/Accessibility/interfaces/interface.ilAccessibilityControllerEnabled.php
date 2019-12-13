<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAccessibilityControllerEnabled
 */
interface ilAccessibilityControllerEnabled
{
    /**
     * The implemented class should be ilCtrl enabled and execute or forward the given command
     */
    public function executeCommand() : void;
}
