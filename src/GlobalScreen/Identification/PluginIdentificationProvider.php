<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Class PluginIdentificationProvider
 *
 * @see    IdentificationProviderInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PluginIdentificationProvider implements IdentificationProviderInterface {

	/**
	 * @var string
	 */
	protected $class_name = '';
	/**
	 * @var string
	 */
	protected $plugin_id = "";


	/**
	 * PluginIdentificationProvider constructor.
	 *
	 * @param string $class_name
	 * @param string $plugin_id
	 */
	public function __construct(string $class_name, string $plugin_id) {
		$this->class_name = $class_name;
		$this->plugin_id = $plugin_id;
	}


	/**
	 * @inheritdoc
	 */
	public function identifier(string $identifier_string): IdentificationInterface {
		return new PluginIdentification($identifier_string, $this->class_name, $this->plugin_id);
	}
}
