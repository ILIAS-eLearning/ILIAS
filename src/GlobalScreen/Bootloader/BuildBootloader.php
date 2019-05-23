<?php namespace ILIAS\GlobalScreen\BootLoader;

use Composer\Script\Event;
use ILIAS\Collector\AbstractComposerEventHandler;
use ILIAS\Collector\AbstractComposerScript;
use ILIAS\Collector\Artifacts\AbstractClassNameCollectionArtifact;
use ILIAS\Collector\Artifacts\Artifact;
use ILIAS\Collector\EventHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/**
 * Class BuildBootLoader
 *
 * @package ILIAS\GlobalScreen\BootLoader
 */
class BuildBootLoader extends AbstractComposerScript {

	/**
	 * @inheritDoc
	 */
	protected static function getEventHandler(Event $event): EventHandler {

		return new class($event) extends AbstractComposerEventHandler implements EventHandler {

			/**
			 * @var array
			 */
			protected $class_names = [];


			/**
			 * @inheritDoc
			 */
			public function run(): void {
				chdir(getcwd() . "/../../");
				require_once('./libs/composer/vendor/autoload.php');

				$i = [
					StaticMainMenuProvider::class,
					StaticMetaBarProvider::class,
					DynamicToolProvider::class,
				];

				foreach ($i as $interface) {
					$this->runForInterface($interface);
				}
			}


			private function runForInterface(string $interface): void {
				$this->IO()->write("Collecting all {$interface} in ILIAS");

				$i = new InterfaceFinder($interface, '/.+\/class\.(il.+)\.php/i', "Services");
				foreach ($i->getFiles() as $file) {
					$this->class_names[$interface][] = $file;
				}
			}


			/**
			 * @inheritDoc
			 */
			public function getArtifact(): Artifact {
				return new class("global_screen_bootloader", $this->class_names) extends AbstractClassNameCollectionArtifact implements Artifact {

					/**
					 * @var array
					 */
					private $class_names = [];


					/**
					 *  constructor.
					 *
					 * @param array $instances
					 */
					public function __construct(string $filename, array $instances) {
						parent::__construct($filename);
						$this->class_names = $instances;
					}


					/**
					 * @inheritDoc
					 */
					protected function getClassNames(): array {
						return $this->class_names;
					}
				};
			}
		};
	}
}



