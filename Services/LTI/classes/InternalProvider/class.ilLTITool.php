<?php

declare(strict_types=1);

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

//use ILIAS\LTI\ToolProvider;
#use ILIAS\LTI\ToolProvider\DataConnector\DataConnector;
#use ilLTIDataConnector;
use ILIAS\LTI\ToolProvider\MediaType;
use ILIAS\LTI\ToolProvider\Profile;
use ILIAS\LTI\ToolProvider\Content\Item;
use ILIAS\LTI\ToolProvider\Jwt\Jwt;
use ILIAS\LTI\ToolProvider\Http\HTTPMessage;
use ILIAS\LTIOAuth;
use ILIAS\LTI\ToolProvider\ApiHook\ApiHook;
use ILIAS\LTI\ToolProvider\Util;
#use ILIAS\LTI\ToolProvider\OAuthDataStore;
//added
use ILIAS\LTI\ToolProvider\Context;
use ILIAS\LTI\ToolProvider\ResourceLink;
#use ILIAS\LTI\ToolProvider\User;
use ILIAS\LTI\ToolProvider\ResourceLinkShareKey;

#use ILIAS\LTI\Profile\Item;

#use ILIAS\LTI\Tool\MediaType;
#use ILIAS\LTI\Profile;

#use ILIAS\LTI\OAuth;


/**
 * LTI provider for LTI launch
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 */
class ilLTITool extends ILIAS\LTI\ToolProvider\Tool
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
        $this->setParameterConstraint('user_id', true, 50, array('basic-lti-launch-request'));
        $this->setParameterConstraint('roles', true, null, array('basic-lti-launch-request'));
    }

    /**
     * Process a valid launch request
     */
    protected function onLaunch(): void
    {
        // save/update current user
        if ($this->userResult instanceof User) {
            $this->userResult->save();
        }

        if ($this->context instanceof Context) {
            $this->context->save();
        }

        if ($this->resourceLink instanceof ResourceLink) {
            $this->resourceLink->save();
        }
    }
}
