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
			$ref_id = $record['ref_id'];
			$label = $record['title']
				. ' (' .$ref_id .')';

			$url = $this->getAsyncURL($environment, $ref_id);

			$node = $factory->simple($label)
				->withAsyncURL($url);

			//find these under ILIAS->Administration in the example tree
			if((int)$ref_id > 9 && (int)$ref_id < 20){
				$label = $environment['modal']->getShowSignal()->getId();
				$node = $factory->simple($label)
					->withAsyncURL($url)
					->withOnClick($environment['modal']->getShowSignal());
			}

			return $node;
		}

		protected function getAsyncURL($environment, string $ref_id): string
		{
			$url = $environment['url'];
			$base = substr($url, 0, strpos($url, '?') + 1);
			$query = parse_url($url, PHP_URL_QUERY);
			parse_str($query, $params);
			$params['async_ref'] = $ref_id;
			$url = $base .http_build_query($params);
			return $url;
		}

	};

	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$image = $f->image()->responsive("src/UI/examples/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
	$page = $f->modal()->lightboxImagePage($image, 'Mountains');
	$modal = $f->modal()->lightbox($page);

	$environment = [
		'url' => $DIC->http()->request()->getRequestTarget(),
		'modal' => $modal
	];

	$tree = $f->tree()->tree($recursion)
		->withEnvironment($environment)
		->withData($data);

	if(! $do_async) {
		return $renderer->render([$modal, $tree]);
	} else {
		echo $renderer->renderAsync([$modal, $tree]);
	}
}