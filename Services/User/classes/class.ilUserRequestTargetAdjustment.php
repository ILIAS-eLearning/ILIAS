<?php declare(strict_types=1);

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

/**
 * Class ilUserAccountMaintenanceEnforcement
 */
class ilUserRequestTargetAdjustment
{
    /** @var Container */
    private $dic;
    /** @var ilUserRequestTargetAdjustmentCase[] */
    private $cases = [];

    /**
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
        $this->initCases();
    }

    /**
     *
     */
    protected function initCases() : void
    {
        $this->cases = [
            new ilTermsOfServiceRequestTargetAdjustmentCase(
                $this->dic
            ),
            new ilUserProfileIncompleteRequestTargetAdjustmentCase(
                $this->dic->user(),
                $this->dic->ctrl()
            ),
            new ilUserPasswordResetRequestTargetAdjustmentCase(
                $this->dic->user(),
                $this->dic->ctrl(),
                $this->dic->http()->request()
            ),
        ];
    }

    /**
     *
     */
    protected function storeRequest() : void
    {
        if (!ilSession::get('orig_request_target')) {
            //#16324 don't use the complete REQUEST_URI
            $url = substr($_SERVER['REQUEST_URI'], (strrpos($_SERVER['REQUEST_URI'], '/') + 1));

            ilSession::set('orig_request_target', $url);
        }
    }

    /**
     * @return boolean
     */
    public function adjust() : bool
    {
        $this->dic->logger()->init()->debug('Started request interception checks ...');

        if (defined('IL_CERT_SSO')) {
            $this->dic->logger()->init()->debug('ApacheAuthentication request. No adjustment.');
            return false;
        } elseif (!ilContext::supportsRedirects()) {
            $this->dic->logger()->init()->debug('Context does not support redirects. No adjustment.');
            return false;
        } elseif ($this->dic->ctrl()->isAsynch()) {
            $this->dic->logger()->init()->debug('Async request. No adjustment.');
            return false;
        } elseif (in_array(basename($_SERVER['PHP_SELF']), array('logout.php'))) {
            $this->dic->logger()->init()->debug('Logout request. No adjustment.');
            return false;
        } elseif (!$this->dic->user()->getId() || $this->dic->user()->isAnonymous()) {
            $this->dic->logger()->init()->debug('Anonymous request. No adjustment.');
            return false;
        } elseif (ilSession::get(__CLASS__ . '_passed')) {
            $this->dic->logger()->init()->debug(__CLASS__ . ' already passed in the current user session.');
            return false;
        }

        foreach ($this->cases as $case) {
            if ($case->isInFulfillment()) {
                $this->dic->logger()->init()->debug('Case is in fulfillment:' . get_class($case));
                return false;
            }

            if ($case->shouldAdjustRequest()) {
                $this->dic->logger()->init()->debug('Case required adjustment:' . get_class($case));
                if ($case->shouldStoreRequestTarget()) {
                    $this->storeRequest();
                }
                $case->adjust();
                return true;
            }
        }

        ilSession::set(__CLASS__ . '_passed', 1);
        return false;
    }
}
