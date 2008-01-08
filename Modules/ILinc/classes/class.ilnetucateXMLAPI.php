<?php

require_once "./Modules/ILinc/classes/class.ilnetucateResponse.php";
require_once "./classes/class.ilXmlWriter.php";

/**
* API to communicate with a the CMSAPI of centra
* (c) Sascha Hofmann, 2004
*  
* @author	Sascha Hofmann <saschahofmann@gmx.de>
*
* @version	$Id$
* 
*/
class ilnetucateXMLAPI extends ilXmlWriter
{
	/**
	* Constructor
	* @access	public
	*/
	function ilnetucateXMLAPI()
	{
		global $ilias;
		
		define('ILINC_MEMBER_NOTSET','ilinc_notset');
		define('ILINC_MEMBER_DOCENT','ilinc_docent');
		define('ILINC_MEMBER_STUDENT','ilinc_student');

		parent::ilXmlWriter();

		$this->ilias =& $ilias;

		$this->reg_login = $this->ilias->getSetting("ilinc_registrar_login");
		$this->reg_passwd = $this->ilias->getSetting("ilinc_registrar_passwd");
		$this->customer_id = $this->ilias->getSetting("ilinc_customer_id");
		$this->server_scheme = $this->ilias->getSetting("ilinc_protocol");
		$this->server_addr	= $this->ilias->getSetting("ilinc_server");
		$this->server_path = $this->ilias->getSetting("ilinc_path");
		$this->server_port	= $this->ilias->getSetting("ilinc_port");
		$this->server_timeout	= $this->ilias->getSetting("ilinc_timeout");
		$this->user_max_strlen = 32; // Max string length of full username (title + firstname + lastname)

	}
	
	function xmlFormatData($a_data)
	{
		return $a_data;
	}
	
	function setServerAddr($a_server_addr)
	{
		$this->server_addr = $a_server_addr;
	}
	
	function getServerAddr()
	{
		return $this->server_addr;
	}
	
	function getServerPort()
	{
		return $this->server_port;
	}
	
	function getServerTimeOut()
	{
		return $this->server_timeout;
	}
	
	function getServerPath()
	{
		return $this->server_path;
	}
	
	function getServerScheme()
	{
		return $this->server_scheme;
	}

	function getCustomerID()
	{
		return $this->customer_id;
	}

	function setRequest($a_data)
	{
		$this->request = $a_data;
	}
	
	// send request to iLinc server
	// returns true if request was successfully sent (a response returned)
	function sendRequest($a_request = '')
	{
		global $ilErr,$lng;
		
		// get request xml data
		$this->request = $this->xmlDumpMem();
		
		// compose request header
		$header = "Host: ".$this->getServerAddr()."\r\n";
		$header .= "User-Agent: ILIAS open source\r\n";
		$header .= "Content-Type: text/xml\r\n";
		$header .= "Content-Length: ".strlen($this->request)."\r\n";
		$header .= "Connection: close\r\n\r\n";

		// determine protocol
		if ($this->getServerScheme() == "https")
		{
			$scheme = "ssl";
		}
		else
		{
			$scheme = "http";
		}

		// open socket connection to server
		$sock = @fsockopen($scheme."://".$this->getServerAddr(), $this->getServerPort(), $errno, $errstr, $this->getServerTimeOut());

		if (!$sock)
		{
			$ilErr->raiseError($lng->txt('ilinc_connection_error'),$ilErr->MESSAGE);
		}

		// send request
		fputs($sock, "POST ".$this->getServerPath()." HTTP/1.0\r\n");
		fputs($sock,$header.$this->request);
		
		$response = "";

		// read response data and surpress error from buggy IIS software (missing 'close_notify' cause fatal error)
		while (!feof($sock))
		{
			$response .= @fgets($sock, 128);
		}
		
		fclose($sock);
		
		// return netucate response object
		$response_obj =  new ilnetucateResponse($response);
		
		return $response_obj;
	}
	
