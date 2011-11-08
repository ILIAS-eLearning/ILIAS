<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
 * Class ilBlogPosting
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBlog
 */
class ilBlogPosting extends ilPageObject
{	
	protected $title; // [string]
	protected $created; // [ilDateTime]
	protected $blog_wsp_id; // [int]

	/**
	 * Constructor
	 *
	 * @param int $a_id blog posting id
	 * @param int $a_old_nr versioning
	 * @param bool $a_halt halt on errors
	 * @return ilBlogPosting
	 */
	function __construct($a_id = 0, $a_old_nr = 0, $a_halt = true)
	{
		parent::__construct("blp", $a_id, $a_old_nr, $a_halt);
	}

	/**
	 * Set title
	 *
	 * @param string $a_title
	 */
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set blog object id
	 *
	 * @param int $a_id
	 */
	function setBlogId($a_id)
	{
		$this->setParentId($a_id);
	}

	/**
	 * Get blog object id
	 *
	 * @return int
	 */
	function getBlogId()
	{
		return $this->getParentId();
	}

	/**
	 * Set creation date
	 *
	 * @param ilDateTime $a_date
	 */
	function setCreated(ilDateTime $a_date)
	{
		$this->created = $a_date;
	}

	/**
	 * Get creation date
	 *
	 * @return ilDateTime
	 */
	function getCreated()
	{
		return $this->created;
	}

	/**
	 * Create new blog posting
	 */
	function create()
	{
		global $ilDB;

		$id = $ilDB->nextId("il_blog_posting");
		$this->setId($id);

		// we are using a separate creation date to enable sorting without JOINs
		
		$query = "INSERT INTO il_blog_posting (id, title, blog_id, created) VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote($this->getTitle(), "text").",".
			$ilDB->quote($this->getBlogId(), "integer").",".
			$ilDB->quote(ilUtil::now(), "timestamp").")";
		$ilDB->manipulate($query);

		parent::create();
		// $this->saveInternalLinks($this->getXMLContent());
	}

	/**
	 * Update blog posting
	 *
	 * @param bool $a_validate
	 * @param bool $a_no_history
	 * @param bool $a_notify
	 * @return boolean
	 */
	function update($a_validate = true, $a_no_history = false, $a_notify = true)
	{
		global $ilDB;

		// blog_id and created cannot be changed
		
		$query = "UPDATE il_blog_posting SET".
			" title = ".$ilDB->quote($this->getTitle(), "text").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
		
		parent::update($a_validate, $a_no_history);
		
		if($a_notify && $this->getActive())
		{
			include_once "Modules/Blog/classes/class.ilObjBlog.php";
			ilObjBlog::sendNotification("update", $this->blog_wsp_id, $this->getId());
		}

		return true;
	}
	
	/**
	 * Read blog posting
	 */
	function read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_blog_posting".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setTitle($rec["title"]);
		$this->setBlogId($rec["blog_id"]);
		$this->setCreated(new ilDateTime($rec["created"], IL_CAL_DATETIME));
	
		parent::read();
	}


	/**
	 * Delete blog posting and all related data
	 *
	 * @return bool
	 */
	function delete()
	{
		global $ilDB;

		$query = "DELETE FROM il_blog_posting".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
		
		parent::delete();

		return true;
	}

	/**
	 * Delete all postings for blog
	 *
	 * @param int $a_blog_id
	 */
	static function deleteAllBlogPostings($a_blog_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_blog_posting".
			" WHERE blog_id = ".$ilDB->quote($a_blog_id, "integer");
		$set = $ilDB->query($query);
		while($rec = $ilDB->fetchAssoc($set))
		{
			$post = new ilBlogPosting($rec["id"]);
			$post->delete();
		}
	}
	
	/**
	 * Lookup blog id
	 *
	 * @param int $a_posting_id
	 * @return int
	 */
	static function lookupBlogId($a_posting_id)
	{
		global $ilDB;

		$query = "SELECT blog_id FROM il_blog_posting".
			" WHERE id = ".$ilDB->quote($a_posting_id, "integer");
		$set = $ilDB->query($query);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["blog_id"];
		}
		return false;
	}

	/**
	 * Get all postings of blog
	 *
	 * @param int $a_blog_id
	 * @param int $a_limit
	 * @param int $a_offset
	 * @return array
	 */
	static function getAllPostings($a_blog_id, $a_limit = 100, $a_offset = 0)
	{
		global $ilDB;
		
		$pages = parent::getAllPages("blp", $a_blog_id);

		if($limit)
		{
			$ilDB->setLimit($a_limit, $a_offset);
		}
		
		$query = "SELECT * FROM il_blog_posting".
			" WHERE blog_id = ".$ilDB->quote($a_blog_id, "integer").
			" ORDER BY created DESC";
		$set = $ilDB->query($query);
		$post = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			if (isset($pages[$rec["id"]]))
			{
				$post[$rec["id"]] = $pages[$rec["id"]];
				$post[$rec["id"]]["title"] = $rec["title"];
				$post[$rec["id"]]["created"] = new ilDateTime($rec["created"], IL_CAL_DATETIME);
			}
		}

		return $post;
	}

	/**
	 * Checks whether a posting exists
	 *
	 * @param int $a_blog_id
	 * @param int $a_posting_id
	 * @return bool
	 */
	static function exists($a_blog_id, $a_posting_id)
	{
		global $ilDB;

		$query = "SELECT id FROM il_blog_posting".
			" WHERE blog_id = ".$ilDB->quote($a_blog_id, "integer").
			" AND id = ".$ilDB->quote($a_posting_id, "integer");
		$set = $ilDB->query($query);
		if($rec = $ilDB->fetchAssoc($set))
		{
			return true;
		}
		return false;
	}

	/**
	 * Get newest posting for blog
	 *
	 * @param int $a_blog_id
	 * @return int
	 */
	static function getLastPost($a_blog_id)
	{
		$data = self::getAllPostings($a_blog_id, 1);
		if($data)
		{
			return array_pop(array_keys($data));
		}
	}
	
	/**
	 * Set blog wsp id (needed for notification)
	 * 
	 * @param int $a_id
	 */
	public function setBlogWspId($a_id)
	{
		$this->blog_wsp_id = (int)$a_id;
	}
}

?>