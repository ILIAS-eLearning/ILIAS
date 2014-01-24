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
	 *
	 */
	public function __construct(ilLuceneQueryParser $query_parser, ilMailSearchResult $result)
	{
		$this->query_parser = $query_parser;
		$this->result       = $result;
	}

	/**
	 * @param int $user_id
	 * @param int $mail_folder_id
	 * @throws Exception
	 */
	public function search($user_id, $mail_folder_id)
	{
		/**
		 * @var $ilSetting ilSetting
		 * @var $ilLog     ilLog
		 * @var $ilBench   ilBenchmark
		 */
		global $ilBench, $ilSetting, $ilLog;

		if(!$this->query_parser->getQuery())
		{
			throw new ilException('mail_search_query_missing');
		}

		$ilBench->start('Mail', 'LuceneSearch');
		try
		{
			include_once 'Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
			$xml = ilRpcClientFactory::factory('RPCSearchHandler')->searchMail(
				CLIENT_ID . '_' . $ilSetting->get('inst_id', 0),
				(int)$user_id,
				(string)$this->query_parser->getQuery(),
				(int)$mail_folder_id
			);
		}
		catch(XML_RPC2_FaultException $e)
		{
			$ilBench->stop('Mail', 'LuceneSearch');
			$ilLog->write(__METHOD__ . ': ' . $e->getMessage());
			throw $e;
		}
		catch(Exception $e)
		{

			$ilBench->stop('Mail', 'LuceneSearch');
			$ilLog->write(__METHOD__ . ': ' . $e->getMessage());
			throw $e;
		}
		$ilBench->stop('Mail', 'LuceneSearch');

		include_once 'Services/Mail/classes/class.ilMailSearchLuceneResultParser.php';
		$parser = new ilMailSearchLuceneResultParser($this->result, $xml);
		$parser->parse();
	}
}
