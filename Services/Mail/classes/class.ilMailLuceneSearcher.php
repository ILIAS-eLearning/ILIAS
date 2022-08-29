<?php

declare(strict_types=1);

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

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailLuceneSearcher
{
    protected ilSetting $settings;

    public function __construct(protected ilLuceneQueryParser $query_parser, protected ilMailSearchResult $result)
    {
        global $DIC;
        $this->settings = $DIC->settings();
    }

    public function search(int $user_id, int $mail_folder_id): void
    {
        if ($this->query_parser->getQuery() === '') {
            throw new ilMailException('mail_search_query_missing');
        }

        try {
            $xml = ilRpcClientFactory::factory('RPCSearchHandler')->searchMail(
                CLIENT_ID . '_' . $this->settings->get('inst_id', '0'),
                $user_id,
                $this->query_parser->getQuery(),
                $mail_folder_id
            );
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('mail')->critical($e->getMessage());
            throw $e;
        }

        $parser = new ilMailSearchLuceneResultParser($this->result, $xml);
        $parser->parse();
    }
}
