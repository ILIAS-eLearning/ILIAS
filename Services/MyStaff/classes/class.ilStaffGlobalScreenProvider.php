<?php

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;
use ILIAS\NavigationContext\ContextInterface;
use ILIAS\NavigationContext\Stack\CalledContexts;
use ILIAS\NavigationContext\Stack\ContextCollection;
use ILIAS\NavigationContext\Stack\ContextStack;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;

/**
 * Class ilStaffGlobalScreenProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilStaffGlobalScreenProvider extends AbstractStaticMainMenuProvider implements DynamicToolProvider {

	/**
	 * @var IdentificationInterface
	 */
	protected $top_item;


	/**
	 * @param Container $dic
	 */
	public function __construct(Container $dic) {
		parent::__construct($dic);
		$this->top_item = (new ilPDGlobalScreenProvider($dic))->getTopItem();
	}


	/**
	 * Some other components want to provide Items for the main menu which are
	 * located at the PD TopTitem by default. Therefore we have to provide our
	 * TopTitem Identification for others
	 *
	 * @return IdentificationInterface
	 */
	public function getTopItem(): IdentificationInterface {
		return $this->top_item;
	}


	/**
	 * @inheritDoc
	 */
	public function getStaticTopItems(): array {
		return [];
	}


	/**
	 * @inheritDoc
	 */
	public function getStaticSubItems(): array {
		$dic = $this->dic;

		return [$this->mainmenu->link($this->if->identifier('mm_pd_mst'))
			        ->withTitle($this->dic->language()->txt("my_staff"))
			        ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToMyStaff")
			        ->withParent($this->getTopItem())
			        ->withPosition(12)
			        ->withAvailableCallable(
				        function () use ($dic) {
					        return (bool)($dic->settings()->get("enable_my_staff"));
				        }
			        )
			        ->withVisibilityCallable(
				        function () {
					        return (bool)ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff();
				        }
			        )->withNonAvailableReason($dic->ui()->factory()->legacy("{$dic->language()->txt('component_not_active')}"))];
	}


	/**
	 * @inheritDoc
	 */
	public function isInterestedInContexts(): ContextCollection {
		return $this->dic->navigationContext()->collection()->desktop();
	}


	/**
	 * @inheritDoc
	 */
	public function getToolsForContextStack(CalledContexts $called_contexts): array {
		$tools = [];
		$last = $called_contexts->getLast();
		$additional_data = $last->getAdditionalData();
		$iff = function ($id) { return $this->globalScreen()->identification()->fromSerializedIdentification($id); };
		$l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };

		if ($additional_data->exists('mine')) {
			$tools[] = $this->mainmenu->tool($iff("providerXY|lorem_tool"))
				->withTitle("A Tool")
				->withContent($l("LOREM {$called_contexts->current()->getReferenceId()->toInt()}"));
			$tools[] = $this->mainmenu->tool($iff("providerXY|ipsum_tool"))
				->withTitle("A Second Tool")
				->withContent($l("IPSUM"));
		}

		$tools[] = $this->mainmenu->tool($iff("providerXY|stack_tool"))
			->withTitle("Stack")
			->withContent($l('<pre>' . print_r($called_contexts->getStackAsArray(), 1) . '</pre>'));

		return $tools;
	}
}
