<?php

/**
 * Class ilCtrlToken
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlToken implements ilCtrlTokenInterface
{
    /**
     * Holds the user id for whom a token should be generated
     * or validated.
     *
     * @var int
     */
    private int $user_id;

    /**
     * Holds an instance of a random generator.
     *
     * @var ilRandom
     */
    private ilRandom $random;

    /**
     * Holds an instance of the database access layer.
     *
     * @var ilDBInterface
     */
    private ilDBInterface $database;

    /**
     * Holds a temporarily generated token.
     *
     * @var string
     */
    private static string $token;

    /**
     * Constructor
     *
     * @param ilDBInterface $database
     * @param ilObjUser     $user
     */
    public function __construct(ilDBInterface $database, ilObjUser $user)
    {
        $this->database = $database;
        $this->user_id  = $user->getId();
        $this->random   = new ilRandom();

        $this->maybeDeleteOldTokens();
    }

    /**
     * @inheritDoc
     */
    public function verify(string $token) : bool
    {
        if ($token !== $this->get()) {
            return false;
        }

        $this->maybeDeleteOldTokens(true);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function get() : string
    {
        if (isset(self::$token)) {
            return self::$token;
        }

        if (0 < $this->user_id && ANONYMOUS_USER_ID !== $this->user_id) {
            self::$token = $this->fetchToken() ?? $this->createToken();
            $this->storeToken(self::$token);

            return self::$token;
        }

        return 'anonymous';
    }

    /**
     * Returns a temporarily stored token or null.
     *
     * @return string|null
     */
    private function fetchToken() : ?string
    {
        $query_result = $this->database->fetchAssoc(
            $this->database->queryF(
                "SELECT token FROM il_request_token WHERE user_id = %s AND session_id = %s;",
                ['integer', 'text'],
                [$this->user_id, session_id()]
            )
        );

        return $query_result['token'] ?? null;
    }

    /**
     * Temporarily stores a given token in the database.
     *
     * @param string $token
     */
    private function storeToken(string $token) : void
    {
        $this->database->manipulateF(
            "INSERT INTO il_request_token (user_id, token, stamp, session_id) VALUES (%s, %s, %s, %s);",
            [
                'integer',
                'text',
                'timestamp',
                'text',
            ],
            [
                $this->user_id,
                $token,
                $this->database->now(),
                session_id(),
            ]
        );
    }

    /**
     * Removes old tokens from the database.
     *
     * The removal runs randomly, but can be forced with the 'true'
     * as an optional parameter.
     *
     * @param bool $force
     */
    private function maybeDeleteOldTokens(bool $force = false) : void
    {
        if ($force || 42 === $this->random->int(1, 200)) {
            // according to bug #13551 the current token must not be removed
            // immediately from the database, therefore only old(er) ones are
            // removed right now.
            $datetime = new ilDateTime(time(), IL_CAL_UNIX);
            $datetime->increment(IL_CAL_DAY, -1);
            $datetime->increment(IL_CAL_HOUR, -12);

            $this->database->manipulateF(
                "DELETE FROM il_request_token WHERE stamp < %s;",
                ['timestamp'],
                [$datetime->get(IL_CAL_TIMESTAMP)]
            );
        }
    }

    /**
     * Returns a uniquely generated token.
     *
     * @return string
     */
    private function createToken() : string
    {
        try {
            $token = md5(uniqid($this->random->int(), true));
        } catch (Throwable) {
            $token = md5(uniqid(time(), true));
        }

        return $token;
    }
}