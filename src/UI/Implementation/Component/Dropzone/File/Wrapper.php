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
use ILIAS\UI\Component\Dropzone\File\Wrapper as WrapperInterface;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Component;
use LogicException;
use ilLanguage;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Signal;

/**
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Wrapper extends File implements WrapperInterface
{
    protected SignalGeneratorInterface $signal_generator;
    protected \ILIAS\UI\Implementation\Component\Signal $clear_signal;
    /**
     * @var Component[]
     */
    protected array $components;

    /**
     * @param Component[]|Component $content
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        InputFactory $input_factory,
        ilLanguage $language,
        UploadLimitResolver $upload_limit_resolver,
        UploadHandler $upload_handler,
        string $post_url,
        $content,
        ?Input $metadata_input
    ) {
        parent::__construct($input_factory, $language, $upload_limit_resolver, $upload_handler, $post_url, $metadata_input);

        $content = $this->toArray($content);
        $this->checkArgListElements('content', $content, [Component::class]);
        $this->checkEmptyArray($content);

        $this->components = $content;
        $this->signal_generator = $signal_generator;
        $this->initSignals();
    }

    protected function initSignals(): void
    {
        $this->clear_signal = $this->signal_generator->create();
    }

    public function getContent(): array
    {
        return $this->components;
    }

    public function getClearSignal(): Signal
    {
        return $this->clear_signal;
    }

    public function withResetSignals()
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }

    /**
     * Checks if the passed array contains at least one element, throws a LogicException otherwise.
     * @throws LogicException if the passed in argument counts 0
     */
    private function checkEmptyArray(array $array): void
    {
        if (count($array) === 0) {
            throw new LogicException("At least one component from the UI framework is required, otherwise
			the wrapper dropzone is not visible.");
        }
    }
}
