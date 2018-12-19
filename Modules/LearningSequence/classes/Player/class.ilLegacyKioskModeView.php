<?php

declare(strict_types=1);

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\KioskMode\State;
use ILIAS\KioskMode\URLBuilder;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilLegacyKioskModeView
 */
class ilLegacyKioskModeView implements ILIAS\KioskMode\View
{
	const CMD_START_OBJECT = 'start_legacy_obj';

	protected $object;

	public function __construct(
		ilObject $object,
		ilLanguage $lng,
		ilAccess $access
	) {
		$this->object = $object;
		$this->lng = $lng;
		$this->access = $access;
	}

	/**
	 * @inheritDoc
	 */
	protected function getObjectClass(): string
	{
		return get_class($this->object);
	}

	protected function getObjectTitle(): string
	{
		return $this->object->getTitle();
	}

	/**
	 * @inheritDoc
	 */
	protected function setObject(\ilObject $object)
	{
		$this->object = $object;
	}

	/**
	 * @inheritDoc
	 */
	protected function hasPermissionToAccessKioskMode(): bool
	{
		return true;
		//return $this->access->checkAccess('read', '', $this->contentPageObject->getRefId());
	}

	/**
	 * @inheritDoc
	 */
	public function buildInitialState(State $empty_state): State
	{
		return $empty_state;
	}

	/**
	 * @inheritDoc
	 */
	public function buildControls(State $state, ControlBuilder $builder): ControlBuilder
	{
		$builder->start (
			'start ' .$this->getObjectClass(),
			self::CMD_START_OBJECT,
			0
		);

		//return $this->debugBuildAllControls($builder);
		return $builder;
	}

	/**
	 * @inheritDoc
	 */
	public function updateGet(State $state, string $command, int $param = null): State
	{
		if($command === self::CMD_START_OBJECT) {
			$url = \ilLink::_getStaticLink(
				$this->object->getRefId(),
				$this->object->getType(),
				true,
				false
			);

			print implode("\n", [
				'<script>',
					'var il_ls_win = window.open("' .$url .'"),',
					' 	il_ls_win_watch = setInterval(',
					'		function(){',
					'			if (il_ls_win.closed) {',
					'				clearInterval(il_ls_win_watch);',
					'				var url = location.toString().replace("start_legacy_obj", "x_");',
					'				location.replace(url);',
					'			}',
					' 		},',
					' 		1000',
					'	);',
				'</script>',
			]);
		}
		return $state;
	}

	/**
	 * @inheritDoc
	 */
	public function updatePost(State $state, string $command, array $post): State
	{
		return $state;
	}

	/**
	 * @inheritDoc
	 */
	public function render(
		State $state,
		Factory $factory,
		URLBuilder $url_builder,
		array $post = null
	): Component {

		$obj_type = $this->object->getType();
		$obj_type_txt = $this->lng->txt('obj_'. $obj_type);
		$icon = $factory->icon()->standard($obj_type, $obj_type_txt, 'large');
		$md = $this->getMetadata((int)$this->object->getId(), $obj_type);
		$props = array_merge(
			[$this->lng->txt('obj_type') => $obj_type_txt],
			$this->getMetadata((int)$this->object->getId(), $obj_type)
		);

		$info =  $factory->item()->standard($this->object->getTitle())
			->withLeadIcon($icon)
			->withDescription($this->object->getDescription())
			->withProperties($props);

		return $info;
	}

	//TODO: enhance metadata
	private function getMetadata(int $obj_id, string $type): array
	{
		$md = new ilMD($obj_id, 0, $type);
		$meta_data = [];

		$section = $md->getGeneral();

		$meta_data['language'] = [];
		foreach ($section->getLanguageIds() as $id) {
			$meta_data['language'][] = $section->getLanguage($id)->getLanguageCode();
		}
		$meta_data['keywords'] = [];
		foreach ($section->getKeywordIds() as $id) {
			$meta_data['keywords'][] = $section->getKeyword($id)->getKeyword();
		}

		$md_flat = [];
		foreach ($meta_data as $md_label => $values) {
			if(count($values) > 0) {
				$md_flat[$this->lng->txt($md_label)] = implode(', ', $values);
			}
		}
		return $md_flat;
	}

	private function debugBuildAllControls(ControlBuilder $builder): ControlBuilder
	{
		$builder

		->tableOfContent($this->getObjectTitle(), 'kommando', 666)
			->node('node1')
				->item('item1.1', 1)
				->item('item1.2', 11)
				->end()
			->item('item2', 111)
			->node('node3', 1111)
				->item('item3.1', 2)
				->node('node3.2')
					->item('item3.2.1', 122)
				->end()
			->end()
			->end()

		->locator('locator_cmd')
			->item('item 1', 1)
			->item('item 2', 2)
			->item('item 3', 3)
			->end()

		->done('cmd', 1)
		->next('cmd', 1)
		->previous('', 1)
		//->exit('cmd', 1)
		->generic('cmd 1', 'x', 1)
		->generic('cmd 2', 'x', 2)
		//->toggle('toggle', 'cmd_on', 'cmd_off')
		->mode('modecmd', ['m1', 'm2', 'm3'])
		;

		return $builder;
	}

}
