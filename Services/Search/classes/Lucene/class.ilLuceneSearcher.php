<?php

declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


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
