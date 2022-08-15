<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\UI\Component\Tree\Node\KeyValue as KeyValueInterface;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * @author  Tim Schmitz <schmitz@leifos.de>
 */
class KeyValue extends Simple implements KeyValueInterface
{
    private string $value;

    public function __construct(string $label, string $value, Icon $icon = null)
    {
        parent::__construct($label, $icon);

        $this->value = $value;
    }

    public function getValue() : string
    {
        return $this->value;
    }
}
