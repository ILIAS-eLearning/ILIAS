<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Dropzone\File\Standard as StandardInterface;
use ILIAS\UI\Component\Dropzone\File\Wrapper as WrapperInterface;
use ILIAS\UI\Component\Dropzone\File\Factory as FactoryInterface;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Input\Field\Input;
use ilLanguage;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Factory implements FactoryInterface
{
    protected InputFactory $factory;
    protected ilLanguage $language;

    public function __construct(InputFactory $factory, ilLanguage $language)
    {
        $this->factory = $factory;
        $this->language = $language;
    }

    public function standard(
        UploadHandler $upload_handler,
        string $post_url,
        ?Input $metadata_input = null
    ) : StandardInterface {
        return new Standard(
            $this->factory,
            $this->language,
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
    ) : WrapperInterface {
        return new Wrapper(
            $this->factory,
            $this->language,
            $upload_handler,
            $post_url,
            $content,
            $metadata_input
        );
    }
}
