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

/**
 * A Trivial memory-based store - no support for tokens
 */
class TrivialOAuthDataStore extends \ILIAS\LTIOAuth\OAuthDataStore
{
    private array $consumers = array();

    public function add_consumer($consumer_key, $consumer_secret): void
    {
        $this->consumers[$consumer_key] = $consumer_secret;
    }

    public function lookup_consumer($consumer_key): ?ILIAS\LTIOAuth\OAuthConsumer
    {
        if (strpos($consumer_key, "http://") === 0) {
            return new \ILIAS\LTIOAuth\OAuthConsumer($consumer_key, "secret", null);
        }
        if ($this->consumers[$consumer_key]) {
            return new \ILIAS\LTIOAuth\OAuthConsumer($consumer_key, $this->consumers[$consumer_key], null);
        }
        return null;
    }

    public function lookup_token($consumer, $token_type, $token): \OAuthToken
    {
        return new OAuthToken($consumer, "");
    }

    // Return NULL if the nonce has not been used
    // Return $nonce if the nonce was previously used
    public function lookup_nonce($consumer, $token, $nonce, $timestamp)
    {
        // Should add some clever logic to keep nonces from
        // being reused - for no we are really trusting
        // that the timestamp will save us
        return null;
    }

    public function new_request_token($consumer, $callback = null)
    {
        return null;
    }

    public function new_access_token($token, $consumer, $verifier = null)
    {
        return null;
    }
}
