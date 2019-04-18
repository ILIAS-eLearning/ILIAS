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
 * @author Coling Kiegel <kiegel@qualitus.de>
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

		$replacement = array(
			'"' => '\"',
			"\n" => "",
			"\t" => "",
			"\r" => "",
		);

		if ($component instanceof Component\Tooltip\Standard) {
			$tpl = $this->getTemplate('tpl.tooltip.html', true, true);
			$tpl->setVariable('CONTENT', $default_renderer->render($component->contents()));

			$options['content'] = str_replace(array_keys($replacement), array_values($replacement), $tpl->get());
		}

		$show = $component->getShowSignal();

		$component = $component->withAdditionalOnLoadCode(function ($id) use ($options, $show) {
			$options = json_encode($options);

			return
				"$(document).on('{$show}', function(event, signalData) {
					il.UI.tooltip.showFromSignal(signalData, JSON.parse('{$options}'));
				});";
		});

		$id = $this->bindJavaScript($component);

		// TODO: Dependency handling
		return
			'<script src="https://unpkg.com/popper.js@1/dist/umd/popper.min.js"></script>
<script src="https://unpkg.com/tippy.js@4"></script>';
	}


	/**
	 * @inheritdoc
	 */
	public function registerResources(ResourceRegistry $registry)
	{
		parent::registerResources($registry);
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
