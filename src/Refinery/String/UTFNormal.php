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

namespace ILIAS\Refinery\String;

use ILIAS\Refinery\Transformation;
use Symfony\Polyfill\Intl\Normalizer\Normalizer as PolyfillNormalizer;
use Normalizer as NativeNormalizer;

class UTFNormal
{
    protected ?Transformation $form_c = null;
    protected ?Transformation $form_d = null;
    protected ?Transformation $form_kc = null;
    protected ?Transformation $form_kd = null;

    /**
     * Normalization Form C (NFC), also known as Canonical Decomposition followed by Canonical Composition.
     */
    public function formC(): Transformation
    {
        if ($this->form_c === null) {
            $this->form_c = $this->getNormalizer(NativeNormalizer::FORM_C);
        }

        return $this->form_c;
    }

    /**
     * Normalization Form D (NFD), also known as Canonical Decomposition.
     */
    public function formD(): Transformation
    {
        if ($this->form_d === null) {
            $this->form_d = $this->getNormalizer(NativeNormalizer::FORM_D);
        }

        return $this->form_d;
    }

    /**
     * Normalization Form KC (NFKC), also known as Compatibility Decomposition followed by Canonical Composition.
     */
    public function formKD(): Transformation
    {
        if ($this->form_kd === null) {
            $this->form_kd = $this->getNormalizer(NativeNormalizer::FORM_KD);
        }

        return $this->form_kd;
    }

    /**
     * Normalization Form KD (NFKD), also known as Compatibility Decomposition.
     */
    public function formKC(): Transformation
    {
        if ($this->form_kc === null) {
            $this->form_kc = $this->getNormalizer(NativeNormalizer::FORM_KC);
        }

        return $this->form_kc;
    }

    protected function getNormalizer($form): Transformation
    {
        if ($this->normalizerExists()) {
            $normalizer = fn ($from) => NativeNormalizer::normalize($from, $form);
        } else {
            $normalizer = fn ($from) => PolyfillNormalizer::normalize($from, $form);
        }

        return new \ILIAS\Refinery\Custom\Transformation($normalizer);
    }

    protected function normalizerExists(): bool
    {
        return class_exists(NativeNormalizer::class) && method_exists(NativeNormalizer::class, 'normalize');
    }
}
