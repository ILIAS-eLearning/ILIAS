<?php

require_once("libs/composer/vendor/autoload.php");
use ILIAS\UI\Implementation\Crawler\Exception as CrawlerException;
use ILIAS\UI\Implementation\Crawler as Crawler;

/**
 * Defines tests every UI-factory MUST pass.
 */
abstract class AbstractFactoryTest extends PHPUnit_Framework_TestCase {

	const IS_COMPONENT = 1;
	const IS_FACTORY = 2;

	public static $factoryReflection;
	/**
	 * kitchensink info test configuration:
	 * true = should be there, check
	 * false = may be there, don't check
	 * Notice, some properties (MUST/MUST NOT) will allways be checked.
	 */
	protected $kitchensink_info_settings_default
		= array('description'
					=> true
				,'background'
					=> false
				,'context'
					=> true
				,'featurewiki'
					=> false
				,'javascript'
					=> false
				,'rules'
					=> true);

	protected $description_categories =
		array('purpose','composition','effect','rival');
	protected $rules_categories =
		array('usage','interaction','wording','style','ordering','responsiveness','composition','accessibility');
	public function setUp() {
		$this->yaml_parser = new Crawler\EntriesYamlParser();
	}

	final public function test_proper_namespace() {
		$this->assertRegExp("#^ILIAS\\\\UI\\\\Component.#", self::$factoryReflection->getNamespaceName());
	}

	final public function test_proper_name() {
		$this->assertTrue($this->isFactoryName( self::$factoryReflection->getName()));
	}

	final protected function setupMethodTestcase(ReflectionMethod $method_reflection) {
		try {
			$docstring_data = $this->yaml_parser->parseArrayFromString($method_reflection->getDocComment())[0];
		} catch (CrawlerException\CrawlerException $ex) {
			$this->assertFalse($ex->getMessage(),$method_reflection->getName().": parse error kitchensink YAML string.");
		}
		$method_name = $method_reflection->getName();
		if($this->returnsFactory($docstring_data)) {
			$type = self::IS_FACTORY;
		} elseif($this->returnsComponent($docstring_data, $method_name, $method_reflection)) {
			$type = self::IS_COMPONENT;
		} else {
			$this->assertFalse(	"Method ".$method_name." seems to return neither factory nor component."
							   ."Please check the @return docstring of your interface."
							   ."If this method is supposed to return a component, please ensure "
							   ."the existense of the corresponding interface.");
		}
		return array(
			"method_reflection" => $method_reflection
			,"method_name" => $method_name
			,"docstring_data" => $docstring_data
			,"type" => $type);
	}

	/**
	 * Tests, wether the YAML Kitchen Sink info may be parsed.
	 *
	 * @dataProvider methods
	 */
	final public function test_check_yaml_extraction($method_reflection) {
		try {
			$docstring_data = $this->yaml_parser->parseArrayFromString($method_reflection->getDocComment())[0];
			$this->assertTrue(true);
		} catch (CrawlerException\CrawlerException $ex) {
			$this->assertFalse($ex->getMessage(),$method_reflection->getName().": parse error kitchensink YAML string.");
		}
	}

	/**
	 * Tests the method name. Does it match the return doctring?
	 *
	 * @dataProvider methods
	 */
	final public function test_factory_method_name_compatible_docstring($method_reflection) {
		$param = $this->setupMethodTestcase($method_reflection);

		if($param["type"] === self::IS_FACTORY ) {
			$this->checkFactoryMethodNameCompatibleDocstring($param["docstring_data"],$param["method_name"]);
		}
		if($param["type"] ===  self::IS_COMPONENT ) {
			$this->checkComponentMethodNameCompatibleDocstring($param["docstring_data"],$param["method_name"]);
		}
	}

	/**
	 * Tests the method parameters. Methodfs returning factories must not have parameters.
	 *
	 * @dataProvider methods
	 */
	final public function test_method_params($method_reflection) {
		$param = $this->setupMethodTestcase($method_reflection);
		if($param["type"] === self::IS_FACTORY ) {
			$this->assertCount(0,$param["method_reflection"]->getNumberOfParameters()
				,$emthod_reflection->getName().": method representing an abstract node must not have parameters.");
		}
		if($param["type"] ===  self::IS_COMPONENT ) {
			$this->assertTrue(true);
		}
	}

