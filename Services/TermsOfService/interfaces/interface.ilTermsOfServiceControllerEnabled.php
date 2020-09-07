<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceControllerEnabled
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceControllerEnabled
{
    /**
     * The implemented class should be \ilCtrl enabled and execute or forward the given command
     */
    public function executeCommand();
}
