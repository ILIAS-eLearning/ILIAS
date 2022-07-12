<?php declare(strict_types=1);

/**
 * A Trivial memory-based store - no support for tokens
 */
class TrivialOAuthDataStore extends OAuthDataStore
{
    private array $consumers = array();

    public function add_consumer($consumer_key, $consumer_secret) : void
    {
        $this->consumers[$consumer_key] = $consumer_secret;
    }

    public function lookup_consumer($consumer_key) : ?\OAuthConsumer
    {
        if (strpos($consumer_key, "http://") === 0) {
            return new OAuthConsumer($consumer_key, "secret", null);
        }
        if ($this->consumers[$consumer_key]) {
            return new OAuthConsumer($consumer_key, $this->consumers[$consumer_key], null);
        }
        return null;
    }

    public function lookup_token($consumer, $token_type, $token) : \OAuthToken
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
