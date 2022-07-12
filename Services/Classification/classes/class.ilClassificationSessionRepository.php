<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Repos for classification session data
 * @author Alexander Killing <killing@leifos.de>
 */
class ilClassificationSessionRepository
{
    public const BASE_SESSION_KEY = 'clsfct';
    protected string $key;

    protected int $base_ref_id;

    public function __construct(int $base_ref_id)
    {
        $this->base_ref_id = $base_ref_id;
        $this->key = self::BASE_SESSION_KEY . "_" . $base_ref_id;
    }

    public function unsetAll() : void
    {
        ilSession::clear($this->key);
    }

    public function unsetValueForProvider(string $provider) : void
    {
        if (ilSession::has($this->key)) {
            $vals = ilSession::get($this->key);
            unset($vals[$provider]);
            ilSession::set($this->key, $vals);
        }
    }

    public function isEmpty() : bool
    {
        return !ilSession::has($this->key);
    }

    public function getValueForProvider(string $provider) : array
    {
        if (ilSession::has($this->key)) {
            $vals = ilSession::get($this->key);
            return $vals[$provider] ?? [];
        }
        return [];
    }

    public function setValueForProvider(string $provider, array $value) : void
    {
        $vals = [];
        if (ilSession::has($this->key)) {
            $vals = ilSession::get($this->key);
        }
        $vals[$provider] = $value;
        ilSession::set($this->key, $vals);
    }
}
