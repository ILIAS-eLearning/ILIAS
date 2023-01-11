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

namespace ILIAS\Refinery\String\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use InvalidArgumentException;
use Symfony\Polyfill\Intl\Normalizer\Normalizer as PolyfillNormalizer;
use Normalizer as NativeNormalizer;

class UTFNormalTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    protected const METHOD_NORMALIZER = 1;
    protected const METHOD_POLYFILL = 2;

    public const FORM_D = 4;
    public const FORM_KD = 8;
    public const FORM_C = 16;
    public const FORM_KC = 32;

    private int $form = self::FORM_C;
    private int $method = self::METHOD_POLYFILL;

    public function __construct(
        int $form = self::FORM_C
    ) {
        $this->form = $form;
        $this->determineMethod();
    }

    private function determineMethod(): void
    {
        if ($this->normalizerExists()) {
            $this->method = self::METHOD_NORMALIZER;
        } else {
            // We luckily have a polyfill for this in libs/composer/vendor/symfony/polyfill-intl-normalizer
            $this->method = self::METHOD_POLYFILL;
        }
    }

    protected function normalizerExists(): bool
    {
        return class_exists(NativeNormalizer::class) && method_exists(NativeNormalizer::class, 'normalize');
    }

    protected function determineNativeOrPolyfillForm(): int
    {
        switch ($this->form) {
            case self::FORM_D:
                $form = NativeNormalizer::FORM_D;
                break;
            case self::FORM_KD:
                $form = NativeNormalizer::FORM_KD;
                break;
            case self::FORM_KC:
                $form = NativeNormalizer::FORM_KC;
                break;
            case self::FORM_C:
            default:
                $form = NativeNormalizer::FORM_C;
                break;
        }
        return $form;
    }

    private function fromPolyfill(string $from): string
    {
        $form = $this->determineNativeOrPolyfillForm();
        if (PolyfillNormalizer::isNormalized($from, $form)) {
            return $from;
        }

        return PolyfillNormalizer::normalize($from, $form) ?: "";
    }

    private function fromNormalizer(string $from): string
    {
        $form = $this->determineNativeOrPolyfillForm();
        if (NativeNormalizer::isNormalized($from, $form)) {
            return $from;
        }

        return NativeNormalizer::normalize($from, $form) ?: "";
    }

    /**
     * The transform method checks if the $form variable contains a string
     * alternatively an InvalidArgumentException is thrown.
     * After the string will be normalized to the desired form.
     *
     * @throws \InvalidArgumentException
     */
    public function transform($from): string
    {
        if (!is_string($from)) {
            throw new InvalidArgumentException(__METHOD__ . " the argument is not a string.");
        }

        switch ($this->method) {
            case self::METHOD_NORMALIZER:
                return $this->fromNormalizer($from);
            case self::METHOD_POLYFILL:
                return $this->fromPolyfill($from);
        }

        return "";
    }
}
