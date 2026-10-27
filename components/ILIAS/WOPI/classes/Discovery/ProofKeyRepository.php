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

namespace ILIAS\WOPI\Discovery;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ProofKeyRepository
{
    private const MODULUS = 'wopi_proof_modulus';
    private const EXPONENT = 'wopi_proof_exponent';
    private const OLD_MODULUS = 'wopi_proof_old_modulus';
    private const OLD_EXPONENT = 'wopi_proof_old_exponent';

    public function __construct(
        private \ilSetting $settings
    ) {
    }

    public function get(): ?ProofKeys
    {
        $modulus = $this->settings->get(self::MODULUS);
        $exponent = $this->settings->get(self::EXPONENT);
        if (empty($modulus) || empty($exponent)) {
            return null;
        }
        $old_modulus = $this->settings->get(self::OLD_MODULUS);
        $old_exponent = $this->settings->get(self::OLD_EXPONENT);

        return new ProofKeys(
            new ProofKey($modulus, $exponent),
            empty($old_modulus) || empty($old_exponent) ? null : new ProofKey($old_modulus, $old_exponent)
        );
    }

    /**
     * Stores the keys of the latest discovery, null removes them (the client offers none).
     */
    public function store(?ProofKeys $keys): void
    {
        if ($keys === null) {
            $this->clear();
            return;
        }
        $this->settings->set(self::MODULUS, $keys->current->modulus);
        $this->settings->set(self::EXPONENT, $keys->current->exponent);
        if ($keys->old === null) {
            $this->settings->delete(self::OLD_MODULUS);
            $this->settings->delete(self::OLD_EXPONENT);
            return;
        }
        $this->settings->set(self::OLD_MODULUS, $keys->old->modulus);
        $this->settings->set(self::OLD_EXPONENT, $keys->old->exponent);
    }

    public function clear(): void
    {
        foreach ([self::MODULUS, self::EXPONENT, self::OLD_MODULUS, self::OLD_EXPONENT] as $key) {
            $this->settings->delete($key);
        }
    }
}
