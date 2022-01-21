<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Crawler;

use ILIAS\UI\Implementation\Crawler\Entry as Entry;

/***
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class FactoriesCrawler implements Crawler
{
    protected ?EntriesYamlParser $parser = null;
    protected ?Exception\Factory $ef = null;

    public function __construct()
    {
        $this->parser = new EntriesYamlParser();
        $this->ef = new Exception\Factory();
    }

    /**
     * @inheritdoc
     */
    public function crawlFactory(
        string $factoryPath,
        Entry\ComponentEntry $parent = null,
        int $depth = 0
    ) : Entry\ComponentEntries {
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
