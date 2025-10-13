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
 * @author Ferdinand Engländer <ferdinand.englaender@concepts-and-training.de>
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;
use ILIAS\UI\Component as C;

/**
 * This implements the section input.
 */
class Section extends Group implements C\Input\Field\Section
{
    protected int $nesting_level = 0;

    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        Language $lng,
        array $inputs,
        string $label,
        ?string $byline = null
    ) {
        parent::__construct($data_factory, $refinery, $lng, $inputs, $label, $byline);
        // only set/update nesting levels recursively once during construction.
        $this->setSectionNestingLevelsRecursively($this->getInputs(), $this->getNestingLevel());
    }

    public function setNestingLevel(int $nesting_level): void
    {
        $this->nesting_level = $nesting_level;
    }

    public function getNestingLevel(): int
    {
        return $this->nesting_level;
    }

    /**
     * @param C\Input\Input[] $inputs
     */
    protected function setSectionNestingLevelsRecursively(array $inputs, int $current_nesting_level): void
    {
        foreach ($inputs as $input) {
            if ($input instanceof C\Input\Group && !($input instanceof self)) {
                $this->setSectionNestingLevelsRecursively($input->getInputs(), $current_nesting_level);
                continue;
            }
            if ($input instanceof self) {
                $next_nesting_level = $current_nesting_level + 1;
                $input->setNestingLevel($next_nesting_level);
                $input->setSectionNestingLevelsRecursively($input->getInputs(), $next_nesting_level);
            }
        }
    }
}
