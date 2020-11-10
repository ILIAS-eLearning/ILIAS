<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\Init\StartupSequence\StartUpSequenceStep;

/**
 * Class ilTermsOfServiceAcceptanceStartUpStep
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceStartUpStep extends StartUpSequenceStep
{
    /** @var Container */
    private $dic;

    /**
     * ilTermsOfServiceAcceptanceStartUpStep constructor.
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
        $cmd = $this->dic->ctrl()->getCmd();
        return (
            strtolower($this->dic->ctrl()->getCmdClass()) === 'ilstartupgui' &&
            (
                strtolower($this->dic->ctrl()->getCmd()) === 'getacceptance' ||
                strtolower($this->dic->ctrl()->getCmd()) === 'confirmacceptance' ||
                strtolower($this->dic->ctrl()->getCmd()) === 'confirmwithdrawal'
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function shouldInterceptRequest() : bool
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
    public function execute() : void
    {
        $this->dic->ctrl()->redirectToURL('ilias.php?baseClass=ilStartUpGUI&cmdClass=ilStartupGUI&cmd=getAcceptance');
    }
}
