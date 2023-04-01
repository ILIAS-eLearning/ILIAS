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

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Implementation\Component\Input\FormInputNameSource;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Dropzone\File\Factory as FileDropzoneFactory;
use ILIAS\UI\Component\Dropzone\File\Standard as StandardDropzone;
use ILIAS\UI\Component\Dropzone\File\Wrapper as WrapperDropzone;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\File as FileInput;
use ILIAS\UI\Component\Component;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Factory implements FileDropzoneFactory
{
    protected SignalGeneratorInterface $signal_generator;
    protected FieldFactory $field_factory;

    public function __construct(SignalGeneratorInterface $signal_generator, FieldFactory $field_factory)
    {
        $this->signal_generator = $signal_generator;
        $this->field_factory = $field_factory;
    }

    /**
     * @inheritDoc
     */
    public function standard(string $title, string $message, string $post_url, FileInput $file_input): StandardDropzone
    {
        return new Standard(
            $this->signal_generator,
            $this->field_factory,
            new FormInputNameSource(),
            $file_input,
            $title,
            $message,
            $post_url
        );
    }

    /**
     * @inheritDoc
     */
    public function wrapper(string $title, string $post_url, $content, FileInput $file_input): WrapperDropzone
    {
        return new Wrapper(
            $this->signal_generator,
            $this->field_factory,
            new FormInputNameSource(),
            $file_input,
            $title,
            $content,
            $post_url
        );
    }
}
