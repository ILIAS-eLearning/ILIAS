<?php
include_once("./Services/UIComponent/Panel/classes/class.ilPanelGUI.php");
include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
include_once("libs/composer/vendor/geshi/geshi/src/geshi.php");


use ILIAS\UI\Implementation\Crawler\Entry as Entry;

/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilKSDocumentationEntryGUI
{
    /**
     * @var Entry\ComponentEntry
     */
    protected $entry = null;

    /**
     * @var Entry\ComponentEntries
     */
    protected $entries = null;

    /**
     * @var ilCtrl $ctrl
     */
    protected $ctrl;

    /**
     * @var ilSystemStyleDocumentationGUI
     */
    protected $parent;

    /**
     * @var ILIAS\UI\Factory
     */
    protected $f = null;

    /**
     * @var ILIAS\UI\Renderer
     */
    protected $r = null;

    /**
     * ilKSDocumentationEntryGUI constructor.
     * @param ilSystemStyleDocumentationGUI $parent
     * @param Entry\ComponentEntry $entry
     * @param Entry\ComponentEntries $entries
     */
    public function __construct(ilSystemStyleDocumentationGUI $parent, Entry\ComponentEntry $entry, Entry\ComponentEntries $entries)
    {
        global $DIC;

        $this->f = $DIC->ui()->factory();
        $this->r = $DIC->ui()->renderer();

        $this->setEntry($entry);
        $this->setEntries($entries);
        $this->setParent($parent);
        $this->ctrl = $DIC->ctrl();
    }

    /**
     * @return string
     */
    public function renderEntry()
    {
        $sub_panels = array();

        $feature_wiki_links = array();
        foreach ($this->entry->getFeatureWikiReferences()as $href) {
            $feature_wiki_links[] = $href;
        }

        $sub_panels[] = $this->f->panel()->sub(
            "Description",
            array(
                $this->f->listing()->descriptive(
                    array(
                        "Purpose" => $this->entry->getDescription()->getProperty("purpose"),
                        "Composition" => $this->entry->getDescription()->getProperty("composition"),
                        "Effect" => $this->entry->getDescription()->getProperty("effect"),

                    )
                ),
                $this->f->listing()->descriptive(
                    array(
                        "Background" => $this->entry->getBackground(),
                        "Context" => $this->f->listing()->ordered($this->entry->getContext()),
                        "Feature Wiki References" => $this->f->listing()->ordered($feature_wiki_links)
                    )
                )
            )
        );

        if (sizeof($this->entry->getDescription()->getProperty("rivals"))) {
            $sub_panels[] = $this->f->panel()->sub(
                "Rivals",
                $this->f->listing()->descriptive(
                    $this->entry->getDescription()->getProperty("rivals")
                )
            );
        }

        if ($this->entry->getRules()->hasRules()) {
            $rule_listings = array();
            foreach ($this->entry->getRulesAsArray() as $categoery => $category_rules) {
                $rule_listings[ucfirst($categoery)] = $this->f->listing()->ordered($category_rules);
            }


            $sub_panels[] = $this->f->panel()->sub(
                "Rules",
                $this->f->listing()->descriptive($rule_listings)
            );
        }


        if ($this->entry->getExamples()) {
            $nr = 1;
            foreach ($this->entry->getExamples() as $name => $path) {
                include_once($path);
                $title = "Example " . $nr . ": " . ucfirst(str_replace("_", " ", $name));
                $nr++;
                $example = "<div class='well'>" . $name() . "</div>"; //Executes function loaded in file indicated by 'path'
                $content_part_1 = $this->f->legacy($example);
                $code = str_replace("<?php\n", "", file_get_contents($path));
                $geshi = new GeSHi($code, "php");
                $content_part_2 = $this->f->legacy($geshi->parse_code());
                $content = array($content_part_1,$content_part_2);
                $sub_panels[] = $this->f->panel()->sub($title, $content);
            }
        }

        $sub_panels[] = $this->f->panel()->sub(
            "Relations",
            $this->f->listing()->descriptive(
                array(
                        "Parents" => $this->f->listing()->ordered(
                            $this->entries->getParentsOfEntryTitles($this->entry->getId())
                        ),
                        "Descendants" => $this->f->listing()->unordered(
                            $this->entries->getDescendantsOfEntryTitles($this->entry->getId())
                        )
                    )
            )
        );

        $report = $this->f->panel()->report($this->entry->getTitle(), $sub_panels);

        return $this->r->render($report);
    }


    /**
     * @return Entry\ComponentEntry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param Entry\ComponentEntry $entry
     */
    public function setEntry(Entry\ComponentEntry $entry)
    {
        $this->entry = $entry;
    }

    /**
     * @return Entry\ComponentEntries
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param Entry\ComponentEntries $entries
     */
    public function setEntries(Entry\ComponentEntries $entries)
    {
        $this->entries = $entries;
    }

    /**
     * @return ilSystemStyleDocumentationGUI
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param ilSystemStyleDocumentationGUI $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
}
