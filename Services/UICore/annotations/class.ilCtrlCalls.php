<?php declare(strict_types=1);

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

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
 ********************************************************************
 */

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target(Target::TARGET_CLASS)
 * @Attributes({
 *      @Attribute("class", type = "string"),
 *      @Attribute("called_by", type = "array"),
 *      @Attribute("calls", type = "array"),
 * })
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilCtrlCalls
{
    /**
     * @Required
     */
    protected string $class_name;

    /**
     * @var string[]
     */
    protected array $called_by;

    /**
     * @var string[]
     */
    protected array $calls;

    /**
     * @param string[]  $called_by
     * @param string[]  $calls
     */
    public function __construct(string $class, array $called_by = [], array $calls = [])
    {
        $this->class_name = $class;
        $this->called_by = $called_by;
        $this->calls = $calls;
    }

    public function getClassName() : string
    {
        return $this->class_name;
    }

    /**
     * @return string[]
     */
    public function getCalledBy() : array
    {
        return $this->called_by;
    }

    /**
     * @return string[]
     */
    public function getCalls() : array
    {
        return $this->calls;
    }
}
