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

namespace ILIAS\Modules\File\Preview;

use ILIAS\Administration\Setting;
use ILIAS\ResourceStorage\Flavour\Engine\GDEngine;
use ILIAS\ResourceStorage\Flavour\Engine\ImagickEngine;
use ILIAS\UI\Component\Input\Field\Group;
use ilSetting;
use ILIAS\UI\Component\Input\Field\Section;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Settings extends ilSetting implements Setting
{
    public const V_MAX_PREVIEWS_DEFAULT = 5;
    public const V_MAX_PREVIEWS_MIN = 1;
    public const V_MAX_PREVIEWS_MAX = 100;

    public const V_IMAGE_SIZE_DEFAULT = 868;
    public const V_IMAGE_SIZE_MIN = 50;
    public const V_IMAGE_SIZE_MAX = 1000;

    private const V_IMAGE_QUALITY_DEFAULT = 85;
    private const V_IMAGE_QUALITY_MIN = 1;
    private const V_IMAGE_QUALITY_MAX = 100;

    public const MODULE_NAME = 'preview';

    private const F_PREVIEW_ENABLED = 'preview_enabled';
    private const F_MAX_PREVIEWS_PER_OBJECT = 'max_previews_per_object';
    private const F_PREVIEW_IMAGE_SIZE = 'preview_image_size';
    private const F_PREVIEW_PERSISTING = 'preview_persisting';
    private const F_PREVIEW_IMAGE_QUALITY = 'preview_image_quality';

    public function __construct()
    {
        parent::__construct(self::MODULE_NAME, false);
    }

    public function setPersisting(bool $a_value): void
    {
        $this->set(self::F_PREVIEW_PERSISTING, $this->boolToStr($a_value));
    }

    public function isPersisting(): bool
    {
        return $this->strToBool($this->get(self::F_PREVIEW_PERSISTING, '1'));
    }

    public function isPreviewPossible(): bool
    {
        return (new ImagickEngine())->isRunning(); // &&(new GDEngine())->isRunning();
    }

    public function setPreviewEnabled(bool $a_value): void
    {
        $this->set(self::F_PREVIEW_ENABLED, $this->boolToStr($a_value));
    }

    public function isPreviewEnabled(): bool
    {
        return $this->isPreviewPossible() && $this->strToBool($this->get(self::F_PREVIEW_ENABLED, '1'));
    }

    public function setMaximumPreviews(int $max_previews): void
    {
        $max_previews = $this->adjustNumeric(
            $max_previews,
            self::V_MAX_PREVIEWS_MIN,
            self::V_MAX_PREVIEWS_MAX,
            self::V_MAX_PREVIEWS_DEFAULT
        );
        $this->set(self::F_MAX_PREVIEWS_PER_OBJECT, $this->intToStr($max_previews));
    }

    public function getMaximumPreviews(): int
    {
        return $this->strToInt($this->get(self::F_MAX_PREVIEWS_PER_OBJECT, (string) self::V_MAX_PREVIEWS_DEFAULT));
    }

    public function setImageSize(int $image_size): void
    {
        $image_size = $this->adjustNumeric(
            $image_size,
            self::V_IMAGE_SIZE_MIN,
            self::V_IMAGE_SIZE_MAX,
            self::V_IMAGE_SIZE_DEFAULT
        );
        $this->set(self::F_PREVIEW_IMAGE_SIZE, $this->intToStr($image_size));
    }

    public function getImageSize(): int
    {
        return $this->strToInt($this->get(self::F_PREVIEW_IMAGE_SIZE, (string) self::V_IMAGE_SIZE_DEFAULT));
    }

    public function setImageQuality(int $quality): void
    {
        $quality = $this->adjustNumeric(
            $quality,
            self::V_IMAGE_QUALITY_MIN,
            self::V_IMAGE_QUALITY_MAX,
            self::V_IMAGE_QUALITY_DEFAULT
        );
        $this->set(self::F_PREVIEW_IMAGE_QUALITY, $this->intToStr($quality));
    }

    public function getImageQuality(): int
    {
        return $this->strToInt($this->get(self::F_PREVIEW_IMAGE_QUALITY, (string) self::V_IMAGE_QUALITY_DEFAULT));
    }

    private function adjustNumeric(int $value, int $min, int $max, int $default): int
    {
        // is number?
        if (is_numeric($value)) {
            // don't allow to large numbers
            $value = (int) $value;
            if ($value < $min) {
                $value = $min;
            } elseif ($value > $max) {
                $value = $max;
            }
        } else {
            $value = $default;
        }

        return $value;
    }

    // HELPERS

    private function strToBool(string $value): bool
    {
        return $value === '1';
    }

    private function boolToStr(bool $value): string
    {
        return $value ? '1' : '0';
    }

    private function intToStr(int $int): string
    {
        return (string) $int;
    }

    private function strToInt(string $str): int
    {
        return (int) $str;
    }
}
