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
 
namespace ILIAS\Setup\Artifact;

use ILIAS\Setup;

/**
 * An array as an artifact.
 */
class ArrayArtifact implements Setup\Artifact
{
    private array $data = [];

    /**
     * @param array  $data - may only contain primitive data
     */
    public function __construct(array $data)
    {
        $this->check($data);
        $this->data = $data;
    }


    final public function serialize() : string
    {
        return "<?" . "php return " . var_export($this->data, true) . ";";
    }

    private function check(array $a) : void
    {
        foreach ($a as $item) {
            if (is_string($item) || is_int($item) || is_float($item) || is_bool($item) || is_null($item)) {
                continue;
            }
            if (is_array($item)) {
                $this->check($item);
                continue;
            }
            throw new \InvalidArgumentException(
                "Array data for artifact may only contain ints, strings, floats, bools or " .
                "other arrays with this content. Found: " . gettype($item)
            );
        }
    }
}