	/**
	 * add user account to iLinc
	 * 
	 * @param	array	login data
	 * @param	string	user fullname
	 * @param	string	permission level (optional)
	 *  
	 */
	function addUserOLD(&$a_login_data,&$a_user_obj,$a_authority = "leader")
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Add";
		$attr['object'] = "User";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$attr = array();
		$attr['loginname'] = $a_login_data["login"]; // required; max 64 chars
		$attr['fullname'] = $a_user_obj->getFullname($this->user_max_strlen); // required; max 32 chars
		$attr['password'] = $a_login_data["passwd"]; // required; max 127 chars
		$attr['authority'] = $a_authority; // optional; participant or leader, Default: leader
		$attr['email'] = $a_user_obj->getEmail(); // optional; max 64 chars
		$attr['homepage'] = ""; // no standard in ILIAS; optional; max 255 chars
		$attr['contactinfo'] = $a_user_obj->getInstitution(); // optional; max 64 chars
		$attr['comments'] = $a_user_obj->getComment(); // no standard in ILIAS; optional; max 64 chars
		$attr['phonenumber'] = $a_user_obj->getPhoneOffice(); // optional; max 32 chars
		$attr['akuservalue1'] = ""; // optional; max 64 chars
		$attr['akuservalue2'] = ""; // optional; max 64 chars

		$this->xmlStartTag('netucate.User',$attr);
		$this->xmlEndTag('netucate.User');
		
