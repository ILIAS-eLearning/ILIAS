<?php

namespace ILIAS\components\File\Settings;

use ILIAS\UI\Component\Input\Field\Factory;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Input\Field\Section;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Form
{
    private \ilLanguage $language;
    private Factory $field_factory;
    private \ILIAS\Refinery\Factory $refinery;

    public function __construct(
        private General $settings,
        private bool $write_access
    ) {
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
            $this->language->txt('settings')
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
            ->withRequired(true)
            ->withDisabled(!$this->write_access)
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(function (int $value): int {
                    $this->settings->setDownloadLimitInMB($value);
                    return $value;
                })
            );

        $inline_file_extensions = $this->field_factory
            ->tag(
                $this->language->txt('inline_file_extensions'),
                [],
                $this->language->txt('inline_file_extensions_info')
            )
            ->withValue($this->settings->getInlineFileExtensions())
            ->withDisabled(!$this->write_access)
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(function (array $value): array {
                    $this->settings->setInlineFileExtensions($value);
                    return $value;
                })
            );

        $show_amount_of_downloads = $this->field_factory
            ->checkbox(
                $this->language->txt('show_amount_of_downloads'),
                $this->language->txt('show_amount_of_downloads_info')
            )
            ->withValue($this->settings->isShowAmountOfDownloads())
            ->withDisabled(!$this->write_access)
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    function (bool $value): bool {
                        $this->settings->setShowAmountOfDownloads($value);
                        return $value;
                    }
                )
            );

        $ascii_filename = $this->field_factory
            ->checkbox(
                $this->language->txt('download_ascii_filename'),
                $this->language->txt('download_ascii_filename_info')
            )
            ->withValue($this->settings->isDownloadWithAsciiFileName())
            ->withDisabled(!$this->write_access)
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(function (bool $value): bool {
                    $this->settings->setDownloadWithAsciiFileName($value);
                    return $value;
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
