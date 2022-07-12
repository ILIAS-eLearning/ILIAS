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
 
namespace ILIAS\UI\Implementation\Component\Counter;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Counter\Counter as Spec;

class Counter implements Spec
{
    use ComponentHelper;

    private static array $types = array( self::NOVELTY, self::STATUS);
    private string $type;
    private int $number;

    public function __construct(string $type, int $number)
    {
        $this->checkArgIsElement("type", $type, self::$types, "counter type");
        $this->type = $type;
        $this->number = $number;
    }

    /**
     * @inheritdoc
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getNumber() : int
    {
        return $this->number;
    }
}
