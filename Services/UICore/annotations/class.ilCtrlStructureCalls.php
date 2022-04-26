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
 ********************************************************************
 */

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("CLASS")
 * @Attributes({
 *      @Attribute("children", type = "array"),
 *      @Attribute("parents", type = "array"),
 * })
 *
 * @noinspection AutoloadingIssuesInspection
 */
class ilCtrlStructureCalls
{
    /**
     * @var string[]
     */
    protected array $parents;

    /**
     * @var string[]
     */
    protected array $children;

    /**
     * @param string[] $parents
     * @param string[] $children
     */
    public function __construct(array $children = [], array $parents = [])
    {
        $this->children = $children;
        $this->parents = $parents;
    }

    /**
     * @return string[]
     */
    public function getChildren() : array
    {
        return $this->children;
    }

    /**
     * @return string[]
     */
    public function getParents() : array
    {
        return $this->parents;
    }
}
