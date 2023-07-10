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

/**
 * Class AbstractComposite
 *
 * Date: 25.03.13
 * Time: 10:05
 * @author Thomas Joußen <tjoussen@databay.de>
 */
abstract class ilAssLacAbstractComposite implements ilAssLacCompositeInterface
{
    /**
     * @var ilAssLacAbstractComposite[]
     */
    public $nodes = array();

    /**
     * Adds an ilAssLacCompositeInterface object to the node array which represents the condition tree structure
     *
     * @param ilAssLacCompositeInterface $node
     */
    public function addNode(ilAssLacCompositeInterface $node): void
    {
        $this->nodes[] = $node;
    }

    /**
     * Describes a Composite tree Structure as human readable string
     * @return string
     */
    public function describe(): string
    {
        $description = "";
        if (\count($this->nodes) > 0) {
            $description .= "(" . $this->nodes[0]->describe();
        }
        $description .= $this->getDescription();
        if (\count($this->nodes) > 0) {
            $description .= $this->nodes[1]->describe() . ") ";
        }
        return $description;
    }
}
