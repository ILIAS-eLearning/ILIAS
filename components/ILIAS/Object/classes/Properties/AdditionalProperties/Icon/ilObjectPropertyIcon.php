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

use ILIAS\Object\Properties\ObjectTypeSpecificProperties\ilObjectTypeSpecificPropertyProviders;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\File;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\FileUpload\MimeType;

/**
 * @author Stephan Kergomard
 */
class ilObjectPropertyIcon implements ilObjectProperty
{
    public const SUPPORTED_MIME_TYPES = [MimeType::IMAGE__SVG_XML];
    private const INPUT_LABEL = 'custom_icon';

    private bool $deleted_flag = false;
    private ?string $temp_file_name = null;

    public function __construct(
        private bool $custom_icons_enabled,
        private ?ilObjectCustomIcon $custom_icon = null,
        private ?ilObjectTypeSpecificPropertyProviders $object_type_specific_property_providers = null
    ) {
    }

    public function getIcon(): ?ilObjectCustomIcon
    {
        return $this->custom_icon;
    }

    public function getDeletedFlag(): bool
    {
        return $this->deleted_flag;
    }

    public function withDeletedFlag(): self
    {
        $clone = clone $this;
        $clone->deleted_flag = true;
        return $clone;
    }

    public function getTempFileName(): ?string
    {
        return $this->temp_file_name;
    }

    public function withTempFileName(string $name): self
    {
        $clone = $this;
        $clone->temp_file_name = $name;
        return $clone;
    }

    public function toForm(
        \ilLanguage $language,
        FieldFactory $field_factory,
        Refinery $refinery
    ): ?File {
        if (!$this->custom_icons_enabled) {
            return null;
        }
        $trafo = $refinery->custom()->transformation(
            function ($v): ?ilObjectProperty {
                $property_icon = new ilObjectPropertyIcon(
                    $this->custom_icons_enabled,
                    $this->custom_icon
                );

                if (count($v) > 0 && $v[0] !== 'custom_icon') {
                    return $property_icon
                        ->withTempFileName($v[0]);
                }

                if (count($v) === 0 && $this->custom_icon->exists()) {
                    return $property_icon
                        ->withDeletedFlag();
                }

                return $property_icon;
            }
        );
        $custom_icon = $field_factory
            ->file(new ilObjectCustomIconUploadHandlerGUI($this->custom_icon), $language->txt(self::INPUT_LABEL))
            ->withAcceptedMimeTypes(self::SUPPORTED_MIME_TYPES)
            ->withAdditionalTransformation($trafo);

        if (!$this->custom_icon->exists()) {
            return $custom_icon;
        }

        return $custom_icon->withValue(['custom_icon']);
    }

    public function toLegacyForm(
        \ilLanguage $language
    ): ?ilImageFileInputGUI {
        if (!$this->custom_icons_enabled) {
            return null;
        }
        $custom_icon_input = new ilImageFileInputGUI($language->txt(self::INPUT_LABEL), 'icon');
        $custom_icon_input->setSuffixes($this->custom_icon->getSupportedFileExtensions());
        $custom_icon_input->setUseCache(false);
        if ($this->custom_icon->exists()) {
            $custom_icon_input->setImage($this->custom_icon->getFullPath());
        } else {
            $custom_icon_input->setImage('');
        }

        return $custom_icon_input;
    }
}
