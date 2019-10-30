<?php

require_once("libs/composer/vendor/autoload.php");

use ILIAS\UI\Implementation\Crawler\Exception as CrawlerException;
use ILIAS\UI\Implementation\Crawler as Crawler;

/**
 * Defines tests every SHOULD pass UI-factory. Checks as much rules as possible from
 * the 'Interfaces to Factories' part of the UI framework rules.
 *
 * TODO: This test heavily relies on data providers and dependencies. PHPUnit
 * does not support dependencies per provided data set via @depends, therefore
 * the test express the dependencies explicitly by calling subsequent testing
 * methods. These leads to test methods being executed to often.
 */
abstract class AbstractFactoryTest extends PHPUnit_Framework_TestCase
{
    const COMPONENT = 1;
    const FACTORY = 2;

    /* kitchensink info test configuration:
     * true = should be there, check
     * false = may be there, don't check
     * Notice, some properties (MUST/MUST NOT) will always be checked.
     */
    private $kitchensink_info_settings_default
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

    /* You can overwrite these settings per factory method when using this test
     * by writing $kitchensink_info_settings. See GlyphFactoryTest for an example.
     */


    // Definitions and Helpers:

    private $description_categories =
            array('purpose', 'composition', 'effect', 'rival');

    private $rules_categories =
            array('usage', 'interaction', 'wording', 'style', 'ordering', 'responsiveness',
                    'composition','accessibility');

    final protected function returnsFactory($docstring_data)
    {
        return $this->isFactoryName($docstring_data["namespace"]);
    }

    final protected function returnsComponent($docstring_data)
    {
        $reflection = new ReflectionClass($docstring_data["namespace"]);
        return in_array("ILIAS\\UI\\Component\\Component", $reflection->getInterfaceNames());
    }

    final protected function isFactoryName($name)
    {
        return preg_match("#^(\\\\)?ILIAS\\\\UI\\\\Component\\\\([a-zA-Z]+\\\\)*Factory$#", $name) === 1;
    }

    final public function buildFactoryReflection()
    {
        return new ReflectionClass($this->factory_title);
    }

    final public function methods_provider()
    {
        $reflection = $this->buildFactoryReflection();
        return array_map(function ($element) {
            return array($element, $element->getName());
        }, $reflection->getMethods());
    }

    // Setup

    public function setUp()
    {
        $this->yaml_parser = new Crawler\EntriesYamlParser();
        $this->reflection = $this->buildFactoryReflection();
    }

    public function test_proper_namespace()
    {
        $message = "TODO: Put your factory into the proper namespace.";
        $this->assertRegExp(
            "#^ILIAS\\\\UI\\\\Component.#",
            $this->reflection->getNamespaceName(),
            $message
        );
    }

    public function test_proper_name()
    {
        $name = $this->reflection->getName();
        $message = "TODO: Give your factory a proper name.";
        $this->assertTrue(
            $this->isFactoryName($name),
            $message
        );
    }

    /**
     * Tests whether the YAML Kitchen Sink info can be parsed.
     *
     * @dataProvider methods_provider
     */
    final public function test_check_yaml_extraction($method_reflection, $name)
    {
        try {
            //Todo (TA) this is not pretty. We should think about using only reflection in the parser as well.
            $function_name_string = "\n public function " . $method_reflection->getName() . "()";
            $docstring_data = $this->yaml_parser->parseArrayFromString(
                $method_reflection->getDocComment() . $function_name_string
            );
            $this->assertTrue(true);
        } catch (CrawlerException\CrawlerException $e) {
            $message = "TODO ($name): fix parse error in kitchen sink yaml: " . $e->getMessage();
            $this->assertTrue(false, $message);
        }
        $this->assertCount(1, $docstring_data);
        return $docstring_data[0];
    }

