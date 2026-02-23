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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\ImagePurpose;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Language\Language;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Image extends File implements C\Input\Field\Image
{
    public function __construct(
        Language $language,
        DataFactory $data_factory,
        Factory $field_factory,
        Refinery $refinery,
        UploadLimitResolver $upload_limit_resolver,
        C\Input\Field\UploadHandler $handler,
        protected ImagePurpose $image_purpose,
        string $label,
        ?FormInput $metadata_input,
        ?string $byline
    ) {
        parent::__construct(
            $language,
            $data_factory,
            $field_factory,
            $refinery,
            $upload_limit_resolver,
            $handler,
            $label,
            $this->createMetaDataInput($language, $field_factory, $metadata_input),
            $byline,
        );

        $this->accepted_mime_types = ['image/*'];
    }

    public function getImagePurpose(): ImagePurpose
    {
        return $this->image_purpose;
    }

    protected function createMetaDataInput(
        Language $language,
        Factory $field_factory,
        ?FormInput $metadata_input
    ): ?FormInput {
        $image_purpose_input = match ($this->getImagePurpose()) {
            ImagePurpose::USER_DEFINED => $this->createUserDefinedAltTextField($language, $field_factory),
            ImagePurpose::INFORMATIVE => $this->createInformativeAltTextField($language, $field_factory),
            default => null,
        };
        if (null === $image_purpose_input && null === $metadata_input) {
            return null;
        }
        if (null === $image_purpose_input && null !== $metadata_input) {
            return $metadata_input;
        }
        if (null !== $image_purpose_input && null === $metadata_input) {
            return $field_factory->group([$image_purpose_input]);
        }
        return $field_factory->group([$image_purpose_input, $metadata_input]);
    }

    protected function createUserDefinedAltTextField(Language $language, Factory $field_factory): FormInput
    {
        return $field_factory->switchableGroup([
            ImagePurpose::INFORMATIVE->name => $field_factory->group([$field_factory->textarea($language->txt('image_alt_text'))], $language->txt('image_purpose_informative')),
            ImagePurpose::DECORATIVE->name => $field_factory->group([], $language->txt('image_purpose_decorative'))
        ], $language->txt('image_purpose_user_defined'))->withRequired(true);
    }

    protected function createInformativeAltTextField(Language $language, Factory $field_factory): FormInput
    {
        return $field_factory->textarea($language->txt('image_alt_text'))->withRequired(true);
    }
}
