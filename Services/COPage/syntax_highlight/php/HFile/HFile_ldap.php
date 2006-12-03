<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_ldap extends HFile{
   function HFile_ldap(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// LDAP
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array(",", ":", "=");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"accountUnlockTime" => "1", 
			"administratorContactInfo" => "1", 
			"adminUrl" => "1", 
			"audio" => "1", 
			"binary" => "1", 
			"businesscategory" => "1", 
			"carlicense" => "1", 
			"changeLogMaximumAge" => "1", 
			"changeLogMaximumSize" => "1", 
			"changeNumber" => "1", 
			"changes" => "1", 
			"changeTime" => "1", 
			"changetype" => "1", 
			"cirBeginORC" => "1", 
			"cirBindCredentials" => "1", 
			"cirBindDn" => "1", 
			"cirHost" => "1", 
			"cirLastUpdateApplied" => "1", 
			"cirPort" => "1", 
			"cirReplicaRoot" => "1", 
			"cirSyncInterval" => "1", 
			"cirUpdateFailedat" => "1", 
			"cirUpdateSchedule" => "1", 
			"cirUsePersistentSearch" => "1", 
			"cirUseSsl" => "1", 
			"cn" => "1", 
			"commonname" => "1", 
			"deleteOldRdn" => "1", 
			"departmentNumber" => "1", 
			"description" => "1", 
			"destinationIndicator" => "1", 
			"dn" => "1", 
			"employeenumber" => "1", 
			"employeeType" => "1", 
			"facsimileTelephoneNumber" => "1", 
			"filterInfo" => "1", 
			"generation" => "1", 
			"givenname" => "1", 
			"homePhone" => "1", 
			"homePostalAddress" => "1", 
			"idxno" => "1", 
			"indexnumber" => "1", 
			"initials" => "1", 
			"installationTimeStamp" => "1", 
			"internationalIsdnNumber" => "1", 
			"jpegPhoto" => "1", 
			"l" => "1", 
			"labeledURI" => "1", 
			"locality" => "1", 
			"mail" => "1", 
			"mailAccessDomain" => "1", 
			"mailalternateaddress" => "1", 
			"mailAutoReplyMode" => "1", 
			"mailAutoReplyText" => "1", 
			"maildeliveryoption" => "1", 
			"mailEnhancedUniqueMember" => "1", 
			"mailForwardingAddress" => "1", 
			"mailhost" => "1", 
			"mailMessageStore" => "1", 
			"mailProgramDeliveryInfo" => "1", 
			"mailQuota" => "1", 
			"manager" => "1", 
			"member" => "1", 
			"memberCertificateDescription" => "1", 
			"memberURL" => "1", 
			"mgrpAllowedBroadcaster" => "1", 
			"mgrpAllowedDomain" => "1", 
			"mgrpDeliverTo" => "1", 
			"mgrpErrorsTo" => "1", 
			"mgrpModerator" => "1", 
			"mgrpMsgMaxSize" => "1", 
			"mgrpMsgRejectAction" => "1", 
			"mgrpMsgRejectText" => "1", 
			"mgrpRFC822MailMember" => "1", 
			"mobile" => "1", 
			"multiLineDescription" => "1", 
			"newRdn" => "1", 
			"newSuperior" => "1", 
			"nsLicensedFor" => "1", 
			"nsLicenseEndTime" => "1", 
			"nsLicenseStartTime" => "1", 
			"ntGroupAttributes" => "1", 
			"ntGroupCreateNewGroup" => "1", 
			"ntGroupDeleteGroup" => "1", 
			"ntGroupDomainId" => "1", 
			"ntGroupId" => "1", 
			"ntUserDomainId" => "1", 
			"ntUserAcctExpires" => "1", 
			"ntUserAuthFlags" => "1", 
			"ntUserBadPwCount" => "1", 
			"ntUserCodePage" => "1", 
			"ntUserComment" => "1", 
			"ntUserCountryCode" => "1", 
			"ntUserCreateNewAccount" => "1", 
			"ntUserDeleteAccount" => "1", 
			"ntUserFlags" => "1", 
			"ntUserHomeDir" => "1", 
			"ntUserHomeDirDrive" => "1", 
			"ntUserLastLogoff" => "1", 
			"ntUserLastLogon" => "1", 
			"ntUserLogonHours" => "1", 
			"ntUserLogonServer" => "1", 
			"ntUserMaxStorage" => "1", 
			"ntUserNumLogons" => "1", 
			"ntUserParms" => "1", 
			"ntUserPasswordExpired" => "1", 
			"ntUserPrimaryGroupId" => "1", 
			"ntUserPriv" => "1", 
			"ntUserProfile" => "1", 
			"ntUserScriptPath" => "1", 
			"ntUserUniqueId" => "1", 
			"ntUserUnitsPerWeek" => "1", 
			"ntUserUsrComment" => "1", 
			"ntUserWorkstations" => "1", 
			"objectclass" => "1", 
			"owner" => "1", 
			"pager" => "1", 
			"passwordChange" => "1", 
			"passwordCheckSyntax" => "1", 
			"passwordExp" => "1", 
			"passwordExpirationTime" => "1", 
			"passwordExpWarned" => "1", 
			"passwordHistory" => "1", 
			"passwordInHistory" => "1", 
			"passwordKeepHistory" => "1", 
			"passwordLockout" => "1", 
			"passwordLockoutDuration" => "1", 
			"passwordMaxAge" => "1", 
			"passwordMaxFailure" => "1", 
			"passwordMinLength" => "1", 
			"passwordResetDuration" => "1", 
			"passwordRetryCount" => "1", 
			"passwordUnlock" => "1", 
			"passwordWarning" => "1", 
			"photo" => "1", 
			"physicaldeliveryofficename" => "1", 
			"postaladdress" => "1", 
			"postalcode" => "1", 
			"postOfficeBox" => "1", 
			"preferredDeliveryMethod" => "1", 
			"preferredLanguage" => "1", 
			"ref" => "1", 
			"registeredAddress" => "1", 
			"replicaBeginOrc" => "1", 
			"replicaBindDn" => "1", 
			"replicaBindMethod" => "1", 
			"replicaCredentials" => "1", 
			"replicaEntryFilter" => "1", 
			"replicaHost" => "1", 
			"replicaNickName" => "1", 
			"replicaPort" => "1", 
			"replicaRoot" => "1", 
			"replicatedAttributeList" => "1", 
			"replicaUpdateFailedAt" => "1", 
			"replicaUpdateReplayed" => "1", 
			"replicaUpdateSchedule" => "1", 
			"replicaUseSSL" => "1", 
			"retryCountResetTime" => "1", 
			"roleOccupant" => "1", 
			"roomnumber" => "1", 
			"searchGuide" => "1", 
			"secretary" => "1", 
			"seeAlso" => "1", 
			"serverHostName" => "1", 
			"serverProductName" => "1", 
			"serverRoot" => "1", 
			"serverVersionNumber" => "1", 
			"sn" => "1", 
			"st" => "1", 
			"stateOrProvinceName" => "1", 
			"street" => "1", 
			"surname" => "1", 
			"targetDn" => "1", 
			"telephonenumber" => "1", 
			"teletexTerminalIdentifier" => "1", 
			"telexNumber" => "1", 
			"title" => "1", 
			"uid" => "1", 
			"uniqueMember" => "1", 
			"userpassword" => "1", 
			"userCertificate" => "1", 
			"userCertificate;binary" => "1", 
			"userSMIMECertificate" => "1", 
			"userSMIMECertificate;binary" => "1", 
			"x121Address" => "1", 
			"x500UniqueIdentifier" => "1", 
			"aci" => "2", 
			"add" => "2", 
			"c" => "2", 
			"dc" => "2", 
			"delete" => "2", 
			"ftp" => "2", 
			"ftps" => "2", 
			"http" => "2", 
			"https" => "2", 
			"ldap" => "2", 
			"ldaps" => "2", 
			"modify" => "2", 
			"nntp" => "2", 
			"nntps" => "2", 
			"o" => "2", 
			"ou" => "2", 
			"URL" => "2", 
			"changeLogEntry" => "4", 
			"cirReplicaSource" => "4", 
			"crypt" => "4", 
			"dcObject" => "4", 
			"epsbranch" => "4", 
			"groupOfCertificates" => "4", 
			"groupOfMailEnhancedUniqueNames" => "4", 
			"groupOfNames" => "4", 
			"groupOfUniqueNames" => "4", 
			"httpd" => "4", 
			"inetOrgPerson" => "4", 
			"LDAPReplica" => "4", 
			"LDAPServer" => "4", 
			"mailGroup" => "4", 
			"mailRecipient" => "4", 
			"netscapeServer" => "4", 
			"nsLicenseUser" => "4", 
			"ntGroup" => "4", 
			"ntUser" => "4", 
			"organization" => "4", 
			"organizationalPerson" => "4", 
			"organizationalRole" => "4", 
			"organizationalUnit" => "4", 
			"passwordObject" => "4", 
			"passwordPolicy" => "4", 
			"person" => "4", 
			"referral" => "4", 
			"residentialPerson" => "4", 
			"SHA" => "4", 
			"slapd" => "4", 
			"top" => "4");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"4" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
