<?php

include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/ilAssLacCompositeInterface.php';

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
    public function addNode(ilAssLacCompositeInterface $node)
    {
        $this->nodes[] = $node;
    }

    /**
     * Describes a Composite tree Structure as human readable string
     * @return string
     */
    public function describe()
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
