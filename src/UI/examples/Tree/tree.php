<?php
function tree() {

	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$data = [
		['label' => 'root', 'children' => [
			['label' => '1', 'children' => [
				['label' => '1.1', 'children' => [
					['label' => '1.1.1', 'children' => []],
					['label' => '1.1.2', 'children' => []]
				]],
				['label' => '1.2', 'children' => []],
				['label' => '1.3', 'children' => []]
			]],
			['label' => '2', 'children' => [
				['label' => '2.1', 'children' => []],
			]],
			['label' => '3', 'children' => [
				['label' => '3.1', 'children' => [
					['label' => '3.1.1', 'children' => [
						['label' => '3.1.1.1', 'children' => []],
					]],
				]],

			]],
		]]
	];

	$recursion = new class implements \ILIAS\UI\Component\Tree\TreeRecursion
	{
		public function getChildren($record): array
		{
			return $record['children'];
		}

		public function build(
			\ILIAS\UI\Component\Tree\Node\Factory $factory,
			$record
		): \ILIAS\UI\Component\Tree\Node\Node {
			$label = $record['label'];
			return $factory->simple($label);
		}
	};

	$tree = $f->tree()->tree($recursion)
		->withData($data);

	return $renderer->render($tree);
}