    /**
     * Tests whether the method either returns a factory or a component.
     *
     * @dataProvider methods_provider
     */
    final public function test_return_type($method_reflection, $name)
    {
        $message = "TODO ($name): fix return type, it must be a factory or a component.";
        $docstring_data = $this->test_check_yaml_extraction($method_reflection, $name);
        if ($this->returnsFactory($docstring_data)) {
            $this->assertTrue(true);
        } elseif ($this->returnsComponent($docstring_data)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false, $message);
        }
    }

    /**
     * Tests whether the method name matches the @return doctring?
     *
     * @dataProvider methods_provider
     */
    final public function test_factory_method_name_compatible_docstring($method_reflection, $name)
    {
        $docstring_data = $this->test_check_yaml_extraction($method_reflection, $name);
        $this->test_return_type($method_reflection, $name);

        $return_doc = $docstring_data["namespace"];
        $name_uppercase = ucwords($name);
        $regex_factory_namespace = $this->get_regex_factory_namespace();
        $regex_head = "#^(\\\\?)$regex_factory_namespace";

        $message = "TODO ($name): fix @return, it does not match the method name.";
        if ($this->returnsFactory($docstring_data)) {
            $this->assertRegExp(
                "$regex_head\\\\$name_uppercase\\\\Factory$#",
                $return_doc,
                $message
            );
        } else { // returnsComponent
            // Every component MUST be described by a single interface, where the name of
            // the interface corresponds to the name of the component.
            $standard_pattern = "$regex_head\\\\$name_uppercase#";
            $standard_case = preg_match($standard_pattern, $return_doc);

            // unless they only differ in a type and share a common prefix to their pathes.
            $namespace_parts = explode("\\", $this->reflection->getNamespaceName());
            $typediff_only_pattern = "$regex_head\\\\" . array_pop($namespace_parts) . "#";
            $typediff_only_case = preg_match($typediff_only_pattern, $return_doc);

            $this->assertTrue($standard_case || $typediff_only_case, $message);
        }
    }

    protected function get_regex_factory_namespace()
    {
        return str_replace("\\", "\\\\", $this->reflection->getNamespaceName());
    }

    /**
     * Tests whether methods returning factories have no parameters.
     *
     * @dataProvider methods_provider
     */
    final public function test_method_params($method_reflection, $name)
    {
        $docstring_data = $this->test_check_yaml_extraction($method_reflection, $name);
        if ($this->returnsFactory($docstring_data)) {
            $message = "TODO ($name): remove params from method that returns Factory.";
            $this->assertEquals(0, $method_reflection->getNumberOfParameters(), $message);
        }
    }

    // Common rules for all factory methods, regardless whether they return other
    // factories or components.

    /**
     * @dataProvider methods_provider
     */
    final public function test_kitchensink_info_description($method_reflection, $name)
    {
        $docstring_data = $this->test_check_yaml_extraction($method_reflection, $name);
        $kitchensink_info_settings = $this->kitchensink_info_settings_merged_with_defaults($name);

        if ($kitchensink_info_settings['description']) {
            $message = "TODO ($name): add a description.";
            $this->assertArrayHasKey('description', $docstring_data, $message);

            $desc_fields = implode(", ", $this->description_categories);
            $message = "TODO ($name): the description field should at least contain one of these: $desc_fields.";
            $existing_keys = array_keys($docstring_data["description"]);
            $existing_expected_keys = array_intersect($this->description_categories, $existing_keys);
            $this->assertGreaterThanOrEqual(
                1,
                $existing_expected_keys,
                $message
            );
        }
    }

    /**
     * @dataProvider methods_provider
     */
    final public function test_kitchensink_info_rivals($method_reflection, $name)
    {
        $docstring_data = $this->test_check_yaml_extraction($method_reflection, $name);
        if (isset($docstring_data["description"]) && isset($docstring_data["description"]["rivals"])) {
            $rules = $docstring_data["description"]["rivals"];
            $message = "TODO ($name): The Rivals field has a non-string index. Format like 'rival_name': 'description'";
            $this->assertTrue(array_unique(array_map("is_string", array_keys($rules))) === array(true), $message);
        }
        $this->assertTrue(true);
    }

    /**
     * @dataProvider methods_provider
     */
    final public function test_kitchensink_info_background($method_reflection, $name)
    {
        $docstring_data = $this->test_check_yaml_extraction($method_reflection, $name);
        $kitchensink_info_settings = $this->kitchensink_info_settings_merged_with_defaults($name);

        if ($kitchensink_info_settings['background']) {
            $message = "TODO ($name): add a background field.";
            $this->assertArrayHasKey('background', $docstring_data, $message);
        }
    }

    /**
     * @dataProvider methods_provider
     */
    final public function test_kitchensink_info_featurewiki($method_reflection, $name)
    {
        $docstring_data = $this->test_check_yaml_extraction($method_reflection, $name);
        $kitchensink_info_settings = $this->kitchensink_info_settings_merged_with_defaults($name);

        if ($kitchensink_info_settings['featurewiki']) {
            $message = "TODO ($name): add a featurewiki field.";
            $this->assertArrayHasKey('featurewiki', $docstring_data, $message);
        }
    }

    /**
     * @dataProvider methods_provider
     */
    final public function test_kitchensink_info_javascript($method_reflection, $name)
    {
        $docstring_data = $this->test_check_yaml_extraction($method_reflection, $name);
        $kitchensink_info_settings = $this->kitchensink_info_settings_merged_with_defaults($name);

        if ($kitchensink_info_settings['javascript']) {
            $message = "TODO ($name): add a javascript field.";
            $this->assertArrayHasKey('javascript', $docstring_data, $message);
        }
    }

    /**
     * @dataProvider methods_provider
     */
    final public function test_kitchensink_info_rules($method_reflection, $name)
    {
        $docstring_data = $this->test_check_yaml_extraction($method_reflection, $name);
        $kitchensink_info_settings = $this->kitchensink_info_settings_merged_with_defaults($name);

        if ($kitchensink_info_settings['rules']) {
            $message = "TODO ($name): add a rules field.";
            $this->assertArrayHasKey('rules', $docstring_data, $message);

            $rules_fields = implode(", ", $this->rules_categories);
            $message = "TODO ($name): the rules field should at least contain one of these: $rules_fields.";
            $existing_keys = array_keys($docstring_data["rules"]);
            $existing_expected_keys = array_intersect($this->rules_categories, $existing_keys);
            $this->assertGreaterThanOrEqual(
                1,
                $existing_expected_keys,
                $message
            );
        }
    }

    /**
     * @dataProvider methods_provider
     */
    final public function test_kitchensink_info_context($method_reflection, $name)
    {
        $docstring_data = $this->test_check_yaml_extraction($method_reflection, $name);
        $kitchensink_info_settings = $this->kitchensink_info_settings_merged_with_defaults($name);

        // Special rules for factory methods:
        if ($this->returnsFactory($docstring_data)) {
            $message = "TODO ($name): remove 'context' field, method returns a factory.";
            $this->assertArrayNotHasKey("context", $docstring_data, $message);
        } else { // returnsComponent
            if ($kitchensink_info_settings["context"]) {
                $message = "TODO ($name): factory method returning component should have context field. Add it.";
                $this->assertArrayHasKey("context", $docstring_data, $message);
            }
        }
    }

    final public function kitchensink_info_settings_merged_with_defaults($name)
    {
        if (array_key_exists($name, $this->kitchensink_info_settings)) {
            return array_merge(
                $this->kitchensink_info_settings_default,
                $this->kitchensink_info_settings[$name]
            );
        } else {
            return $this->kitchensink_info_settings_default;
        }
    }
}
