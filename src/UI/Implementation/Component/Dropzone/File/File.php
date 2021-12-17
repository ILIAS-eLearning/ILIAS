<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Input\Container\Form\Standard as FormInterface;
use ILIAS\UI\Implementation\Component\Input\Field\FileUploadAwareHelper;
use ILIAS\UI\Component\Dropzone\File\File as FileInterface;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Component\Signal;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Refinery\Transformation;
use LogicException;
use ilLanguage;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class File implements FileInterface
{
    public const JAVASCRIPT_EVENT = 'drop';

    use FileUploadAwareHelper;
    use JavaScriptBindable;
    use ComponentHelper;
    use Triggerer;

    protected ?FormInterface $form = null;
    protected InputFactory $input_factory;
    protected ilLanguage $language;
    protected string $title = '';
    protected string $post_url;

    public function __construct(
        InputFactory $input_factory,
        ilLanguage $language,
        UploadHandler $upload_handler,
        string $post_url
    ) {
        $this->input_factory = $input_factory;
        $this->language = $language;
        $this->upload_handler = $upload_handler;
        $this->post_url = $post_url;
    }

    public function withTitle(string $title) : self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getForm() : FormInterface
    {
        return
            $this->form ??
            $this->input_factory->container()->form()->standard(
                $this->post_url,
                [
                    $this->input_factory
                        ->field()->file($this->upload_handler, '')
                        ->withMaxFiles($this->getMaxFiles())
                        ->withMaxFileSize($this->getMaxFileSize())
                        ->withAcceptedMimeTypes($this->getAcceptedMimeTypes())
                        ->withTemplateForDynamicInputs(
                            $this->input_factory->field()->group([
                                $this->input_factory->field()->text($this->language->txt('dropzone_file_title')),
                                $this->input_factory->field()->textarea($this->language->txt('dropzone_file_description')),
                            ])
                        )
                    ,
                ]
            );
    }

    public function withRequest(ServerRequestInterface $request) : self
    {
        $clone = clone $this;
        $clone->form = (null === $clone->form) ?
            $clone->getForm()->withRequest($request) :
            $clone->form->withRequest($request);

        return $clone;
    }

    public function withAdditionalTransformation(Transformation $transformation) : self
    {
        $clone = clone $this;
        $clone->form = (null === $clone->form) ?
            $clone->getForm()->withAdditionalTransformation($transformation) :
            $clone->form->withAdditionalTransformation($transformation);

        return $clone;
    }

    public function getData()
    {
        if (null === $this->form) {
            throw new LogicException(static::class . " ::withRequest must be called first.");
        }

        return $this->form->getData();
    }

    public function withOnDrop(Signal $signal) : self
    {
        return $this->withTriggeredSignal($signal, self::JAVASCRIPT_EVENT);
    }

    public function withAdditionalDrop(Signal $signal) : self
    {
        return $this->appendTriggeredSignal($signal, self::JAVASCRIPT_EVENT);
    }
}
