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
 */

namespace ILIAS\UI\Implementation\Component\Meta;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Meta\Meta;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class AbstractMeta implements Meta
{
    use ComponentHelper;

    protected string $key;
    protected string $value;

    public function __construct(string $key, string $value)
    {
        $this->checkAttributeKey($key);
        $this->checkEmptyStringArg('value', $value);

        $this->key = $key;
        $this->value = $value;
    }

    public function getKey() : string
    {
        return $this->key;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    protected function checkAttributeKey(string $key) : void
    {
        if (!in_array($key, $this->getSupportedAttributes(), true)) {
            throw new \InvalidArgumentException(
                "Invalid key supplied for " . static::class . ", allowed are: " .
                implode(", ", $this->getSupportedAttributes())
            );
        }
    }

    protected function checkEmptyStringArg(string $which, string $arg) : void
    {
        if (empty($arg)) {
            throw new \LogicException("Cannot set empty attribute $which.");
        }
    }

    /**
     * This function will be used to check whether a given attribute-key
     * is allowed when creating an instance of Meta.
     *
     * @return string[]
     */
    abstract protected function getSupportedAttributes() : array;
}
