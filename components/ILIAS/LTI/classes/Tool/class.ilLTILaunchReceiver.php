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

use ceLTIc\LTI\Enum\LtiVersion;
use ceLTIc\LTI\Tool;
use ceLTIc\LTI\Util;
use Random\RandomException;

/**
 * ILIAS as the tool a platform launches: celtic/lti checks the launch, whatever LTI version it comes
 * in, and stores the platform, context, resource link and user result of it. A launch the library
 * rejects ends there, reported back to the platform by the library.
 *
 * The library reads the launch from the request itself, which is why it runs before ILIAS takes over.
 *
 * A Deep Linking request of LTI Advantage arrives here too, and so do the objects an instructor picked for it:
 * see ilLTIAdvantageToolDeepLinking. Neither logs anybody in.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTILaunchReceiver extends Tool
{
    private const string MESSAGE_LAUNCH = 'basic-lti-launch-request';

    public function __construct(ilLTIDataConnector $data_connector)
    {
        parent::__construct($data_connector);

        // What ILIAS needs to create the user of the launch, as long as the columns that keep the ids. The name,
        // the email and the roles are optional: platforms leave them out for privacy, and LTI Advantage sends
        // no role for a user without one in the context.
        $this->setParameterConstraint('resource_link_id', true, 255, [self::MESSAGE_LAUNCH]);
        $this->setParameterConstraint('user_id', true, 250, [self::MESSAGE_LAUNCH]);
    }

    /**
     * Checks and stores the launch of the current request.
     *
     * @throws RandomException
     */
    public function receive(): void
    {
        global $DIC;

        $request = $DIC->http()->request();
        $deep_linking_token = $request->getQueryParams()[ilLTIAdvantageToolDeepLinking::TOKEN_PARAM] ?? null;
        if (is_string($deep_linking_token)) {
            new ilLTIAdvantageToolDeepLinking()->respond($deep_linking_token);
        }

        // the library reads $_GET and $_POST unless it is handed the parameters, and lti.php changes both
        $body = $request->getParsedBody();
        // lti.php hides the client id of an OpenID Connect login from ILIAS, which would take it for the id
        // of its own client, so the query of such a login is read as it came
        parse_str($request->getUri()->getQuery(), $query);
        if (!isset($query['iss'])) {
            $query = $request->getQueryParams();
        }
        Util::$requestParameters = array_merge($query, is_array($body) ? $body : []);

        if ((Util::$requestParameters['lti_version'] ?? '') === LtiVersion::V1->value) {
            ilLTI1p1ProviderLaunchRequestUri::stripClientId();
        } else {
            $this->signAsIlias();
        }

        // the OpenID Connect login of LTI Advantage goes back to the platform by GET, which carries the cookies
        // of the platform that SameSite=Lax holds back from a POST, as in earlier releases
        self::$authenticateUsingGet = true;
        $this->handleRequest();
    }

    /**
     * What ILIAS sends to an LTI Advantage platform is signed with its key, the requests for the access
     * tokens of the platform services included, which the library signs as its default tool. Checking the
     * launch does not need it, so a missing key only stops what is sent later.
     *
     * @throws RandomException
     */
    private function signAsIlias(): void
    {
        try {
            ilLTIAdvantageKeyPair::applyTo($this);
            Tool::$defaultTool = $this;
        } catch (ilException $e) {
            global $DIC;

            $DIC->logger()->forComponent('lti')->error($e->getMessage());
        }
    }

    /**
     * The library has stored all there is to store when it calls this: ILIAS takes over afterwards.
     */
    protected function onLaunch(): void
    {
        $this->ok = true;
    }

    /**
     * @throws RandomException
     */
    protected function onContentItem(): void
    {
        ilLTIAdvantageToolDeepLinking::showSelection($this, $this->contentTypes ?? []);
    }
}
