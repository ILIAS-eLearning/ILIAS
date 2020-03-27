<?php
declare(strict_types=1);

class DataNode
{
    public function __construct(string $label, array $children = [])
    {
        $this->label = $label;
        $this->children = $children;
    }
    public function getLabel()
    {
        return $this->label;
    }
    public function getChildren()
    {
        return $this->children;
    }
}

function expandable2()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();


    $n11 = new DataNode('1.1');
    $n12 = new DataNode('1.2', array(new DataNode('1.2.1')));
    $n1 = new DataNode('1', [$n11, $n12]);
    $data = [$n1];

    $recursion = new class implements \ILIAS\UI\Component\Tree\TreeRecursion {
        public function getChildren($record, $environment = null) : array
        {
            return $record->getChildren();
        }

        public function build(
            \ILIAS\UI\Component\Tree\Node\Factory $factory,
            $record,
            $environment = null
        ) : \ILIAS\UI\Component\Tree\Node\Node {
            return $factory->simple($record->getLabel());
        }
    };

    $tree = $f->tree()->expandable("Label", $recursion)
        ->withData($data);

    return $renderer->render($tree);
}
