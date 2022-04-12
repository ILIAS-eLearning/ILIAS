<?php

namespace ILIAS\LTI\ToolProvider;

use ILIAS\LTIOAuth;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
#require_once dirname(__DIR__, 4) . "/Modules/LTIConsumer/lib/OAuth.php";
#require_once dirname(__DIR__, 2) . "/src/OAuth/OAuth.php";
class OAuthDataStore extends LTIOAuth\OAuthDataStore
{

/**
     * Tool Provider object.
     */
    private ?\ILIAS\LTI\ToolProvider\ToolProvider $toolProvider = null;

    /**
     * Class constructor.
     * @param ToolProvider $toolProvider Tool_Provider object
     */
    public function __construct(ToolProvider $toolProvider)
    {
        $this->toolProvider = $toolProvider;
    }

    /**
     * Create an OAuthConsumer object for the tool consumer.
     *
     * @param string $consumerKey Consumer key value
     *
     * @return \ILIAS\LTIOAuth\OAuthConsumer OAuthConsumer object
     */
    public function lookup_consumer(string $consumerKey) : \ILIAS\LTIOAuth\OAuthConsumer
    {
        return new \ILIAS\LTIOAuth\OAuthConsumer(
            $this->toolProvider->consumer->getKey(),
            $this->toolProvider->consumer->secret
        );
    }

    /**
     * Create an OAuthToken object for the tool consumer.
     *
     * @param string $consumer   OAuthConsumer object
     * @param string $tokenType  Token type
     * @param string $token      Token value
     *
     * @return \ILIAS\LTIOAuth\OAuthToken OAuthToken object
     */
    public function lookup_token($consumer, string $tokenType, string $token) : \ILIAS\LTIOAuth\OAuthToken
    {
        return new \ILIAS\LTIOAuth\OAuthToken($consumer, '');
    }

    /**
     * Lookup nonce value for the tool consumer.
     * @param \ILIAS\LTIOAuth\OAuthConsumer $consumer  OAuthConsumer object
     * @param LTIOAuth\OAuthToken|null      $token
     * @param string                        $value     Nonce value
     * @param int                        $timestamp Date/time of request
     * @return boolean True if the nonce value already exists
     */
    public function lookup_nonce(\ILIAS\LTIOAuth\OAuthConsumer $consumer, ?\ILIAS\LTIOAuth\OAuthToken $token, string $value, int $timestamp) : bool
    {
        $nonce = new ConsumerNonce($this->toolProvider->consumer, $value);
        $ok = !$nonce->load();
        if ($ok) {
            $ok = $nonce->save();
        }
        if (!$ok) {
            $this->toolProvider->reason = 'Invalid nonce.';
        }

        return !$ok;
    }

    /**
     * Get new request token.
     *
     * @param \ILIAS\LTIOAuth\OAuthConsumer $consumer  OAuthConsumer object
     * @param string        $callback  Callback URL
     *
     * @return string Null value
     */
    public function new_request_token(\ILIAS\LTIOAuth\OAuthConsumer $consumer, ?string $callback = null) : ?string
    {
        return null;
    }

    /**
     * Get new access token.
     * @param string                        $token    Token value
     * @param \ILIAS\LTIOAuth\OAuthConsumer $consumer OAuthConsumer object
     * @param string|null                   $verifier Verification code
     * @return string Null value
     */
    public function new_access_token($token, $consumer, string $verifier = null) : ?string
    {
        return null;
    }
}
