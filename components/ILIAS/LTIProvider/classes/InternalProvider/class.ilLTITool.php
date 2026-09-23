<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ceLTIc\LTI\Context;
use ceLTIc\LTI\ResourceLink;
use ceLTIc\LTI\Tool;
use ceLTIc\LTI\User;
use ceLTIc\LTI\Util;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;

/**
 * LTI provider for LTI launch
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 */
class ilLTITool extends Tool
{
    /**
     * @var \ilLogger
     */
    protected ?\ilLogger $logger = null;

    /**
     * ilLTITool constructor.
     * @param ilLTIDataConnector $dataConnector
     */
    public function __construct(ilLTIDataConnector $dataConnector)
    {
        $this->logger = ilLoggerFactory::getLogger('ltis');
        //        $this->initialize();
        if (empty($dataConnector)) {
            $dataConnector = ilLTIDataConnector::getDataConnector();
        }
        $this->dataConnector = $dataConnector;
        //parent::__construct($dataConnector);
        $this->setParameterConstraint('resource_link_id', true, 50, array('basic-lti-launch-request'));
        $this->setParameterConstraint('user_id', true, 64, array('basic-lti-launch-request'));
        $this->setParameterConstraint('roles', true, null, array('basic-lti-launch-request'));
        $this->setParameterConstraint('lis_person_contact_email_primary', true, 80, array('basic-lti-launch-request'));
    }

    /**
     * Process a valid launch request
     */
    protected function onLaunch(): void
    {
        // save/update current user
        if ($this->userResult instanceof User) {
            $this->logger->debug("onLaunch - user");
            $this->userResult->save();
        }

        if ($this->context instanceof Context) {
            $this->logger->debug("onLaunch - context");
            $this->context->save();
        }

        if ($this->resourceLink instanceof ResourceLink) {
            $this->logger->debug("onLaunch - resource");
            $this->resourceLink->save();
        }
    }

    public function parsePostBody(ArrayBasedRequestWrapper $postData): array
    {
        global $DIC;
        $res = [];
        foreach ($postData->keys() as $key) {
            $res[$key] = $postData->retrieve($key, $DIC->refinery()->kindlyTo()->string());
        }
        return $res;
    }

    public function handleRequest(bool $strictMode = null, bool $disableCookieCheck = false, bool $generateWarnings = false): void
    {
        global $DIC;

        $_POST = $this->parsePostBody($DIC->http()->wrapper()->post());
        $_GET = $DIC->http()->request()->getQueryParams();

        if (isset($_POST['lti_version']) && $_POST['lti_version'] === 'LTI-1p0') {
            $_SERVER['REQUEST_URI'] = rtrim(preg_replace('/([?&])client_id=[^&]+(&|$)/', '$1', $_SERVER['REQUEST_URI']), '?&');
        }

        self::$authenticateUsingGet = true;

        // The LTI library ends the request itself once the message has been processed. ILIAS still
        // has to authenticate the user and forward them to the requested object, so the library is
        // asked to throw instead of exiting.
        $this->onExitExceptionClass = ilLTIExitException::class;

        try {
            parent::handleRequest($strictMode, $disableCookieCheck, $generateWarnings);
        } catch (ilLTIExitException $e) {
            $this->sendPendingResponse();
        }
    }

    /**
     * Send whatever the LTI library prepared for the platform before it tried to end the request.
     *
     * A redirect back to the platform carries an error message, an output body is either an error
     * page or one of the self submitting forms used for the browser storage and cookie checks. A
     * successfully processed launch leaves both of them empty, which is the only case where the
     * control flow returns to ILIAS.
     */
    private function sendPendingResponse(): void
    {
        if (!is_null($this->redirectUrl)) {
            Util::redirect($this->redirectUrl);
        }

        if (!is_null($this->output)) {
            echo $this->output;
            exit;
        }
    }
}
