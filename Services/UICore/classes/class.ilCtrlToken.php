<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlToken is responsible for generating and storing
 * unique CSRF tokens.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This class needs to be initialized with an instance of the
 * database access layer and a user object, as it generates
 * tokens on a per-user-basis.
 *
 * Note that this class only stores tokens in the database, as
 * the session would be an insecure place to do so. It does
 * however not remove them from it, as this should be done per
 * cron job.
 *
 * @TODO: implement cron-job that removes old tokens (<12h).
 */
final class ilCtrlToken implements ilCtrlTokenInterface
{
    /**
     * Holds the current session id.
     *
     * @var string
     */
    private string $sid;

    /**
     * Holds the user for whom a token should be generated
     * or validated.
     *
     * @var ilObjUser
     */
    private ilObjUser $user;

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
     * ilCtrlToken Constructor
     *
     * @param ilDBInterface $database
     * @param ilObjUser     $user
     */
    public function __construct(ilDBInterface $database, ilObjUser $user)
    {
        $this->user     = $user;
        $this->database = $database;
        $this->sid      = session_id();
    }

    /**
     * @inheritDoc
     */
    public function getToken() : string
    {
        if (!isset(self::$token)) {
            self::$token = $this->fetchToken() ?? $this->generateToken();
        }

        return self::$token;
    }

    /**
     * @inheritDoc
     */
    public function verifyWith(string $token) : bool
    {
        return ($token === $this->getToken());
    }

    /**
     * Generates a unique token and stores it in the database.
     *
     * @return string
     */
    private function generateToken() : string
    {
        // random_bytes() is cryptographically secure but
        // depends on the system it's running on. If the
        // generation fails, we use a less secure option
        // that is available for sure.

        try {
            $token = bin2hex(random_bytes(32));
        } catch (Throwable $t) {
            $token = md5(uniqid($this->user->getLogin(), true));
        }

        $this->storeToken($token);

        return $token;
    }

    /**
     * Returns a token from the database for the user of this instance.
     *
     * @return string|null
     */
    private function fetchToken() : ?string
    {
        $query_result = $this->database->fetchAssoc(
            $this->database->queryF(
                // @TODO: figure out, if ' AND stamp < {timestamp_before_12h};' or
                //        similar is needed.
                "SELECT token FROM il_request_token WHERE user_id = %s AND session_id = %s;",
                [
                    'integer',
                    'text',
                ],
                [
                    $this->user->getId(),
                    ($this->sid) ?: '',
                ]
            )
        );

        return $query_result['token'] ?? null;
    }

    /**
     * Stores the given token in the database for the user of this instance.
     *
     * @param string $token
     */
    private function storeToken(string $token) : void
    {
        $this->database->manipulateF(
            "INSERT INTO il_request_token (user_id, stamp, session_id, token) VALUES (%s, %s, %s, %s);",
            [
                'integer',
                'timestamp',
                'text',
                'text',
            ],
            [
                $this->user->getId(),
                $this->database->now(),
                ($this->sid) ?: '',
                $token,
            ]
        );
    }
}
