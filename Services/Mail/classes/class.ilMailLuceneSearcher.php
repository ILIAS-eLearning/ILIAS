<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesMail
 */
class ilMailLuceneSearcher
{
    /**
     * @var ilLuceneQueryParser
     */
    protected $query_parser;

    /**
     * @var ilMailSearchResult
     */
    protected $result;

    /**
     * @var \ilSetting
     */
    protected $settings;

    /**
     *
     */
    public function __construct(ilLuceneQueryParser $query_parser, ilMailSearchResult $result)
    {
        global $DIC;

        $this->settings = $DIC->settings();

        $this->query_parser = $query_parser;
        $this->result = $result;
    }

    /**
     * @param int $user_id
     * @param int $mail_folder_id
     * @throws Exception
     */
    public function search($user_id, $mail_folder_id)
    {
        if (!$this->query_parser->getQuery()) {
            throw new ilException('mail_search_query_missing');
        }

        try {
            include_once 'Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
            $xml = ilRpcClientFactory::factory('RPCSearchHandler')->searchMail(
                CLIENT_ID . '_' . $this->settings->get('inst_id', 0),
                (int) $user_id,
                (string) $this->query_parser->getQuery(),
                (int) $mail_folder_id
            );
        } catch (Exception $e) {
            require_once './Services/Logging/classes/public/class.ilLoggerFactory.php';
            ilLoggerFactory::getLogger('mail')->critical($e->getMessage());
            throw $e;
        }

        include_once 'Services/Mail/classes/class.ilMailSearchLuceneResultParser.php';
        $parser = new ilMailSearchLuceneResultParser($this->result, $xml);
        $parser->parse();
    }
}
