<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\Init\StartupSequence\StartUpSequenceStep;

/**
 * Class ilTermsOfServiceWithdrawalStartUpStep
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceWithdrawalStartUpStep extends StartUpSequenceStep
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

        $a = $this->dic->ctrl()->getCmdClass();
        $b = $this->dic->ctrl()->getCmd();
        return (
            strtolower($this->dic->ctrl()->getCmdClass()) === 'ilpersonalprofilegui' && (
                strtolower($this->dic->ctrl()->getCmd()) === 'showuseragreement'  ||
                strtolower($this->dic->ctrl()->getCmd()) === 'confirmwithdrawal'  ||
                strtolower($this->dic->ctrl()->getCmd()) === 'showconsentwithdrawalconfirmation'  ||
                strtolower($this->dic->ctrl()->getCmd()) === 'cancelwithdrawal'  ||
                strtolower($this->dic->ctrl()->getCmd()) === 'withdrawacceptance'  ||
                strtolower($this->dic->ctrl()->getCmd()) === 'rejectwithdrawal'
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

        if ($this->dic->user()->getPref('consent_withdrawal_requested')) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function execute() : void
    {
        $this->dic->ctrl()->redirectByClass(
            [ilDashboardGUI::class, ilPersonalProfileGUI::class],
            'showConsentWithdrawalConfirmation'
        );
    }
}