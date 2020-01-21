<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceRequestTargetAdjustmentCase
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceRequestTargetAdjustmentCase extends ilUserRequestTargetAdjustmentCase
{
    /**
     * @inheritdoc
     */
    public function shouldStoreRequestTarget() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isInFulfillment() : bool
    {
        return (
            strtolower($this->ctrl->getCmdClass()) === 'ilstartupgui' &&
            strtolower($this->ctrl->getCmd()) === 'getacceptance'
        );
    }

    /**
     * @inheritdoc
     */
    public function shouldAdjustRequest() : bool
    {
        if ($this->isInFulfillment()) {
            return false;
        }

        if (
            $this->user->hasToAcceptTermsOfService() &&
            $this->user->checkTimeLimit() &&
            $this->user->hasToAcceptTermsOfServiceInSession()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function adjust() : void
    {
        $this->ctrl->redirectToURL('ilias.php?baseClass=ilStartUpGUI&cmdClass=ilStartupGUI&cmd=getAcceptance');
    }
}
