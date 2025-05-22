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
        global $DIC;
        $this->logger = ilLoggerFactory::getLogger('ltis');
        //        $this->initialize();
        if (empty($dataConnector)) {
            $dataConnector = ilLTIDataConnector::getDataConnector();
        }
        $this->dataConnector = $dataConnector;
        //        parent::__construct($dataConnector);
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
}
