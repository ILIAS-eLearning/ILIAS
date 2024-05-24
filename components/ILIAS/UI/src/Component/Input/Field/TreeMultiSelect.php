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

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Input\Container\Form\FormInput;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface TreeMultiSelect extends FormInput, HasDynamicInputs
{
    /**
     * Get an input like this, but allow the selection of sub-nodes under an already
     * selected parent node. By default, such sub-nodes cannot be selected anymore.
     */
    public function withSelectSubNodes(bool $is_allowed): static;
}
