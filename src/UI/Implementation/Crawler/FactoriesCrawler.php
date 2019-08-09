<?php
/***
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$
 *
 */
namespace ILIAS\UI\Implementation\Crawler;

use Symfony\Component\Yaml;
use ILIAS\UI\Implementation\Crawler\Entry as Entry;

class FactoriesCrawler implements Crawler
{
    /**
     * @var EntriesYamlParser
     */
    protected $parser = null;

    /**
     * @var Exception\Factory
     */
    protected $ef = null;

    /**
     * FactoryCrawler constructor.
     */
    public function __construct()
    {
        $this->parser = new EntriesYamlParser();
        $this->ef = new Exception\Factory();
    }

    /**
     * @inheritdoc
     */
    public function crawlFactory($factoryPath, Entry\ComponentEntry $parent = null, $depth=0)
    {
        $depth++;
        if ($depth > 30) {
            throw $this->ef->exception(Exception\CrawlerException::CRAWL_MAX_NESTING_REACHED, " Current Path: " . $factoryPath . " Parent: " . $parent->getId());
        }
        $entries = $this->parser->parseEntriesFromFile($factoryPath);

        $children = new Entry\ComponentEntries();

        foreach ($entries as $entry) {
            if ($entry->isAbstract()) {
                $children->addEntries($this->crawlFactory($entry->getPath() . ".php", $entry, $depth));
            }
            if ($parent) {
                $entry->setParent($parent->getId());
                $parent->addChild($entry->getId());
            }
        }
        $entries->addEntries($children);
        return $entries;
    }
}
