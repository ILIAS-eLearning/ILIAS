# Rules for the ./Services internal interface documentation
 
1. Each subdirectory under ./Services SHOULD contain a README.md file
2. All internal interfaces provided by the service SHOULD be documented in the README.md file.

# Example: User Action Interface

The user action interface allows consuming components to implement
* **user actions** (e.g. show profile, add as contact, send mail) in contexts that list these actions 
(e.g. profile, members gallery, who-is-only tool) and
* the **user action contexts** that present these actions.

## User Actions

In both cases you first need to add an entry to your service.xml or module.xml.
````
<?xml version = "1.0" encoding = "UTF-8"?>
<service xmlns="http://www.w3.org" version="$Id$" id="...">
	...
	<implements component="user" api="user_action" dir="classes"/>
	...
</service>
````

The dir-attribute tells the user action service where to find an implemetation class that you need
provide. It has to extend Services/User/UserActions/class.ilUserActionProvider.php.

Example:
````
<?php

include_once("./Services/User/Actions/classes/class.ilUserActionProvider.php");

/**
 * Adds link to mail
 * ...
 */
class ilMailUserActionProvider extends ilUserActionProvider
{
	...

	/**
	 * @inheritdoc
	 */
	function getComponentId()
	{
		return "mail";
	}

	/**
	 * @inheritdoc
	 */
	function getActionTypes()
	{
		return array(
			"compose" => $this->lng->txt("mail")
		);
	}

	/**
	 * Collect user actions
	 *
	 * @param int $a_target_user target user
	 * @return ilUserActionCollection collection
	 */
	function collectActionsForTargetUser($a_target_user)
	{
		$coll = ilUserActionCollection::getInstance();
		include_once("./Services/User/Actions/classes/class.ilUserAction.php");
		include_once("./Services/Mail/classes/class.ilMailFormCall.php");

		// check mail permission of user
		if ($this->getUserId() == ANONYMOUS_USER_ID || !$this->checkUserMailAccess($this->getUserId()))
		{
			return $coll;
		}

		// check mail permission of target user
		if ($this->checkUserMailAccess($a_target_user))
		{
			$f = new ilUserAction();
			$f->setType("compose");
			$f->setText($this->lng->txt("mail"));
			$tn = ilObjUser::_lookupName($a_target_user);
			$f->setHref(ilMailFormCall::getLinkTarget("", '', array(), array('type' => 'new', 'rcp_to' => urlencode($tn["login"]))));
			$coll->addAction($f);
		}

		return $coll;
	}
}
?>
````


## User Action Contexts

To implement a context you also need a similar entry in yout service.xml or module.xml.

````
<?xml version = "1.0" encoding = "UTF-8"?>
<service xmlns="http://www.w3.org" version="$Id$" id="...">
	...
	<implements component="user" api="user_action_context" dir="classes"/>
	...
</service>
````

Again the dir-attribute tells the user action service where to find your implementation class. This
time it needs to extend the abstract class Services/User/Actions/Contexts/class.ilUserActionContext.php.

Example:
````
<?php

/**
 * Awareness context for user actions
 * ...
 */
class ilAwarenessUserActionContext extends ilUserActionContext
{
	/**
	 * @inheritdoc
	 */
	function getComponentId()
	{
		return "awrn";
	}

	/**
	 * @inheritdoc
	 */
	function getContextId()
	{
		return "toplist";
	}

}

?>
````
