<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

require_once(dirname(__FILE__) . '/templates/testrail.case_ids.php');

use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntry;
use ILIAS\UI\Implementation\Crawler\ExamplesYamlParser;

class TestRailXMLWriter
{
    use TestrailCaseIds;
    protected const SHOW = 'show';
    protected const VALIDATE = 'validate';
    protected const BASE = 'UIBASE';
    protected const OPEN = 'open';
    protected const PREPARE = 'prepare';

    protected SimpleXMLElement $xml;

    public function __construct(
        protected \ilTemplate $case_tpl,
        protected ExamplesYamlParser $parser
    ) {
        $this->xml = new SimpleXMLElement('<?xml version="1.0"?><sections></sections>');
    }

    public function getXML(): SimpleXMLElement
    {
        return $this->xml;
    }

    /**
     * @param <string,array> $data
     */
    public function withData(array $data): self
    {
        $xml_sections = $this
            ->initUISection()
            ->addChild('sections');

        foreach ($data as $section => $components) {
            $xml_cases = $this
                ->addSection($xml_sections, $section, '')
                ->addChild('cases');

            foreach ($components as $component) {
                list($component_name, $entry) = $component;
                $this->addComponentCases($xml_cases, $section, $component_name, $entry);
            }
        }

        return $this;
    }

    public function addSection(
        SimpleXMLElement $xml_parent_node,
        string $name,
        string $description = '',
    ): SimpleXMLElement {
        $xml_section = $xml_parent_node->addChild('section');
        $xml_section->addChild('name', $name);
        $xml_section->addChild('description', $description);
        return $xml_section;
    }

    protected function addCase(
        SimpleXMLElement $xml_parent_node,
        string $id,
        string $title,
        string $preconditions,
        string $steps,
        string $expected,
    ): SimpleXMLElement {
        $xml_case = $xml_parent_node->addChild('case');
        $xml_case->addChild('id', $id);
        $xml_case->addChild('title', $title);
        $xml_case->addChild('template', 'Test Case');
        $xml_case->addChild('type', 'Other');
        $xml_case->addChild('priority', '4 - Must Test');
        $xml_case->addChild('references', 'Other');
        $xml_cust = $xml_case->addChild('custom');
        $xml_cust->addChild('preconds', "\n" . trim($preconditions) . "\n");
        $xml_cust->addChild('steps', "\n" . trim($steps) . "\n");
        $xml_cust->addChild('expected', "\n" . trim($expected) . "\n");
        return $xml_parent_node;
    }

    protected function initUISection(): SimpleXMLElement
    {
        $xml_section = $this->addSection(
            $this->xml,
            $this->getBlockContents('suite_title'),
            $this->getBlockContents('suite_description')
        );
        $xml_cases = $xml_section->addChild('cases');

        $this->addCase(
            $xml_cases,
            $this->maybeGetCaseId(self::BASE, self::OPEN),
            $this->getBlockContents('suite_case_open_title'),
            $this->getBlockContents('suite_case_open_precond'),
            $this->getBlockContents('suite_case_open_steps'),
            $this->getBlockContents('suite_case_open_expected')
        );
        $this->addCase(
            $xml_cases,
            $this->maybeGetCaseId(self::BASE, self::PREPARE),
            $this->getBlockContents('suite_case_validate_title'),
            $this->getBlockContents('suite_case_validate_precond'),
            $this->getBlockContents('suite_case_validate_steps'),
            $this->getBlockContents('suite_case_validate_expected')
        );

        return $xml_section;
    }

    protected function addComponentCases(
        SimpleXMLElement $xml_parent_node,
        string $section,
        string $component_name,
        ComponentEntry $entry,
    ): void {

        $preconditions = $this->getBlockContents('preconditions');
        $steps = $this->getTemplate();
        $steps->setCurrentBlock('steps_show');
        $steps->setVariable('SECTION', $section);
        $steps->setVariable('CLICKPATH', $this->getClickpath($entry));
        $steps->setVariable('TITLE', $entry->getTitle());
        $steps->parseCurrentBlock();
        $steps = $steps->get();

        $expected = $this->getTemplate();
        $expected_examples = $this->getExpectedExamples($entry->getExamples());
        $expected->setVariable('EXAMPLE_COUNTER', (string) count($entry->getExamples()));
        $expected->setVariable('EXPECTED', $expected_examples);
        $expected = $expected->get();

        $this->addCase(
            $xml_parent_node,
            $this->maybeGetCaseId($section . '/' . $component_name, self::SHOW),
            $section . ' - ' . $component_name . ': ' . self::SHOW,
            $preconditions,
            $steps,
            $expected
        );

        $steps = $this->getTemplate();
        $steps->setCurrentBlock('steps_validate');
        $steps->setVariable('SECTION', $section);
        $steps->setVariable('CLICKPATH', $this->getClickpath($entry));
        $steps->setVariable('TITLE', $entry->getTitle());
        $steps->parseCurrentBlock();
        $steps = $steps->get();

        $expected = $this->getBlockContents('expected_validate');

        $this->addCase(
            $xml_parent_node,
            $this->maybeGetCaseId($section . '/' . $component_name, self::VALIDATE),
            $section . ' - ' . $component_name . ': ' . self::VALIDATE,
            $preconditions,
            $steps,
            $expected
        );
    }

    protected function getTemplate(): ilTemplate
    {
        return clone $this->case_tpl;
    }

    protected function getBlockContents(string $block): string
    {
        $contents = $this->getTemplate();
        $contents->touchBlock($block);
        return "\n" . trim($contents->get()) . "\n";
    }

    protected function getClickpath(ComponentEntry $entry): string
    {
        $clickpath = array_slice(explode('/', $entry->getPath()), 4, -1);
        $clickpath[] = $entry->getTitle();
        return implode(' -> ', $clickpath);
    }


    protected function maybeGetCaseId(string $component_path, string $subkey): string
    {
        if (array_key_exists($component_path, self::$CASEIDS)
            && array_key_exists($subkey, self::$CASEIDS[$component_path])
        ) {
            return self::$CASEIDS[$component_path][$subkey];
        } else {
            print "\nno caseId for: $component_path ($subkey)";
        }
        return '';
    }

    protected function getExpectedExamples(array $examples): string
    {
        $expected_show = '';
        foreach(array_keys($examples) as $idx => $example) {
            $expected_show = $expected_show . "\n**" . $idx + 1 . '. ' . ucfirst(str_replace('_', ' ', $example)) . '**';
            $docs = $this->parser->parseYamlStringArrayFromFile($examples[$example]);
            if(array_key_exists('expected output', $docs)) {
                $expected_show .= "\n" . $docs['expected output'] . "\n";
            }
        }
        return $expected_show;
    }
}
