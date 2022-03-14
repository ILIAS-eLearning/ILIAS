<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Dropzone\File\Wrapper as WrapperInterface;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Component;
use LogicException;
use ilLanguage;
use ILIAS\UI\Component\Input\Field\Input;

/**
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Wrapper extends File implements WrapperInterface
{
    /**
     * @var Component[]
     */
    protected array $components;

    /**
     * @param Component[]|Component $content
     */
    public function __construct(
        InputFactory $input_factory,
        ilLanguage $language,
        UploadHandler $upload_handler,
        string $post_url,
        $content,
        ?Input $metadata_input
    ) {
        parent::__construct($input_factory, $language, $upload_handler, $post_url, $metadata_input);

        $content = $this->toArray($content);
        $this->checkArgListElements('content', $content, [Component::class]);
        $this->checkEmptyArray($content);

        $this->components = $content;
    }

    public function getContent() : array
    {
        return $this->components;
    }

    /**
     * Checks if the passed array contains at least one element, throws a LogicException otherwise.
     * @throws LogicException if the passed in argument counts 0
     */
    private function checkEmptyArray(array $array) : void
    {
        if (count($array) === 0) {
            throw new LogicException("At least one component from the UI framework is required, otherwise
			the wrapper dropzone is not visible.");
        }
    }
}