	/**
	 * Tests the content of the YAML Kithcen Sink information.
	 *
	 * @dataProvider methods
	 */
	final public function test_kitchensink_info($method_reflection) {
		$param = $this->setupMethodTestcase($method_reflection);

		$kitchensink_info_settings	=
			array_merge($this->kitchensink_info_settings_default
				,isset($this->kitchensink_info_settings[$param["method_name"]]) ?
						$this->kitchensink_info_settings[$param["method_name"]] :
						array());

		if($param["type"] === self::IS_FACTORY ) {
			$this->checkFactoryDocstringData($param["docstring_data"]
				,$kitchensink_info_settings
				,$param["method_name"]);
		}
		if($param["type"] ===  self::IS_COMPONENT ) {
			$this->checkComponentDocstringData($param["docstring_data"]
				,$kitchensink_info_settings
				,$param["method_name"]);
		}
	}

	final public function methods() {
		if(!self::$factoryReflection) {
			self::$factoryReflection = new ReflectionClass(static::$factory_title);
		}
		return array_map(function($element) {return array($element);}
							,self::$factoryReflection->getMethods());
	}

	final protected function checkFactoryDocstringData(array $docstring_data,array $kitchensink_info_settings,$method_name) {
		if(isset($docstring_data['context'])) {
			$this->assertFalse($method_name.": factories must not have context");
		}
		$this->checkCommonDocstringData($docstring_data,$kitchensink_info_settings,$method_name);

	}

	final protected function checkComponentDocstringData(array $docstring_data,array $kitchensink_info_settings,$method_name) {
		if($kitchensink_info_settings['context']) {
			$this->assertArrayHasKey('context',$docstring_data,$method_name);		
		}
		$this->checkCommonDocstringData($docstring_data,$kitchensink_info_settings,$method_name);
	}

	final protected function checkCommonDocstringData(array $docstring_data,array $kitchensink_info_settings,$method_name) {
		if($kitchensink_info_settings['description']) {
			$this->assertArrayHasKey('description',$docstring_data,$method_name);
			$this->assertGreaterThanOrEqual(1,
				count(array_intersect(
				array_keys($docstring_data['description']), $this->description_categories))
					,$method_name.": description should contain at least one of the following "
					.implode(", ",$this->description_categories).".");
		}
		if($kitchensink_info_settings['background']) {
			$this->assertArrayHasKey('background',$docstring_data,$method_name);
		}
		if($kitchensink_info_settings['featurewiki']) {
			$this->assertArrayHasKey('featurewiki',$docstring_data,$method_name);
		}
		if($kitchensink_info_settings['javascript']) {
			$this->assertArrayHasKey('javascript',$docstring_data,$method_name);
		}
		if($kitchensink_info_settings['rules']) {
			$this->assertArrayHasKey('rules',$docstring_data,$method_name);
			$this->assertGreaterThanOrEqual(1,
				count(array_intersect(
				array_keys($docstring_data['rules']), $this->rules_categories))
					,$method_name.": description should contain at least one of the following"
					.implode(", ",$this->rules_categories).".");
			$rule_indices = array();
			foreach ($docstring_data['rules'] as $rule_category => $rules) {
				foreach ($rules as $rule_index => $rule) {
					$this->assertTrue(is_numeric($rule_index), $method_name.": rule indices must be numeric");
					$rule_indices[] = (int)$rule_index;
				}
			}
			$num_rules = count($rule_indices);
			$cnt_start = min($rule_indices);
			$cnt = 1;
			while($cnt < $num_rules) {
				$this->assertTrue(in_array($cnt_start + $cnt, $rule_indices), $method_name.": rule indices must be successive");
				$cnt++;
			}
		}
	}

	final public function checkFactoryMethodNameCompatibleDocstring($docstring_data,$method_name) {
		$return_doc = $docstring_data["namespace"];
		$method_name_uppercase = ucwords($method_name);
		$this->assertRegExp("#^(\\\\?)"
					.str_replace("\\", "\\\\", self::$factoryReflection->getNamespaceName())
					."\\\\".$method_name_uppercase."\\\\Factory$#", $return_doc
				, $method_name.": it seems the the @return docstring does not match the method name");
	}

	final public function checkComponentMethodNameCompatibleDocstring($docstring_data,$method_name) {
		$return_doc = $docstring_data["namespace"];
		$this->assertRegExp("#^(\\\\?)".str_replace("\\", "\\\\", self::$factoryReflection->getNamespaceName())."\\\\*#", $return_doc
			, $method_name.": it seems the the @return docstring does not match the method name");
	}

	final protected function returnsFactory($docstring_data) {
		return $this->isFactoryName($docstring_data["namespace"]);
	}

	final protected function returnsComponent($docstring_data) {
		$reflection = new ReflectionClass($docstring_data["namespace"]);
		return in_array("ILIAS\\UI\\Component\\Component", $reflection->getInterfaceNames());
	}

	final protected function isFactoryName($name) {
		return preg_match("#^ILIAS\\\\UI\\\\Component\\\\([a-zA-Z]+\\\\)*Factory$#", $name) === 1;
	}
}