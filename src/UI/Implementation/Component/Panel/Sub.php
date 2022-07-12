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
 
namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Sub
 * @package ILIAS\UI\Implementation\Component\Sub
 */
class Sub extends Panel implements C\Panel\Sub
{
    use ComponentHelper;

    /**
     * Component to be displayed on the right of the Sub Panel
     * @var C\Card\Card|C\Panel\Secondary\Secondary
     */
    private $component = null;

    /**
     * @inheritdoc
     */
    public function withFurtherInformation($component) : C\Panel\Sub
    {
        $clone = clone $this;
        $clone->component = $component;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getFurtherInformation()
    {
        return $this->component;
    }
}
