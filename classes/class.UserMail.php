<?php
/**
 * user mail
 * 
 * this class handles user mails 
 *  
 * @author Peter Gabriel <pgabriel@databay.de>
 * 
 * @package application
 * @version $Id$
 */
class UserMail extends PEAR
{
    /**
     * User Id
     *
	 * @var integer
     */
    var $id;					

    /**
     * database handler
     *
     * @var object DB
     */	
    var $db;

    /**
     * Constructor
     *
     * setup an usermail object
     *
     * @param object database handler
     * @param string UserID
     */
    function UserMail(&$dbhandle, $aUsrId = "")
    {

		// Initiate variables
		$this->db =& $dbhandle;

		if (!empty($aUsrId) and ($aUsrId > 0))
		{
		    $this->Id = $AUsrId;
		}
    }


    /**
	* delete a mail
    * @param int
	* @return boolean
	* @access public
    */
	 function delete ($aMsgId)
	 {
		 if (empty($aMsgId))
		 {
		 	return true;
		 }
		 else
		 {
		 	//delete mail here
			return true;
		 }
	 }
	 
	
	 
	 /**
	  * get mail
	  *
	  * @return array mails
	  * @access public
	  */
	 function getMail()
	 {
		 global $lng;

		 //initialize array
		 $mails = array();
		 //query
		 $sql = "SELECT * FROM mails 
                 WHERE user_fk='".$this->id."'
                 AND read=0";
				 
		 $mails["msg"] = array();
		 $mails["msg"][] = array(
			 "id" => 1,
			 "from" => "Hermann Mustermann",
			 "email" => "herm@nn.de",
			 "subject" => "Hello",
			 "body" => "This is a test mail",
			 "datetime" => $lng->fmtDate(date("Y-m-d")),
			 "new" => true
			 );
		 $mails["msg"][] = array(
			 "id" => 2,
			 "from" => "Hermann Mustermann",
			 "email" => "herm@nn.de",
			 "subject" => "Hello once again",
			 "body" => "This is a test mail",
			 "datetime" => $lng->fmtDate(date("Y-m-d")),
			 "new" => false
			 );

		 $mails["unread"] = 1;
		 return $mails;
	 }
	 
} // END class user
?>
