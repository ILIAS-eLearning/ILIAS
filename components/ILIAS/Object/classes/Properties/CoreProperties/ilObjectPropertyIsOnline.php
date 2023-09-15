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

use ILIAS\UI\Component\Input\Field\Checkbox;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Stephan Kergomard
 */
class ilObjectPropertyIsOnline implements ilObjectProperty
{
    private const DEFAULT_IS_ONLINE = false;
    private const INPUT_LABEL = 'online';
    private const INPUT_BYLINE = 'online_input_byline';

    public function __construct(
        private bool $is_online = self::DEFAULT_IS_ONLINE
    ) {
    }

    public function getIsOnline(): bool
    {
        return $this->is_online;
    }

    public function withOnline(): self
    {
        $clone = clone $this;
        $clone->is_online = true;
        return $clone;
    }

    public function withOffline(): self
    {
        $clone = clone $this;
        $clone->is_online = false;
        return $clone;
    }

    public function toForm(
        \ilLanguage $language,
        FieldFactory $field_factory,
        Refinery $refinery
    ): Checkbox {
        $trafo = $refinery->custom()->transformation(
            function ($v): ilObjectProperty {
                return new ilObjectPropertyIsOnline($v);
            }
        );
        return $field_factory->checkbox($language->txt(self::INPUT_LABEL))
            ->withByline($language->txt(self::INPUT_BYLINE))
            ->withAdditionalTransformation($trafo)
            ->withValue($this->getIsOnline());
    }
}
