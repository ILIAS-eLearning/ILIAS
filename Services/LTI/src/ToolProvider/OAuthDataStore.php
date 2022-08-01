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


namespace ILIAS\LTI\ToolProvider;

use ILIAS\LTIOAuth;
use ILIAS\LTIOAuth\OAuthConsumer;
use ILIAS\LTIOAuth\OAuthToken;
use ILIAS\LTIOAuth\OAuthException;

/**
 * Class to represent an OAuth datastore
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class OAuthDataStore extends LTIOAuth\OAuthDataStore
{

    /**
     * System object.
     *
     * @var Tool|Platform|null $system
     */
    private $system = null;

    /**
     * Class constructor.
     *
     * @param Tool|Platform $system System object
     */
    public function __construct($system)
    {
        $this->system = $system;
    }

    /**
     * Create an OAuthConsumer object for the system.
     * @param string $consumerKey Consumer key value
     * @return OAuthConsumer OAuthConsumer object
     */
    public function lookup_consumer(string $consumerKey) : OAuthConsumer
    {
        $key = $this->system->getKey();
        $secret = '';
        if (!empty($key)) {
            $secret = $this->system->secret;
        } elseif (($this->system instanceof Tool) && !empty($this->system->platform)) {
            $key = $this->system->platform->getKey();
            $secret = $this->system->platform->secret;
        } elseif (($this->system instanceof Platform) && !empty(Tool::$defaultTool)) {
            $key = Tool::$defaultTool->getKey();
            $secret = Tool::$defaultTool->secret;
        }
        if ($key !== $consumerKey) {
            throw new OAuthException('Consumer key not found');
        }

        return new OAuthConsumer($key, $secret);
    }

    /**
     * Create an OAuthToken object for the system.
     * @param OAuthConsumer $consumer  OAuthConsumer object //UK: removed string
     * @param string $tokenType Token type
     * @param string $token     Token value
     * @return OAuthToken OAuthToken object
     */
    public function lookup_token(OAuthConsumer $consumer, string $tokenType, string $token) : OAuthToken
    {
        return new OAuthToken($consumer, '');
    }

    /**
     * Lookup nonce value for the system.
     * @param OAuthConsumer   $consumer  OAuthConsumer object
     * @param OAuthToken|null $token     Token value //UK: removed string
     * @param string          $value     Nonce value
     * @param int             $timestamp Date/time of request //UK: removed string
     * @return bool    True if the nonce value already exists
     */
    public function lookup_nonce(OAuthConsumer $consumer, ?OAuthToken $token, string $value, int $timestamp) : bool
    {
        if ($this->system instanceof Platform) {
            $platform = $this->system;
        } else {
            $platform = $this->system->platform;
        }
        $nonce = new PlatformNonce($platform, $value);
        $ok = !$nonce->load();
        if ($ok) {
            $ok = $nonce->save();
        }
        if (!$ok) {
            $this->system->reason = 'Invalid nonce.';
        }

        return !$ok;
    }

    /**
     * Get new request token.
     * @param OAuthConsumer $consumer OAuthConsumer object
     * @param mixed        $callback Callback URL //UK: removed string CHECK
     * @return string Null value
     */
    public function new_request_token(OAuthConsumer $consumer, $callback = null) : ?string
    {
        return null;
    }

    /**
     * Get new access token.
     * @param OAuthToken    $token    Token value //UK: removed string CHECK
     * @param OAuthConsumer $consumer OAuthConsumer object
     * @param string        $verifier Verification code
     * @return string Null value
     */
    public function new_access_token(OAuthToken $token, OAuthConsumer $consumer, $verifier = null) : ?string
    {
        return null;
    }
}
