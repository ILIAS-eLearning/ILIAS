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

namespace ILIAS\components\File\Settings;

use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Input\Field\Section;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Form
{
    private \ilLanguage $language;
    private \ILIAS\UI\Component\Input\Field\Factory $field_factory;
    private \ILIAS\Refinery\Factory $refinery;

    public function __construct(private General $settings)
    {
        global $DIC;
        $this->language = $DIC->language();
        $this->language->loadLanguageModule("bgtask");
        $this->field_factory = $DIC->ui()->factory()->input()->field();
        $this->refinery = $DIC->refinery();
    }

    public function asFormSection(): Section
    {
        return $this->field_factory->section(
            [$this->asFormGroup()],
            $this->language->txt('obj_file')
        );
    }

    public function asFormGroup(): Group
    {
        $download_limit = $this->field_factory
            ->numeric(
                $this->language->txt('bgtask_setting_limit'),
                $this->language->txt('bgtask_setting_limit_info')
            )
            ->withValue($this->settings->getDownloadLimitinMB())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(function ($value): void {
                    $this->settings->setDownloadLimitInMB($value);
                })
            );

        $inline_file_extensions = $this->field_factory
            ->tag(
                $this->language->txt('inline_file_extensions'),
                [],
                $this->language->txt('inline_file_extensions_info')
            )
            ->withValue($this->settings->getInlineFileExtensions())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(function ($value): void {
                    $this->settings->setInlineFileExtensions($value);
                })
            );

        $show_amount_of_downloads = $this->field_factory
            ->checkbox(
                $this->language->txt('show_amount_of_downloads'),
                $this->language->txt('show_amount_of_downloads_info')
            )
            ->withValue($this->settings->isShowAmountOfDownloads())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    function ($value): void {
                        $this->settings->setShowAmountOfDownloads($value);
                    }
                )
            );

        $ascii_filename = $this->field_factory
            ->checkbox(
                $this->language->txt('download_ascii_filename'),
                $this->language->txt('download_ascii_filename_info')
            )
            ->withValue($this->settings->isDownloadWithAsciiFileName())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(function ($value): void {
                    $this->settings->setDownloadWithAsciiFileName($value);
                })
            );

        return $this->field_factory->group(
            [
                $ascii_filename,
                $download_limit,
                $inline_file_extensions,
                $show_amount_of_downloads,
            ]
        );
    }
}
