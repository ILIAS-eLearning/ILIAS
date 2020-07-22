<?php

/**
 * Class CompositeBuilder
 *
 * Date: 27.03.13
 * Time: 12:18
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacCompositeBuilder
{

    /**
     * This array defines the weights and direction of operators.<br />
     * It is required to build the composite tree with the correct depth structure
     *
     * @var array
     */
    protected $operators = array('<=','<','=','>=','>','<>','&','|');

    /**
     * Construct requirements
     */
    public function __construct()
    {
        include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Factory/ilAssLacOperationManufacturer.php';
        include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Factory/ilAssLacExpressionManufacturer.php';
    }

    /**
     * @param array $nodes
     *
     * @return array
     */
    public function create($nodes)
    {
        if ($nodes['type'] == 'group') {
            foreach ($nodes['nodes'] as $key => $child) {
                $nodes['nodes'][$key] = $this->create($child);
            }

            foreach ($this->operators as $next_operator) {
                do {
                    $index = -1;
                    for ($i = 0; $i < count($nodes['nodes']); $i++) {
                        if (!is_object($nodes['nodes'][$i]) && $nodes['nodes'][$i]['type'] == 'operator' && $nodes['nodes'][$i]['value'] == $next_operator) {
                            $index = $i;
                            break;
                        }
                    }
                    if ($index >= 0) {
                        $operation_manufacture = ilAssLacOperationManufacturer::_getInstance();
                        $operator = $operation_manufacture->manufacture($nodes['nodes'][$index]['value']);

                        $operator->setNegated($nodes["negated"]);
                        $operator->addNode($this->getExpression($nodes, $index - 1));
                        $operator->addNode($this->getExpression($nodes, $index + 1));

                        $new_nodes = array_slice($nodes['nodes'], 0, $index - 1);
                        $new_nodes[] = $operator;
                        $nodes['nodes'] = array_merge($new_nodes, array_slice($nodes['nodes'], $index + 2));
                    }
                } while ($index >= 0);
            }
            return $nodes['nodes'][0];
        }
        return $nodes;
    }

    /**
     * Manufacure an expression from the delivered node and the index. If an expression already exist in the node for<br />
     * for the delivered index, this function will return the existing expression
     *
     * @param array $node
     * @param int $index
     *
     * @return ilAssLacCompositeInterface
     */
    private function getExpression(array $node, $index)
    {
        $manufacturer = ilAssLacExpressionManufacturer::_getInstance();

        $expression = $node['nodes'][$index];
        if (!($expression instanceof ilAssLacAbstractComposite)) {
            $expression = $manufacturer->manufacture($node['nodes'][$index]['value']);
        }
        return $expression;
    }
}
