<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Tooltip;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Tooltip
 * @author Niels Theen <ntheen@databay.de>
 * @author Colin Kiegel <kiegel@qualitus.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class Renderer extends AbstractComponentRenderer
{
	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer)
	{
		/** @var Component\Tooltip\Tooltip $component */
		$this->checkComponent($component);

		$options = array(
			'placement' => $component->getPlacement(),
		);

		$tpl = $this->getTemplate('tpl.standard-tooltip-content.html', true, true);
		$tpl->setVariable('CONTENT', $default_renderer->render($component->contents()));

		$show = $component->getShowSignal();

		$component = $component->withAdditionalOnLoadCode(function ($id) use ($options, $show) {
			$options['contentId'] = $id;
			$options = json_encode($options);

			return
				"$(document).on('{$show}', function(event, signalData) {
					il.UI.tooltip.showFromSignal(signalData, JSON.parse('{$options}'));
				});";
		});

		$id = $this->bindJavaScript($component);

		$tpl->setVariable('ID', $id);

		return  $tpl->get();
	}


	/**
	 * @inheritdoc
	 */
	public function registerResources(ResourceRegistry $registry)
	{
		parent::registerResources($registry);
		$registry->register('./libs/yarn/node_modules/popper.js/dist/umd/popper.js');
		$registry->register('./libs/yarn/node_modules/tippy.js/umd/index.all.js');
		$registry->register('./src/UI/templates/js/Tooltip/tooltip.js');
	}


	/**
	 * @param Component\Tooltip\Standard $tooltip
	 * @param RendererInterface $default_renderer
	 * @param string $id
	 *
	 * @return string
	 */
	protected function renderStandardTooltip(
		Component\Tooltip\Standard $tooltip,
		RendererInterface $default_renderer,
		$id
	) {
		$tpl = $this->getTemplate('tpl.tooltip.html', true, true);
		$tpl->setVariable('ID', $id);
		$tpl->setVariable('CONTENT', $default_renderer->render($tooltip->contents()));

		return $tpl->get();
	}


	/**
	 * @param string $str
	 *
	 * @return string
	 */
	protected function escape($str)
	{
		return strip_tags(htmlentities($str, ENT_QUOTES, 'UTF-8'));
	}


	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName()
	{
		return [
			Component\Tooltip\Standard::class
		];
	}
}
