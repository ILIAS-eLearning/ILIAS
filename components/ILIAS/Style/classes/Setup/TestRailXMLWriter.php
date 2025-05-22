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

    protected int $no_found_ids = 0;
    protected int $no_expexted_ids = 0;
    protected int $no_new_ids = 0;
    protected int $no_components = 2; //initUISection calls getCaseId twice, updating new_ids/found_ids; this is to make total numbers match up

    protected array $expected_by_caseids = [];

    public function __construct(
        protected \ilTemplate $case_tpl,
        protected ExamplesYamlParser $parser,
        protected bool $only_new_cases
    ) {
        $this->xml = new SimpleXMLElement('<?xml version="1.0"?><sections></sections>');
        foreach (self::$CASEIDS as $k => $v) {
            $this->no_expexted_ids += count($v);
            $this->expected_by_caseids = array_merge($this->expected_by_caseids, array_values($v));
        }
    }

    public function getXML(): SimpleXMLElement
    {
        print "\n found " . $this->no_components . ' components/cases';
        print "\n update for " . $this->no_found_ids . ' of ' . $this->no_expexted_ids . ' known IDs';
        print "\n new cases: " . $this->no_new_ids;
        print "\n known ids unaccounted for: " . implode(',', $this->expected_by_caseids);
        print "\n";
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
                $this->no_components += 2;
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

        list($build, $case_id) = $this->getCaseId(self::BASE, self::OPEN);
        if ($build) {
            $this->addCase(
                $xml_cases,
                $case_id,
                $this->getBlockContents('suite_case_open_title'),
                $this->getBlockContents('suite_case_open_precond'),
                $this->getBlockContents('suite_case_open_steps'),
                $this->getBlockContents('suite_case_open_expected')
            );
        }
        list($build, $case_id) = $this->getCaseId(self::BASE, self::PREPARE);
        if ($build) {
            $this->addCase(
                $xml_cases,
                $case_id,
                $this->getBlockContents('suite_case_validate_title'),
                $this->getBlockContents('suite_case_validate_precond'),
                $this->getBlockContents('suite_case_validate_steps'),
                $this->getBlockContents('suite_case_validate_expected')
            );
        }

        return $xml_section;
    }

    protected function addComponentCases(
        SimpleXMLElement $xml_parent_node,
        string $section,
        string $component_name,
        ComponentEntry $entry,
    ): void {

        $preconditions = $this->getBlockContents('preconditions');

        list($build, $case_id) = $this->getCaseId($section . '/' . $component_name, self::SHOW);
        if ($build) {
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
                $case_id,
                $section . ' - ' . $component_name . ': ' . self::SHOW,
                $preconditions,
                $steps,
                $expected
            );
        }

        list($build, $case_id) = $this->getCaseId($section . '/' . $component_name, self::VALIDATE);
        if ($build) {
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
                $case_id,
                $section . ' - ' . $component_name . ': ' . self::VALIDATE,
                $preconditions,
                $steps,
                $expected
            );
        }
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

    protected function getCaseId(string $component_path, string $subkey): array
    {
        $case_id = '';
        if (array_key_exists($component_path, self::$CASEIDS)
            && array_key_exists($subkey, self::$CASEIDS[$component_path])
        ) {
            $case_id = self::$CASEIDS[$component_path][$subkey];
            $this->no_found_ids += 1;
            print "\n caseId for: $component_path ($subkey)";
            unset(
                $this->expected_by_caseids[
                    array_search($case_id, $this->expected_by_caseids)
                ]
            );
        } else {
            $this->no_new_ids += 1;
            print "\n no caseId for: $component_path ($subkey)";
        }

        $build = ($case_id === '' && $this->only_new_cases)
            || ($case_id !== '' && !$this->only_new_cases);

        return [$build, $case_id];
    }

    protected function getExpectedExamples(array $examples): string
    {
        $expected_show = '';
        foreach (array_keys($examples) as $idx => $example) {
            $expected_show = $expected_show . "\n**" . $idx + 1 . '. ' . ucfirst(str_replace('_', ' ', $example)) . '**';
            $docs = $this->parser->parseYamlStringArrayFromFile($examples[$example]);
            if (array_key_exists('expected output', $docs)) {
                $expected_show .= "\n" . $docs['expected output'] . "\n";
            }
        }
        return $expected_show;
    }
}
