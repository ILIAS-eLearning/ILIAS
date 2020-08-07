<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

/**
 * Class ilTermsOfServiceRequestTargetAdjustmentCase
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceRequestTargetAdjustmentCase extends ilUserRequestTargetAdjustmentCase
{
    /** @var Container */
    private $dic;

    /**
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }

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
            strtolower($this->dic->ctrl()->getCmdClass()) === 'ilstartupgui' &&
            strtolower($this->dic->ctrl()->getCmd()) === 'getacceptance'
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

        if (!$this->dic->user()->hasToAcceptTermsOfServiceInSession()) {
            return false;
        }

        if ($this->dic->user()->checkTimeLimit()) {
            if ($this->dic->user()->hasToAcceptTermsOfService()) {
                return true;
            }

            /** @var ilTermsOfServiceHelper $tosService */
            $tosService = $this->dic['tos.service'];
            if ($tosService->hasToResignAcceptance($this->dic->user(), $this->dic->logger()->tos())) {
                $tosService->resetAcceptance($this->dic->user());
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function adjust() : void
    {
        $this->dic->ctrl()->redirectToURL('ilias.php?baseClass=ilStartUpGUI&cmdClass=ilStartupGUI&cmd=getAcceptance');
    }
}
