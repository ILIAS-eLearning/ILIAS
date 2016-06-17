<?php

require_once("libs/composer/vendor/autoload.php");
use ILIAS\UI\Implementation\Crawler\Exception as CrawlerException;
use ILIAS\UI\Implementation\Crawler as Crawler;

/**
 * Defines tests every UI-factory should pass.
 */
abstract class AbstractFactoryTest extends PHPUnit_Framework_TestCase {

	const IS_COMPONENT = 1;
	const IS_FACTORY = 2;

	public static $factoryReflection;
	/**
	 * 1 = must be there, check
	 * 0 = may be there, don't check
	 */

	
	protected $kitchensink_component_info_settings_def
		= array('description' 	=> 1
				,'background' 	=> 0
				,'context'		=> 1
				,'featurewiki'	=> 0
				,'javascript'	=> 0
				,'rules'		=> 1);
	/**
	 * Returns a fully quilified name of the factory interface.
	 */
	abstract static public function getFactoryTitle();

	public function setUp() {
		$this->yaml_parser = new Crawler\EntriesYamlParser();
	}

	public function test_proper_namespace() {
		$this->assertRegExp("#^ILIAS\\\\UI\\\\Component.#", self::$factoryReflection->getNamespaceName());
	}

	public function test_proper_name() {
		$this->assertTrue($this->isFactoryName( self::$factoryReflection->getName()));
	}

	protected function setupMethodTestcase(ReflectionMethod $method_reflection) {
		$docstring_data = $this->yaml_parser->parseArrayFromString($method_reflection->getDocComment())[0];
		$method_name = $method_reflection->getName();
		if($this->returnsFactory($docstring_data)) {
			$type = self::IS_FACTORY;
		} elseif($this->returnsComponent($docstring_data, $method_name, $method_reflection)) {
			$type = self::IS_COMPONENT;
		} else {
			$this->assertFalse(	"Method ".$method_name." seems to return neither factory nor component."
							   ."Please check the @return docstring of your interface."
							   ."If this method is supposed to return a component, please make ensure "
							   ."the existense of the corresponding iterface.");
		}
		return array(
			"method_reflection" => $method_reflection
			,"method_name" => $method_name
			,"docstring_data" => $docstring_data
			,"type" => $type);
	}

	/**
	 * @dataProvider methods
	 */
	public function test_check_yaml_extraction($method_reflection) {
		try {
			$docstring_data = $this->yaml_parser->parseArrayFromString($method_reflection->getDocComment())[0];
			$this->assertTrue(true);
		} catch (CrawlerException\CrawlerException $ex) {
			$this->assertFalse($ex->getMessage);
		}
	}

	/**
	 * @dataProvider methods
	 */
	public function test_factory_method_name_compatible_docstring($method_reflection) {
		$param = $this->setupMethodTestcase($method_reflection);

		if($param["type"] === self::IS_FACTORY ) {
			$this->checkFactoryMethodNameCompatibleDocstring($param["docstring_data"],$param["method_name"]);
		}
		if($param["type"] ===  self::IS_COMPONENT ) {
			$this->checkComponentMethodNameCompatibleDocstring($param["docstring_data"],$param["method_name"]);
		}
	}

	/**
	 * @dataProvider methods
	 */
	public function test_method_params($method_reflection) {
		$param = $this->setupMethodTestcase($method_reflection);
		if($param["type"] === self::IS_FACTORY ) {
			$this->assertCount(0,$param["method_reflection"]->getNumberOfParameters());
		}
		if($param["type"] ===  self::IS_COMPONENT ) {
			$this->assertTrue(true);
		}
	}

	/**
	 * @dataProvider methods
	 */
	public function test_kitchensink_info($method_reflection) {
		$param = $this->setupMethodTestcase($method_reflection);
		if($param["type"] === self::IS_FACTORY ) {
			$this->checkFactoryDocstringData($param["docstring_data"]
				,$this->kitchensink_info_settings[$param["method_name"]]
				,$param["method_name"]);
		}
		if($param["type"] ===  self::IS_COMPONENT ) {
			$this->checkComponentDocstringData($param["docstring_data"]
				,$this->kitchensink_info_settings[$param["method_name"]]
				,$param["method_name"]);
		}
	}