		$this->xmlEndTag('netucate.API.Request');
	}
	
	/**
	 * add user account to iLinc
	 * 
	 * @param	array	login data
	 * @param	string	user fullname
	 * @param	string	permission level (optional)
	 *  
	 */
	function addUser(&$a_ilinc_user_obj,$a_authority = "leader")
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Add";
		$attr['object'] = "User";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$attr = array();
		$attr['loginname'] = $a_ilinc_user_obj->login; // required; max 64 chars
		$attr['fullname'] = $a_ilinc_user_obj->user->getFullname($this->user_max_strlen); // required; max 32 chars
		$attr['password'] = $a_ilinc_user_obj->passwd; // required; max 127 chars
		$attr['authority'] = $a_authority; // optional; participant or leader, Default: leader
		$attr['email'] = $a_ilinc_user_obj->user->getEmail(); // optional; max 64 chars
		$attr['homepage'] = ""; // no standard in ILIAS; optional; max 255 chars
		$attr['contactinfo'] = $a_ilinc_user_obj->user->getInstitution(); // optional; max 64 chars
		$attr['comments'] = $a_ilinc_user_obj->user->getComment(); // no standard in ILIAS; optional; max 64 chars
		$attr['phonenumber'] = $a_ilinc_user_obj->user->getPhoneOffice(); // optional; max 32 chars
		$attr['akuservalue1'] = $a_ilinc_user_obj->akuservalue1; // optional; max 64 chars
		$attr['akuservalue2'] = $a_ilinc_user_obj->akuservalue2; // optional; max 64 chars

		$this->xmlStartTag('netucate.User',$attr);
		$this->xmlEndTag('netucate.User');
		
		$this->xmlEndTag('netucate.API.Request');
	}
	
	/**
	 * change user account in iLinc
	 * 
	 * @param	array	login data
	 * @param	string	user fullname
	 * @param	string	permission level (optional)
	 *  
	 */
	function editUser(&$a_ilinc_user_obj)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Edit";
		$attr['object'] = "User";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$attr = array();
		$attr['userid'] = $a_ilinc_user_obj->id; // required;
		//$attr['loginname'] = $a_ilinc_user_obj->login; // required; max 64 chars
		$attr['fullname'] = $a_ilinc_user_obj->user->getFullname($this->user_max_strlen); // required; max 32 chars
		//$attr['password'] = $a_ilinc_user_obj->passwd; // required; max 127 chars
		//$attr['authority'] = $a_authority; // optional; participant or leader, Default: leader
		$attr['email'] = $a_ilinc_user_obj->user->getEmail(); // optional; max 64 chars
		//$attr['homepage'] = ""; // no standard in ILIAS; optional; max 255 chars
		$attr['contactinfo'] = $a_ilinc_user_obj->user->getInstitution(); // optional; max 64 chars
		$attr['comments'] = $a_ilinc_user_obj->user->getComment(); // no standard in ILIAS; optional; max 64 chars
		$attr['phonenumber'] = $a_ilinc_user_obj->user->getPhoneOffice(); // optional; max 32 chars
		$attr['akuservalue1'] = $a_ilinc_user_obj->akuservalue1; // optional; max 64 chars
		$attr['akuservalue2'] = $a_ilinc_user_obj->akuservalue2; // optional; max 64 chars

		$this->xmlStartTag('netucate.User',$attr);
		$this->xmlEndTag('netucate.User');
		
		$this->xmlEndTag('netucate.API.Request');
	}

	function registerUser($a_ilinc_course_id,$a_ilinc_user_arr)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Register";
		$attr['object'] = "User";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$attr = array();
		$attr['courseid'] = $a_ilinc_course_id;
		$this->xmlStartTag('netucate.Course',$attr);

		$this->xmlStartTag('netucate.User.List');

		foreach ($a_ilinc_user_arr as $user)
		{
			$attr = array();
			$attr['userid'] = $user['id'];
			$attr['instructorflag'] = $user['instructor'];
			$this->xmlStartTag('netucate.User',$attr);
			$this->xmlEndTag('netucate.User');
		}
		
		$this->xmlEndTag('netucate.User.List');
		
		$this->xmlEndTag('netucate.Course');
		
		$this->xmlEndTag('netucate.API.Request');
		//var_dump($a_ilinc_user_arr,$this->xmlDumpMem());exit;
	}
	
	function findRegisteredUsersByRole($a_ilinc_course_id,$a_instructorflag = false)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Find";
		$attr['object'] = "RegisteredUsersByRole";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$attr = array();
		$attr['courseid'] = $a_ilinc_course_id;
		$attr['instructorflag'] = ($a_instructorflag) ? "True" : "False";
		$this->xmlStartTag('netucate.Course',$attr);

		$this->xmlEndTag('netucate.Course');
		
		$this->xmlEndTag('netucate.API.Request');
	}

	function unregisterUser($a_ilinc_course_id, $a_ilinc_user_ids)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "UnRegister";
		$attr['object'] = "User";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$attr = array();
		$attr['courseid'] = $a_ilinc_course_id;
		$this->xmlStartTag('netucate.Course',$attr);

		$this->xmlStartTag('netucate.User.List');

		foreach ($a_ilinc_user_ids as $user_id)
		{
			$attr = array();
			$attr['userid'] = $user_id;
			$this->xmlStartTag('netucate.User',$attr);
			$this->xmlEndTag('netucate.User');
		}
		
		$this->xmlEndTag('netucate.User.List');
		
		$this->xmlEndTag('netucate.Course');
		
		$this->xmlEndTag('netucate.API.Request');
	}

	// not used yet
	function findUser(&$a_id,&$a_login,&$a_fullname)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Find";
		$attr['object'] = "User";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$attr = array();
		$attr['userid'] = $a_id;
		$attr['loginname'] = $a_login;
		$attr['fullname'] = $a_fullname;
		$attr['lotnumber'] = "0"; // optional; The set of matching records to return. If 0, all matching records will be returned. If 1, matches 1-25 will be returned. If 2, matches 26-50 will be returned, etc.; Default: 0
		$this->xmlStartTag('netucate.User',$attr);
		$this->xmlEndTag('netucate.User');
		
		$this->xmlEndTag('netucate.API.Request');
	}

	// not used yet
	function removeUser(&$a_user_obj)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Remove";
		$attr['object'] = "User";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$this->xmlStartTag('netucate.User.List');

		$attr = array();
		$attr['userid'] = "2191";
		$attr['instructorflag'] = "True";
		$this->xmlStartTag('netucate.User',$attr);
		$this->xmlEndTag('netucate.User');
		
		$attr = array();
		$attr['userid'] = "2192";
		$attr['loginname'] = "ffuss";
		$this->xmlStartTag('netucate.User',$attr); // userid or loginname per User are required.
		$this->xmlEndTag('netucate.User');
		
		$this->xmlEndTag('netucate.User.List');
		
		$this->xmlEndTag('netucate.API.Request');
	}
	
	function addClass($a_course_id,$a_data)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Add";
		$attr['object'] = "Class";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$attr = array();
		$attr['courseid'] = $a_course_id;
		$attr['name'] = $a_data['title'];
		$attr['instructoruserid'] = $a_data['instructoruserid'];
		$attr['description'] = $a_data['desc'];
		$attr['alwaysopen'] = $a_data['alwaysopen'];
		//$attr['password'] = $a_data['password'];
		//$attr['bandwidth'] = $a_data['bandwidth'];
		//$attr['appsharebandwidth'] = $a_data['appsharebandwidth'];
		//$attr['message'] = $a_data['message'];
		//$attr['floorpolicy'] = $a_data['floorpolicy'];
		//$attr['conferencetypeid'] = $a_data['conferencetypeid'];
		//$attr['videobandwidth'] = $a_data['videobandwidth'];
		//$attr['videoframerate'] = $a_data['videoframerate'];
		//$attr['enablepush'] = $a_data['enablepush'];
		//$attr['issecure'] = $a_data['issecure'];
		
		// only update akclassvalues if akclassvalues are enabled
		if (array_key_exists('akclassvalue1',$a_data))
		{
			$attr['akclassvalue1'] = $a_data['akclassvalue1'];
		}
		if (array_key_exists('akclassvalue2',$a_data))
		{
			$attr['akclassvalue2'] = $a_data['akclassvalue2'];
		}

		$this->xmlStartTag('netucate.Class',$attr);
		$this->xmlEndTag('netucate.Class');
		
		$this->xmlEndTag('netucate.API.Request');
		
		//var_dump($this->xmlDumpMem());exit;
	}

	function editClass($a_class_id,$a_data)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');

		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Edit";
		$attr['object'] = "Class";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$attr = array();
		$attr['classid'] = $a_class_id;
		$attr['name'] = $a_data['name'];
		$attr['instructoruserid'] = $a_data['instructoruserid'];
		$attr['description'] = $a_data['description'];
		$attr['alwaysopen'] = $a_data['alwaysopen'];
		//$attr['password'] = $a_data['password'];
		//$attr['message'] = $a_data['message'];
		//$attr['appsharebandwidth'] = $a_data['appsharebandwidth'];
		//$attr['bandwidth'] = $a_data['bandwidth'];
		//$attr['floorpolicy'] = $a_data['floorpolicy'];
		//$attr['conferencetypeid'] = $a_data['conferencetypeid'];
		//$attr['videobandwidth'] = $a_data['videobandwidth'];
		//$attr['videoframerate'] = $a_data['videoframerate'];
		//$attr['enablepush'] = $a_data['enablepush'];
		//$attr['issecure'] = $a_data['issecure'];
		
		// only update akclassvalues if akclassvalues are enabled
		if (array_key_exists('akclassvalue1',$a_data))
		{
			$attr['akclassvalue1'] = $a_data['akclassvalue1'];
		}
		if (array_key_exists('akclassvalue2',$a_data))
		{
			$attr['akclassvalue2'] = $a_data['akclassvalue2'];
		}

		$this->xmlStartTag('netucate.Class',$attr);
		$this->xmlEndTag('netucate.Class');
		
		$this->xmlEndTag('netucate.API.Request');

		//var_dump($this->xmlDumpMem());exit;
	}
	
	function joinClass(&$a_ilinc_user_obj,$a_ilinc_class_id)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');

		$attr = array();
		$attr['user'] = $a_ilinc_user_obj->login;
		$attr['password'] = $a_ilinc_user_obj->passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['task'] = "JoinClass";
		$attr['classid'] = $a_ilinc_class_id;
		$this->xmlStartTag('netucate.Task',$attr);
		$this->xmlEndTag('netucate.Task');

		$this->xmlEndTag('netucate.API.Request');
	}
	
	function userLogin(&$a_ilinc_user_obj)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');

		$attr = array();
		$attr['user'] = $a_ilinc_user_obj->login;
		$attr['password'] = $a_ilinc_user_obj->passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['locale'] = $a_ilinc_user_obj->user->getLanguage();
		$attr['task'] = "UserLogin";
		$this->xmlStartTag('netucate.Task',$attr);
		$this->xmlEndTag('netucate.Task');

		$this->xmlEndTag('netucate.API.Request');
	}
	
	function uploadPicture(&$a_ilinc_user_obj)
	{
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');

		$attr = array();
		$attr['user'] = $a_ilinc_user_obj->login;
		$attr['password'] = $a_ilinc_user_obj->passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['locale'] = $a_ilinc_user_obj->user->getLanguage();
		$attr['task'] = "UploadPicture";
		$this->xmlStartTag('netucate.Task',$attr);
		$this->xmlEndTag('netucate.Task');

		$this->xmlEndTag('netucate.API.Request');
	}

	function removeClass($a_icla_id)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Remove";
		$attr['object'] = "Class";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$this->xmlStartTag('netucate.Class.List');

		$attr = array();
		$attr['classid'] = $a_icla_id;
		$this->xmlStartTag('netucate.Class',$attr);
		$this->xmlEndTag('netucate.Class');
		
		$this->xmlEndTag('netucate.Class.List');
		
		$this->xmlEndTag('netucate.API.Request');
	}
	
	function findCourseClasses($a_icrs_id)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Find";
		$attr['object'] = "CourseClasses";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$attr = array();
		$attr['courseid'] = $a_icrs_id;
		$this->xmlStartTag('netucate.Course',$attr);
		$this->xmlEndTag('netucate.Course');
		
		$this->xmlEndTag('netucate.API.Request');
	}
	
	function findClass($a_class_id)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Find";
		$attr['object'] = "Class";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$attr = array();
		$attr['classid'] = $a_class_id;
		$this->xmlStartTag('netucate.Class',$attr);
		$this->xmlEndTag('netucate.Class');
		
		$this->xmlEndTag('netucate.API.Request');
	}
	
	function addCourse(&$a_icrs_arr)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Add";
		$attr['object'] = "Course";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$attr = array();
		$attr['name'] = $a_icrs_arr['title'];
		$attr['homepage'] = $a_icrs_arr['homepage']; // (optional; if present and not empty, the value will be changed)
		$attr['download'] = $a_icrs_arr['download']; // (optional; if present and not empty, the value will be changed)
		$attr['description'] = $a_icrs_arr['desc']; // (optional; if present and not empty, the value will be changed)
		$this->xmlStartTag('netucate.Course',$attr);
		$this->xmlEndTag('netucate.Course');
		
		$this->xmlEndTag('netucate.API.Request');
	}

	function editCourse($a_icrs_id,$a_icrs_arr)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Edit";
		$attr['object'] = "Course";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		// Modifies any or all of the fields in a Course record. An empty parameter in an existing attribute (except the name) will cause the corresponding field to be cleared.
		$attr = array();
		$attr['courseid'] = $a_icrs_id; // (required; existing courseID)
		$attr['name'] = $a_icrs_arr['title']; // (optional; if present and not empty, the value will be changed)
		$attr['homepage'] = $a_icrs_arr['homepage']; // (optional; if present and not empty, the value will be changed)
		$attr['download'] = $a_icrs_arr['download']; // (optional; if present and not empty, the value will be changed)
		$attr['description'] = $a_icrs_arr['desc']; // (optional; if present and not empty, the value will be changed)
		$this->xmlStartTag('netucate.Course',$attr);
		$this->xmlEndTag('netucate.Course');
		
		$this->xmlEndTag('netucate.API.Request');
	}
	
	function removeCourse($a_icrs_id)
	{
		$this->xmlClear();
		$this->xmlHeader();

		$this->xmlStartTag('netucate.API.Request');
		
		$attr = array();
		$attr['user'] = $this->reg_login;
		$attr['password'] = $this->reg_passwd;
		$attr['customerid'] = $this->customer_id;
		$attr['id'] = "";
		$attr['command'] = "Remove";
		$attr['object'] = "Course";
		$this->xmlStartTag('netucate.Command',$attr);
		$this->xmlEndTag('netucate.Command');

		$this->xmlStartTag('netucate.Course.List');

		$attr = array();
		$attr['courseid'] = $a_icrs_id;
		$this->xmlStartTag('netucate.Class',$attr);
		$this->xmlEndTag('netucate.Class');
		
		/*
		$attr = array();
		$attr['courseid'] = "2191";
		$this->xmlStartTag('netucate.Course',$attr);
		$this->xmlEndTag('netucate.Course');
		*/

		$this->xmlEndTag('netucate.Course.List');
		
		$this->xmlEndTag('netucate.API.Request');
	}

}
?>