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

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\ViewControl as VCInterface;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Implementation\Component\Input\Input;

class Mode extends ViewControlInput implements VCInterface\Mode
{
    public function __construct(
        DataFactory $data_factory,
        Refinery $refinery,
        protected array $options
    ) {
        $keys = array_keys($options);
        $this->checkArgListElements('options', $keys, 'string');
        $this->checkArgListElements('options', $options, 'string');
        if (count($options) < 2) {
            throw new \InvalidArgumentException('ModeViewControls must contain more than one option.');
        }
        $this->setAdditionalTransformation(
            $refinery->custom()->transformation(
                static fn($v) => $v ?? array_key_first($options)
            )
        );
        parent::__construct($data_factory, $refinery);
    }

    protected function isClientSideValueOk($value): bool
    {
        return is_null($value) || is_string($value);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function withAriaLabel(string $label): self
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }
}
