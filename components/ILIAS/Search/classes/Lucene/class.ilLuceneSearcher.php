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
/**
* Reads and parses lucene search results
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup
*/
class ilLuceneSearcher
{
    public const TYPE_STANDARD = 1;
    public const TYPE_USER = 2;

    private static ?ilLuceneSearcher $instance = null;

    private ilLuceneQueryParser $query_parser;
    private ilLuceneSearchResult $result;
    private ?ilLuceneHighlighterResultParser $highlighter = null;
    private int $page_number = 1;
    private int $type = self::TYPE_STANDARD;

    protected ilSetting $setting;

    private function __construct(ilLuceneQueryParser $qp)
    {
        global $DIC;

        $this->setting = $DIC->settings();
        $this->result = new ilLuceneSearchResult();
        $this->result->setCallback([$this,'nextResultPage']);
        $this->query_parser = $qp;
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(ilLuceneQueryParser $qp): self
    {
        if (self::$instance instanceof ilLuceneSearcher) {
            return self::$instance;
        }
        return self::$instance = new ilLuceneSearcher($qp);
    }

    /**
     * Set search type
     */
    public function setType(int $a_type): void
    {
        $this->type = $a_type;
    }

    /**
     * Get type
     */
    public function getType(): int
    {
        return $this->type;
    }


    /**
     * Search
     */
    public function search(): void
    {
        $this->performSearch();
    }

    /**
     * @param int[] $a_obj_ids
     * @return ilLuceneHighlighterResultParser|null
     */
    public function highlight(array $a_obj_ids): ?ilLuceneHighlighterResultParser
    {
        if (!$this->query_parser->getQuery()) {
            return null;
        }

        // Search in combined index
        try {
            $res = ilRpcClientFactory::factory('RPCSearchHandler')->highlight(
                CLIENT_ID . '_' . $this->setting->get('inst_id', '0'),
                $a_obj_ids,
                $this->query_parser->getQuery()
            );
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('src')->error('Highlighting failed with message: ' . $e->getMessage());
            return new ilLuceneHighlighterResultParser();
        }

        $this->highlighter = new ilLuceneHighlighterResultParser();
        $this->highlighter->setResultString($res);
        $this->highlighter->parse();

        return $this->highlighter;
    }

    /**
     * get next result page
     */
    public function nextResultPage(): void
    {
        $this->page_number++;
        $this->performSearch();
    }

    /**
     * get highlighter
     */
    public function getHighlighter(): ?ilLuceneHighlighterResultParser
    {
        return $this->highlighter;
    }

    /**
     * Get result
     */
    public function getResult(): ilLuceneSearchResult
    {
        return $this->result;
    }

    /**
     * get current page number
     */
    public function getPageNumber(): int
    {
        return $this->page_number;
    }

    /**
     * search lucene
     */
    protected function performSearch(): void
    {
        if (!$this->query_parser->getQuery()) {
            return;
        }
        try {
            switch ($this->getType()) {
                case self::TYPE_USER:
                    /** @noinspection PhpUndefinedMethodInspection */
                    $res = ilRpcClientFactory::factory('RPCSearchHandler')->searchUsers(
                        CLIENT_ID . '_' . $this->setting->get('inst_id', '0'),
                        $this->query_parser->getQuery()
                    );
                    break;

                case self::TYPE_STANDARD:
                default:
                    $res = ilRpcClientFactory::factory('RPCSearchHandler')->search(
                        CLIENT_ID . '_' . $this->setting->get('inst_id', '0'),
                        $this->query_parser->getQuery(),
                        $this->getPageNumber()
                    );
                    break;
            }
            ilLoggerFactory::getLogger('src')->debug('Searching for: ' . $this->query_parser->getQuery());
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('src')->error('Searching failed with message: ' . $e->getMessage());
            return;
        }

        ilLoggerFactory::getLogger('src')->dump($res);

        // Parse results
        $parser = new ilLuceneSearchResultParser($res);
        $parser->parse($this->result);
    }
}
