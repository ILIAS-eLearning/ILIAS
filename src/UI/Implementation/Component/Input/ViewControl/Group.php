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

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\Container\ViewControl\ViewControlInput as ViewControlInputInterface;
use ILIAS\UI\Component\Input\ViewControl\Group as ViewControlGroupInterface;
use ILIAS\UI\Implementation\Component\Input\GroupInternal;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\Input;
use ILIAS\UI\Implementation\Component\Input\Group as GroupInternals;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result;
use ILIAS\UI\Component\Signal;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Group extends ViewControlInput implements ViewControlGroupInterface, GroupInternal
{
    use GroupInternals;

    protected \ilLanguage $language;

    public function __construct(
        DataFactory $data_factory,
        Refinery $refinery,
        \ilLanguage $language,
        array $inputs
    ) {
        parent::__construct($data_factory, $refinery);
        $this->language = $language;
        $this->checkInputListElements('inputs', $inputs, [ViewControlInputInterface::class]);
        $this->setInputs($inputs);
    }

    /**
     * @inheritdoc
     */
    public function withNameFrom(NameSource $source, ?string $parent_name = null): self
    {
        /** @var $clone self */
        $clone = parent::withNameFrom($source, $parent_name);
        $clone->setInputs($this->nameInputs($source, $clone->getName()));
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getContent(): Result
    {
        if (empty($this->getInputs())) {
            return new Ok([]);
        }
        return parent::getContent();
    }

    public function withOnChange(Signal $change_signal): self
    {
        $clone = parent::withOnChange($change_signal);
        $clone->setInputs(array_map(static fn ($i) => $i->withOnChange($change_signal), $clone->getInputs()));
        return $clone;
    }

    /**
     * @inheritDoc
     */
    protected function setError(string $error): void
    {
        $this->error = $error;
    }

    protected function getLanguage(): \ilLanguage
    {
        return $this->language;
    }

    protected function getDataFactory(): DataFactory
    {
        return $this->data_factory;
    }

    /** ATTENTION: @see GroupInternals::_isClientSideValueOk() */
    protected function isClientSideValueOk($value): bool
    {
        return $this->_isClientSideValueOk($value);
    }
}
