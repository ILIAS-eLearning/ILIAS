<?php

if ($_GET['async_ref']) {
	$ref = (int)$_GET['async_ref'];
	tree_async($ref);
	exit();
}


function tree_async($ref = null) {
	global $DIC;
	$ilTree = $DIC['tree'];

	if(is_null($ref)) {
		$do_async = false;
		$ref = 1;
		$data = array(
			$ilTree->getNodeData(1)
		);
	} else {
		$do_async = true;
		$data = $ilTree->getChilds($ref);
		if(count($data) === 0) {
			return;
		}
	}

	$recursion = new class implements \ILIAS\UI\Component\Tree\TreeRecursion
	{
		public function getChildren($record, $environment = null): array
		{
			return [];
		}

		public function build(
			\ILIAS\UI\Component\Tree\Node\Factory $factory,
			$record,
			$environment = null
		): \ILIAS\UI\Component\Tree\Node\Node {
			$label = $record['title'];

			$url = $environment['url'];
			$base = substr($url, 0, strpos($url, '?') + 1);
			$query = parse_url($url, PHP_URL_QUERY);
			parse_str($query, $params);
			$params['async_ref'] = $record['ref_id'];
			$url = $base .http_build_query($params);

			$label .= ' (' .$record['ref_id'] .')';

			$node = $factory->simple($label)
				->withAsyncURL($url);

			return $node;
		}
	};

	$environment = [
		'url' => $DIC->http()->request()->getRequestTarget()
	];


	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$tree = $f->tree()->tree($recursion)
		->withEnvironment($environment)
		->withData($data);

	if(! $do_async) {
		return $renderer->render($tree);
	} else {
		echo $renderer->render($tree);
	}
}