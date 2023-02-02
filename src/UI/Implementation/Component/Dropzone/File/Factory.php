<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;
use ILIAS\UI\Component\Dropzone\File\Standard as StandardInterface;
use ILIAS\UI\Component\Dropzone\File\Wrapper as WrapperInterface;
use ILIAS\UI\Component\Dropzone\File\Factory as FactoryInterface;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Input\Field\Input;
use ilLanguage;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Factory implements FactoryInterface
{
    protected SignalGeneratorInterface $signal_generator;
    protected UploadLimitResolver $upload_limit_resolver;
    protected InputFactory $factory;
    protected ilLanguage $language;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        UploadLimitResolver $upload_limit_resolver,
        InputFactory $factory,
        ilLanguage $language
    ) {
        $this->upload_limit_resolver = $upload_limit_resolver;
        $this->factory = $factory;
        $this->language = $language;
        $this->signal_generator = $signal_generator;
    }

    public function standard(
        UploadHandler $upload_handler,
        string $post_url,
        ?Input $metadata_input = null
    ): StandardInterface {
        return new Standard(
            $this->factory,
            $this->language,
            $this->upload_limit_resolver,
            $upload_handler,
            $post_url,
            $metadata_input
        );
    }

    /**
     * @inheritdoc
     */
    public function wrapper(
        UploadHandler $upload_handler,
        string $post_url,
        $content,
        ?Input $metadata_input = null
    ): WrapperInterface {
        return new Wrapper(
            $this->signal_generator,
            $this->factory,
            $this->language,
            $this->upload_limit_resolver,
            $upload_handler,
            $post_url,
            $content,
            $metadata_input
        );
    }
}
