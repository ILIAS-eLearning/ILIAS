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
class Form
{
    private \ilLanguage $language;
    private \ILIAS\UI\Component\Input\Field\Factory $field_factory;
    private \ILIAS\Refinery\Factory $refinery;

    public function __construct(private Settings $settings)
    {
        global $DIC;
        $this->language = $DIC->language();
        $this->field_factory = $DIC->ui()->factory()->input()->field();
        $this->refinery = $DIC->refinery();
    }

    public function asFormSection(): Section
    {
        return $this->field_factory->section(
            [$this->asFormGroup()],
            $this->language->txt('preview')
        );
    }

    public function asFormGroup(): Group
    {
        $possible = $this->settings->isPreviewPossible();

        $activated = $this->field_factory
            ->checkbox(
                $this->language->txt('enable_preview'),
                $this->language->txt('enable_preview_info')
            )
            ->withDisabled(!$possible)
            ->withValue($this->settings->isPreviewEnabled())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(function ($v) {
                    $this->settings->setPreviewEnabled($v);
                })
            );

        $image_size = $this->field_factory
            ->numeric(
                $this->language->txt('preview_image_size'),
                $this->language->txt('preview_image_size_info')
            )
            ->withDisabled(!$possible)
            ->withRequired(true)
            ->withValue($this->settings->getImageSize())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(function ($v) {
                    $this->settings->setImageSize($v);
                })
            );

        $persisting = $this->field_factory
            ->checkbox(
                $this->language->txt('preview_persisting'),
                $this->language->txt('preview_persisting_info')
            )
            ->withDisabled(!$possible)
            ->withValue($this->settings->isPersisting())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(function ($v) {
                    $this->settings->setPersisting($v);
                })
            );

        $max_previews = $this->field_factory
            ->numeric(
                $this->language->txt('max_previews_per_object'),
                $this->language->txt('max_previews_per_object_info')
            )
            ->withDisabled(!$possible)
            ->withValue($this->settings->getMaximumPreviews())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(function ($v) {
                    $this->settings->setMaximumPreviews($v);
                })
            );

        return $this->field_factory->group(
            [
                $activated,
                $image_size,
                $max_previews,
                $persisting
            ]
        );
    }
}
