<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Forum/classes/class.ilForum.php");
require_once("./Modules/Forum/classes/class.ilForumProperties.php");

/**
* Class ilForumExplorer 
* class for explorer view of forum posts
* 
* @author Stefan Meyer <meyer@leifos.com>
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id$
* 
* @ingroup ModulesForum
*/

class ilForumExplorer
{
	/**
	* id of thread
	* @var int thread_pk
	* @access private
	*/
	private $thread_id;
	private $thread_subject;
	

	/**
	* id of root node
	* @var int root_id
	* @access private
	*/
	public $root_id;

	/**
	* forum object, used for owerwritten tree methods
	* @var object forum object
	* @access private
	*/
	private $forum;
	
	/**
	 * ilForumProperties object 
	 * @access private
	 */
	private $objProperties = null;
	
	private $objCurrentTopic = null;
	public $target = null;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	*/
	public function ilForumExplorer($tpl, $a_target, ilForumTopic $a_thread, $a_ref_id)
	{
		global $lng;

		$this->tpl = $tpl;
		$this->target = $a_target;

		$lng->loadLanguageModule('forum');
		
		$this->forum = new ilForum();
		$this->forum_obj = ilObjectFactory::getInstanceByRefId($a_ref_id);		
		
		$this->objProperties = ilForumProperties::getInstance($this->forum_obj->getId());
		
		$this->objCurrentTopic = $a_thread;
		$this->thread_id = $this->objCurrentTopic->getId();
		$this->root_id = $this->objCurrentTopic->getFirstPostNode()->getId();	
		
		$this->__readThreadSubject();

		// max length of user fullname which is shown in explorer view
		define(FULLNAME_MAXLENGTH, 16);
	}

	public function renderTree()
	{
		$this->setOutput(0);
	}

	/**
	* Creates output for explorer view in admin menue
	* recursive method
	* @access	public
	* @param	integer		parent_node_id where to start from (default=0, 'root')
	* @param	integer		depth level where to start (default=1)
	* @return	string
	*/
	public function setOutput($a_parent, $a_depth = 1)
	{
		global $lng, $ilUser, $ilCtrl;
		static $counter = 0;
		include_once 'Services/JSON/classes/class.ilJsonUtil.php';

		if (is_numeric($a_parent) && $objects = $this->objCurrentTopic->getPostChilds($a_parent, 'explorer'))
		{
			++$a_depth;
	
            foreach ($objects as $key => $object)
			{
				if (!$object['pos_status'] && !ilForum::_isModerator($_GET['ref_id'], $ilUser->getId()))
				{
					continue;
				}

				$href_target = $this->target."&pos_pk=".$object['child'].'#'.$object['child'];

				if($ilUser->getId() == ANONYMOUS_USER_ID ||
				 $this->forum_obj->isRead($ilUser->getId(), $object['pos_pk']))
				{
					$title = "<span style='white-space:wrap;' class='frmTitle' id='frm_node_".$object['pos_pk']."'><a class='small' href='".$href_target."'>".stripslashes($object['subject'])."</a></span>".
						 "<div style='white-space:nowrap; margin-bottom:5px;' class='small'>";
				}
				else
				{	//bold
					$mark_post_target = str_replace('viewThread', 'markPostRead', $href_target);
					$href_target = $mark_post_target;
					$title = "<span style='white-space:wrap;' class='frmTitleBold' id='frm_node_".$object['pos_pk']."'><a class='small' href='".$href_target."'>".stripslashes($object['subject'])."</a></span>".
						 "<div style='white-space:nowrap; margin-bottom:5px;' class='small'>";
				}

				if ($this->objProperties->isAnonymized())
				{
					if ($object['alias'] != '') $title .= stripslashes($object['alias']);
					else $title .= $lng->txt('forums_anonymous');
				}
				else
				{
					$title .= stripslashes($object['loginname']);
				}
				$title .= ", ".$this->forum->convertDate($object['date'])."</div>";

				$this->tpl->setVariable('OLD_THR_ID', $_SESSION['thread_control']['old']);
				$this->tpl->setVariable('NEW_THR_ID', $_SESSION['thread_control']['new']);

				$node = new stdClass();
				$node->html = $title;
				if($object['child'] == $this->root_id)
				{
					$this->tpl->setVariable('FRM_TREE_ROOT_NODE_VARIABLE', 'frmNode'.$object['child']);
					$this->tpl->setVariable('FRM_TREE_ROOT_NODE_LINK', ilJsonUtil::encode($node));
				}
				else
				{
					$this->tpl->setCurrentBlock('frm_nodes');
					$this->tpl->setVariable('FRM_NODES_VARNAME', 'frmNode'.$object['child']);
					$this->tpl->setVariable('FRM_NODES_PARENT_VARNAME', 'frmNode'.$object['parent']);
					$this->tpl->setVariable('FRM_NODES_LINK', ilJsonUtil::encode($node));
					$this->tpl->parseCurrentBlock();
				}
				++$counter;

				// Recursive
				$this->setOutput($object['child'], $a_depth);
			} //foreach
		} //if

		 $_SESSION['thread_control']['old']  = $_SESSION['thread_control']['new'];
	} //function

	private function __readThreadSubject()
	{
		$this->thread_subject = $this->objCurrentTopic->getSubject();
	}
} // END class.ilExplorer
?>