	public function methods() {
		if(!self::$factoryReflection) {
			self::$factoryReflection = new ReflectionClass(static::getFactoryTitle());
		}
		return array_map(function($element) {return array($element);}
							,self::$factoryReflection->getMethods());
	}

	protected function checkFactoryDocstringData(array $docstring_data,array $kitchensink_info_settings,$method_name) {
		if(isset($docstring_data['context'])) {
			$this->assertFalse($method_name.": factories must not have context");
		}
		$this->checkCommonDocstringData($docstring_data,$kitchensink_info_settings,$method_name);

	}

	protected function checkComponentDocstringData(array $docstring_data,array $kitchensink_info_settings,$method_name) {
		if(1 === $kitchensink_info_settings['context']) {
			$this->assertArrayHasKey('context',$docstring_data,$method_name);		
		}
		$this->checkCommonDocstringData($docstring_data,$kitchensink_info_settings,$method_name);
	}

	protected function checkCommonDocstringData(array $docstring_data,array $kitchensink_info_settings,$method_name) {
		if(1 === $kitchensink_info_settings['description']) {
			$this->assertArrayHasKey('description',$docstring_data,$method_name);
		}
		if(1 === $kitchensink_info_settings['description']) {
			$this->assertArrayHasKey('description',$docstring_data,$method_name);
			$this->assertGreaterThanOrEqual(1,
				count(array_intersect(
				array_keys($docstring_data['description']), 
				array('purpose','composition','effect','rival'))));
		}
		if(1 === $kitchensink_info_settings['background']) {
			$this->assertArrayHasKey('background',$docstring_data,$method_name);
		}
		if(1 === $kitchensink_info_settings['featurewiki']) {
			$this->assertArrayHasKey('featurewiki',$docstring_data,$method_name);
		}
		if(1 === $kitchensink_info_settings['javascript']) {
			$this->assertArrayHasKey('javascript',$docstring_data,$method_name);
		}
		if(1 === $kitchensink_info_settings['rules']) {
			$this->assertArrayHasKey('rules',$docstring_data,$method_name);
			$this->assertGreaterThanOrEqual(1,
				count(array_intersect(
				array_keys($docstring_data['rules']), 
				array('usage','interaction','wording','style','ordering','responsiveness','composition','accessibility'))));
			$rule_indices = array();
			foreach ($docstring_data['rules'] as $rule_category => $rules) {
				foreach ($rules as $rule_index => $rule) {
					$this->assertTrue(is_numeric($rule_index));
					$rule_indices[] = (int)$rule_index;
				}
			}
			$num_rules = count($rule_indices);
			$cnt_start = min($rule_indices);
			$cnt = 1;
			while($cnt < $num_rules) {
				$this->assertTrue(in_array($cnt_start + $cnt, $rule_indices));
				$cnt++;
			}
		}
	}

	public function checkFactoryMethodNameCompatibleDocstring($docstring_data,$method_name) {
		$return_doc = $docstring_data["namespace"];
		$method_name_uppercase = ucwords($method_name);
		$this->assertRegExp("#^(\\\\?)"
					.str_replace("\\", "\\\\", self::$factoryReflection->getNamespaceName())
					."\\\\".$method_name_uppercase."\\\\Factory$#", $return_doc);
	}

	public function checkComponentMethodNameCompatibleDocstring($docstring_data,$method_name) {
		$return_doc = $docstring_data["namespace"];
		$this->assertRegExp("#^(\\\\?)".str_replace("\\", "\\\\", self::$factoryReflection->getNamespaceName())."\\\\*#", $return_doc);
	}

	protected function returnsFactory($docstring_data) {
		return $this->isFactoryName($docstring_data["namespace"]);
	}

	protected function returnsComponent($docstring_data) {
		$reflection = new ReflectionClass($docstring_data["namespace"]);
		return in_array("ILIAS\\UI\\Component\\Component", $reflection->getInterfaceNames());
	}

	protected function isFactoryName($name) {
		return preg_match("#^ILIAS\\\\UI\\\\Component\\\\([a-zA-Z]+\\\\)*Factory$#", $name) === 1;
	}

}