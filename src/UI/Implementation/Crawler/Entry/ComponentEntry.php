<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Crawler\Entry;

/**
 * Stores Information of UI Components parsed from YAML, examples and less files
 *
 * @author			  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class ComponentEntry extends AbstractEntryPart implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $id = "";

    /**
     * @var string
     */
    protected $title = "";

    /**
     * @var bool
     */
    protected $is_abstract = false;

    /**
     * @var array
     */
    protected $status_list_entry = array("Accepted","Proposed","To be revised");

    /**
     * @var array
     */
    protected $status_list_implementation = array("Implemented","Partly implemented","To be implemented");

    /**
     * @var string
     */
    protected $status_entry = "";

    /**
     * @var string
     */
    protected $status_implementation = "";

    /**
     * @var ComponentEntryDescription
     */
    protected $description = null;

    /**
     * @var string
     */
    protected $background = "";

    /**
     * @var string
     */
    protected $context = [];

    /**
     * @var string
     */
    protected $selector = "";

    /**
     * @var array
     */
    protected $feature_wiki_references = array();

    /**
     * @var ComponentEntryRules
     */
    protected $rules = null;

    /**
     * @var string
     */
    protected $parent = false;

    /**
     * @var string[]
     */
    protected $children = array();

    /**
     * @var array
     */
    protected $less_variables = array();

    /**
     * @var string
     */
    protected $path = "";


    /**
     * @var array
     */
    protected $examples = null;

    /**
     * @var string
     */
    protected $examples_path = "";

    /**
     * ComponentEntry constructor.
     *
     * @param $entry_data
     */
    public function __construct($entry_data)
    {
        parent::__construct();
        $this->assert()->isIndex('id', $entry_data);
        $this->setId($entry_data['id']);
        $this->assert()->isIndex('title', $entry_data);
        $this->setTitle($entry_data['title']);
        $this->assert()->isIndex('abstract', $entry_data);
        $this->setIsAbstract($entry_data['abstract']);
        $this->setStatusEntry("Proposed");
        $this->setStatusImplementation("Partly implemented");
        if (array_key_exists('description', $entry_data)) {
            $this->setDescription(new ComponentEntryDescription($entry_data['description']));
        }
        if (array_key_exists('rules', $entry_data)) {
            $this->setRules(new ComponentEntryRules($entry_data['rules']));
        }

        $this->assert()->isIndex('path', $entry_data);
        $this->setPath($entry_data['path']);

        if (array_key_exists('background', $entry_data)) {
            $this->setBackground($entry_data['background']);
        }
        if (array_key_exists('context', $entry_data)) {
            $this->setContext($entry_data['context']);
        }
        if (array_key_exists('featurewiki', $entry_data)) {
            $this->setFeatureWikiReferences($entry_data['featurewiki']);
        }
        if (array_key_exists('parent', $entry_data)) {
            $this->setParent($entry_data['parent']);
        }
        if (array_key_exists('children', $entry_data)) {
            $this->setChildren($entry_data['children']);
        }

        if (!$this->isAbstract()) {
            $this->readExamples();
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->assert()->isString($id, false);
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->assert()->isString($title, false);
        $this->title = $title;
    }

    /**
     * @return boolean
     */
    public function isAbstract()
    {
        return $this->is_abstract;
    }

    /**
     * @param boolean $is_abstract
     */
    public function setIsAbstract($is_abstract)
    {
        $this->is_abstract = $is_abstract;
    }

    /**
     * @return string
     */
    public function getStatusEntry()
    {
        return $this->status_entry;
    }

    /**
     * @param string $status_entry
     */
    public function setStatusEntry($status_entry)
    {
        $this->assert()->isString($status_entry);
        //$this->assert()->isIndex($status_entry,$this->status_list_entry);

        $this->status_entry = $status_entry;
    }

    /**
     * @return array
     */
    public function getStatusImplementation()
    {
        return $this->status_implementation;
    }

    /**
     * @param array $status_implementation
     */
    public function setStatusImplementation($status_implementation)
    {
        $this->assert()->isString($status_implementation);
        //$this->assert()->isIndex($status_implementation,$this->status_list_implementation);

        $this->status_implementation = $status_implementation;
    }

    /**
     * @return ComponentEntryDescription
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getDescriptionAsArray()
    {
        return $this->description->getDescription();
    }

    /**
     * @param ComponentEntryDescription $description
     * @throws \ILIAS\UI\Implementation\Crawler\Exception\CrawlerException
     */
    public function setDescription(ComponentEntryDescription $description)
    {
        $this->assert()->isTypeOf($description, ComponentEntryDescription::class);
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * @param string $background
     */
    public function setBackground($background)
    {
        $this->assert()->isString($background);
        $this->background = $background;
    }

    /**
     * @param array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext($context)
    {
        $this->assert()->isArray($context);
        $this->context = $context;
    }


    /**
     * @return array
     */
    public function getFeatureWikiReferences()
    {
        return $this->feature_wiki_references;
    }

    /**
     * @param array $feature_wiki_references
     */
    public function setFeatureWikiReferences($feature_wiki_references)
    {
        $this->assert()->isArray($feature_wiki_references);
        $this->feature_wiki_references = $feature_wiki_references;
    }

    /**
     * @return ComponentEntryRules
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return array
     */
    public function getRulesAsArray()
    {
        if ($this->rules) {
            return $this->rules->getRules();
        } else {
            return [];
        }
    }

    /**
     * @param ComponentEntryRules $rules
     */
    public function setRules($rules)
    {
        $this->assert()->isTypeOf($rules, ComponentEntryRules::class);
        $this->rules = $rules;
    }

    /**
     * @return string
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * @param string $selector
     */
    public function setSelector($selector)
    {
        $this->assert()->isString($selector);
        $this->selector = $selector;
    }

    /**
     * @param array $less_variables
     */
    public function setLessVariables($less_variables)
    {
        $this->assert()->isArray($less_variables);
        $this->less_variables = $less_variables;
    }

    /**
     * @return array
     */
    public function getLessVariables()
    {
        return $this->less_variables;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->assert()->isString($path);
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param string $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return \string[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param \string[] $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @param string $child
     */
    public function addChild($child)
    {
        $this->children[] = $child;
    }

    /**
     * @param \string[] $children
     */
    public function addChildren($children)
    {
        $this->setChildren(array_merge($this->children, $children));
    }

    /**
     * @return array
     */
    public function getExamples()
    {
        return $this->examples;
    }

    /**
     *
     */
    protected function readExamples()
    {
        $this->examples = array();
        if (is_dir($this->getExamplesPath())) {
            foreach (scandir($this->getExamplesPath()) as $file_name) {
                $example_path = $this->getExamplesPath() . "/" . $file_name;
                if (is_file($example_path) && pathinfo($example_path)["extension"] == "php") {
                    $example_name = str_replace(".php", "", $file_name);
                    $this->examples[$example_name] = $example_path;
                }
            }
        }
    }

    public function getExamplesPath()
    {
        if (!$this->examples_path) {
            $path_componants = str_replace("Component", "examples", $this->getPath())
                    . "/" . str_replace(" ", "", $this->getTitle());
            $path_array = self::array_iunique(explode("/", $path_componants));
            $this->examples_path = implode("/", $path_array);
        }
        return $this->examples_path;
    }


    /**
     * @param array $array
     * @return array
     */
    private static function array_iunique($array)
    {
        return array_intersect_key(
            $array,
            array_unique(array_map("StrToLower", $array))
        );
    }


    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'abstract' => $this->isAbstract(),
            'status_entry' => $this->getStatusEntry(),
            'status_implementation' => $this->getStatusImplementation(),
            'description' => $this->getDescription(),
            'background' => $this->getBackground(),
            'context' => $this->getContext(),
            'selector' => $this->getSelector(),
            'feature_wiki_references ' => $this->getFeatureWikiReferences(),
            'rules' => $this->getRules(),
            'parent' => $this->getParent(),
            'children' => $this->getChildren(),
            'less_variables' => $this->getLessVariables(),
            'path' => $this->getPath()
        );
    }
}
