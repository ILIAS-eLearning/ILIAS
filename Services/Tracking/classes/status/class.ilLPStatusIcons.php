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

/**
 * Caches and supplies the paths to the learning progress status images.
 * @author  Tim Schmitz <schmitz@leifos.de>
 */
class ilLPStatusIcons
{
    public const ICON_VARIANT_LONG = 0;
    public const ICON_VARIANT_SHORT = 1;
    public const ICON_VARIANT_SCORM = 2;
    public const ICON_VARIANT_DEFAULT = ilLPStatusIcons::ICON_VARIANT_LONG;

    private static ?self $instance_variant_long = null;
    private static ?self $instance_variant_short = null;
    private static ?self $instance_variant_scorm = null;

    private string $image_path_in_progress = '';
    private string $image_path_completed = '';
    private string $image_path_not_attempted = '';
    private string $image_path_failed = '';

    //The following two icons are not available as a long variant.
    private string $image_path_asset = '';
    private string $image_path_running = '';

    public static function getInstance(int $variant = ilLPStatusIcons::ICON_VARIANT_DEFAULT) : ilLPStatusIcons
    {
        switch ($variant) {
            case ilLPStatusIcons::ICON_VARIANT_SCORM:
                if (self::$instance_variant_scorm) {
                    return self::$instance_variant_scorm;
                }
                return self::$instance_variant_scorm = new self(ilLPStatusIcons::ICON_VARIANT_SCORM);

            case ilLPStatusIcons::ICON_VARIANT_SHORT:
                if (self::$instance_variant_short) {
                    return self::$instance_variant_short;
                }
                return self::$instance_variant_short = new self(ilLPStatusIcons::ICON_VARIANT_SHORT);

            case ilLPStatusIcons::ICON_VARIANT_LONG:
                if (self::$instance_variant_long) {
                    return self::$instance_variant_long;
                }
                return self::$instance_variant_long = new self(ilLPStatusIcons::ICON_VARIANT_LONG);

            default:
                throw new ilLPException("No such variant of the LP icons exists.");
        }
    }

    private function __construct(int $variant = ilLPStatusIcons::ICON_VARIANT_DEFAULT)
    {
        switch ($variant) {
            case ilLPStatusIcons::ICON_VARIANT_SCORM:
                $this->image_path_in_progress = ilUtil::getImagePath('scorm/incomplete.svg');
                $this->image_path_completed = ilUtil::getImagePath('scorm/complete.svg');
                $this->image_path_not_attempted = ilUtil::getImagePath('scorm/not_attempted.svg');
                $this->image_path_failed = ilUtil::getImagePath('scorm/failed.svg');
                $this->image_path_asset = ilUtil::getImagePath('scorm/asset.svg');
                $this->image_path_running = ilUtil::getImagePath('scorm/running.svg');
                break;

            case ilLPStatusIcons::ICON_VARIANT_SHORT:
                $this->image_path_in_progress = ilUtil::getImagePath('learning_progress/short/in_progress.svg');
                $this->image_path_completed = ilUtil::getImagePath('learning_progress/short/completed.svg');
                $this->image_path_not_attempted = ilUtil::getImagePath('learning_progress/short/not_attempted.svg');
                $this->image_path_failed = ilUtil::getImagePath('learning_progress/short/failed.svg');
                $this->image_path_asset = ilUtil::getImagePath('learning_progress/short/asset.svg');
                $this->image_path_running = ilUtil::getImagePath('learning_progress/short/running.svg');
                break;

            case ilLPStatusIcons::ICON_VARIANT_LONG:
                $this->image_path_in_progress = ilUtil::getImagePath('learning_progress/in_progress.svg');
                $this->image_path_completed = ilUtil::getImagePath('learning_progress/completed.svg');
                $this->image_path_not_attempted = ilUtil::getImagePath('learning_progress/not_attempted.svg');
                $this->image_path_failed = ilUtil::getImagePath('learning_progress/failed.svg');
                break;

            default:
                throw new ilLPException("No such variant of the LP icons exists.");
        }
    }

    public function getImagePathInProgress() : string
    {
        return $this->image_path_in_progress;
    }

    public function getImagePathCompleted() : string
    {
        return $this->image_path_completed;
    }

    public function getImagePathNotAttempted() : string
    {
        return $this->image_path_not_attempted;
    }

    public function getImagePathFailed() : string
    {
        return $this->image_path_failed;
    }

    /**
     * A long variant of this icon is not available.
     */
    public function getImagePathAsset() : string
    {
        if ($this->image_path_asset) {
            return $this->image_path_asset;
        }
        throw new ilLPException("A long variant of the 'asset' LP icon does not exist.");
    }

    /**
     * A long variant of this icon is not available.
     */
    public function getImagePathRunning() : string
    {
        if ($this->image_path_running) {
            return $this->image_path_running;
        }
        throw new ilLPException("A long variant of the 'running' LP icon does not exist.");
    }
}
