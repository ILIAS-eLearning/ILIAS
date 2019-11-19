<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Repos for classification session data
 *
 * @author killing@leifos.de
 */
class ilClassificationSessionRepository
{
    const BASE_SESSION_KEY = 'clsfct';

    /**
     * @var int
     */
    protected $base_ref_id;

    /**
     * ilClassificationSessionRepository constructor.
     * @param int $base_ref_id
     */
    public function __construct(int $base_ref_id)
    {
        $this->base_ref_id = $base_ref_id;
    }

    /**
     * Unset all
     */
    public function unsetAll()
    {
        unset($_SESSION[self::BASE_SESSION_KEY][$this->base_ref_id]);
    }

    /**
     * @param string $provider
     */
    public function unsetValueForProvider(string $provider)
    {
        unset($_SESSION[self::BASE_SESSION_KEY][$this->base_ref_id][$provider]);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return !isset($_SESSION[self::BASE_SESSION_KEY][$this->base_ref_id]);
    }

    /**
     * @param string $provider
     * @return mixed
     */
    public function getValueForProvider(string $provider)
    {
        return $_SESSION[self::BASE_SESSION_KEY][$this->base_ref_id][$provider];
    }

    /**
     * @param $provider
     * @param $value
     */
    public function setValueForProvider($provider, $value)
    {
        $_SESSION[self::BASE_SESSION_KEY][$this->base_ref_id][$provider] = $value;
    }



}