 /*******************************************************************************
**
** FileName: SCOPlayerWrapper.js
**
*******************************************************************************/

/*******************************************************************************
**
** Advanced Distributed Learning (ADL) grants you ("Licensee") a non-
** exclusive, royalty free, license to use, modify and redistribute this
** software in source and binary code form, provided that i) this copyright
** notice and license appear on all copies of the software; and ii)Licensee does
** not utilize the software in a manner which is disparaging to ADL.
**
** This software is provided "AS IS," without a warranty of any kind.  ALL
** EXPRESS OR IMPLIED CONDITIONS, REPRESENTATIONS AND WARRANTIES, INCLUDING ANY
** IMPLIED WARRANTY OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE OR NON-
** INFRINGEMENT, ARE HEREBY EXCLUDED.  ADL AND ITS LICENSORS SHALL NOT BE LIABLE
** FOR ANY DAMAGES SUFFERED BY LICENSEE AS A RESULT OF USING, MODIFYING OR
** DISTRIBUTING THE SOFTWARE OR ITS DERIVATIVES.  IN NO EVENT WILL ADL  OR ITS
** LICENSORS BE LIABLE FOR ANY LOST REVENUE, PROFIT OR DATA, OR FOR DIRECT,
** INDIRECT, SPECIAL, CONSEQUENTIAL, INCIDENTAL OR PUNITIVE DAMAGES, HOWEVER
** CAUSED AND REGARDLESS OF THE THEORY OF LIABILITY, ARISING OUT OF THE USE OF
** OR INABILITY TO USE SOFTWARE, EVEN IF ADL HAS BEEN ADVISED OF THE POSSIBILITY
** OF SUCH DAMAGES.
**
*******************************************************************************/

// Define exception/error codes
var NO_ERROR = 0;
var NOT_INITIALIZED = 301;

// If the API Call is translated and passed on to the LMS for processing, then
// the internal error code should be API_CALLED_PASSED_TO_LMS
var API_CALL_PASSED_TO_LMS =  0;

// If the API Call is translated and processed by the API Wrapper, then
// the internal error code should be API_CALLED_NOT_PASSED_TO_LMS
var API_CALL_NOT_PASSED_TO_LMS = 1;

// Local variable definitions
var apiHandle = null;
var api = null;
var findAPITries = 0;
var _InternalErrorCode = API_CALL_PASSED_TO_LMS;
var hours = 0;
var minutes = 0;
var seconds = 0;

var statusRequest = null;
var objectivesFlag = "";
var objectivesStatusRequestArr = new Array();
var elementRequestArr = new Array();
var keyList = new Array(25);
var valueList = new Array(25);

/*******************************************************************************
**
** Function: doLMSInitialize()
** Inputs:  None
** Return:  Returns true if the initialization was successful, or
**          returns false if the initialization failed.
**
** Description:
** Initialize communication with LMS by calling the Initialize() API method
**
*******************************************************************************/
function doLMSInitialize()
{
   var result = "false";
   
   if (api == null)
   {
      api = getAPIHandle();
   }
   
   if ( api != null )
   {
      // Set the internal error code being tracked by the API Wrapper to 0
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;

      // Invoke the Initialize("") API method 
      result = api.Initialize("");
   }

   if ( result != "true" )
   {
      determineError();
   }

   return result;
}

/*******************************************************************************
**
** Function doLMSFinish()
** Inputs:  None
** Return:  Returns true if the termination of communication was successful, or
**          returns false if the termination of communication was unsuccessful
**
** Description:
** Terminate communication with LMS by calling the Terminate() API method
**
*******************************************************************************/
function doLMSFinish()
{
   var result = "false";

   if (api == null)
   {
      api = getAPIHandle();
   }
   
   if ( api != null )
   {
      // Set the internal error code being tracked by the API Wrapper to 0
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;

      // Invoke the Terminate("") API method 
      result = api.Terminate("");
   }

   if ( result != "true" )
   {
      determineError();
   }

   return result;
}

/*******************************************************************************
**
** Function doLMSGetValue(name)
** Inputs:  name - string representing a data model element 
**
** Return:  The value presently stored by the LMS for the data model element
**
** Description:
** Requests the value for the data model element
**
*******************************************************************************/
function doLMSGetValue(name)
{
   initializeConversionTables();
   // The value currently being stored by the LMS for the data model element
   var getValueReturn = "";

   if (api == null)
   {
      api = getAPIHandle();
   }

   // Spilt the name value on '.'
   elementRequestArr = name.split(".");
   
   // First check is using SCORM cmi datamodel
   if ( elementRequestArr[0] == "cmi" )
   {
      // Check if call is requesting any children elements
      var tempArrCount = elementRequestArr.length - 1;   
      if ( elementRequestArr[tempArrCount] == "_children" )
      {
         getValueReturn = childrenGetRequest(name, elementRequestArr);
         _InternalErrorCode = API_CALL_NOT_PASSED_TO_LMS;
      }
      else
      {
         getValueReturn = translateDataModelElement(name);
      }
   }
   else
   { // send call through using a different data model
      // Normal getValue() Call
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;
      getValueReturn = api.GetValue(updatedName);
   }
   
   return getValueReturn;
}   

/*******************************************************************************
**
** Function doLMSSetValue(name, value)
** Inputs:  name -string representing the data model defined category or element
**          value -the value that the named element or category will be assigned
** Return:  true if successful
**          false if failed.
**
** Description:
** Converts the element name to the Updated element name following the
** SCORM 2004 data model. Also converts the value to the appropriate format
** when neccessary.
**
*******************************************************************************/
function doLMSSetValue(name, value)
{
   initializeConversionTables();
   if (api == null)
   {
      api = getAPIHandle();
   }

   var setValueReturn = "";
   
   // Spilt the name value on '.'
   var elementRequestArr = name.split(".");
   
   // 1st Check is using SCORM cmi datamodel
   if ( elementRequestArr[0] == "cmi" )
   {
      setValueReturn = dmElementSetFunction(name, value);
   }
   else
   {
      // Normal setValue() Call
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;
      setValueReturn = api.SetValue(updatedName, value);
   }
   
   return setValueReturn;
}

/*******************************************************************************
**
** Function doLMSCommit()
** Inputs:  None
** Return:  None
**
** Description:
** Calls the LMSCommit function 
**
*******************************************************************************/
function doLMSCommit()
{
   if (api == null)
   {
      api = getAPIHandle();
   }

   _InternalErrorCode = API_CALL_PASSED_TO_LMS;
   var result = api.Commit("");
   if ( result != "true" )
   {
      var err = determineError();
   }

   return result;
}

/*******************************************************************************
**
** Function doLMSGetLastError()
** Inputs:  None
** Return:  The error code that was set by the last LMS function call
**
** Description:
** Calls the LMSGetLastError function, if the InternalErrorCode variable has
** previously been set to 1 then the returning value is NO_ERROR. 
** InternalErrorCode is set to 1 upon recieving a value that is not passed onto 
** the LMS and converted internaly by the SCOPlayerWrapper.
**
*******************************************************************************/
function doLMSGetLastError()
{
   var result = null;
   if ( _InternalErrorCode == API_CALL_NOT_PASSED_TO_LMS )
   {
      // There is no error the APIWrapper caught the last call and did not
      // comunicate with the LMS
      result = NO_ERROR;
   }
   else
   {
      var api = getAPIHandle();
      
      var errorcode13 = api.GetLastError().toString();
      result = getOldErrorValue( errorcode13 );            
   }   

   return result;
}

/*******************************************************************************
**
** Function doLMSGetErrorString(errorCode)
** Inputs:  errorCode - Error Code
** Return:  The textual description that corresponds to the input error code
**
** Description:
** Calls LMSGetErrorString function 
**
*******************************************************************************/
function doLMSGetErrorString(errorCode)
{
   if (api == null)
   {
      api = getAPIHandle();
   }

   var errString = getErrorString(errorCode);
   return errString;
}

/*******************************************************************************
**
** Function doLMSGetDiagnostic(errorCode)
** Inputs:  errorCode - Error Code(integer format), or null
** Return:  The vendor specific textual description that corresponds to the 
**          input error code
**
** Description:
** Calls LMSGetDiagnostic function
**
*******************************************************************************/
function doLMSGetDiagnostic(errorCode)
{
   if (api == null)
   {
      api = getAPIHandle();
   }

   var errString = getErrorString(errorCode);
   return errString;
}


/*******************************************************************************
**
** Function determineError()
** Inputs:  None
** Return:  The current value of the LMS Error Code
**
** Description:
** Determines if an error was encountered by the previous API call
** and if so, displays a message to the user.  If the error code
** has associated text it is also displayed.
**
*******************************************************************************/
function determineError()
{
   if (api == null)
   {
      api = getAPIHandle();
   }

   // check for errors caused by or from the LMS
   var errCode = doLMSGetLastError().toString();
   
   if ( errCode != NO_ERROR )
   {
      // an error was encountered so display the error description
      var errDescription = doLMSGetErrorString(errCode);
   }

   return errCode;
}

/******************************************************************************
**
** Function getAPIHandle()
** Inputs:  None
** Return:  value contained by APIHandle
**
** Description:
** Returns the handle to API object if it was previously set,
** otherwise it returns null
**
*******************************************************************************/
function getAPIHandle()
{
   if ( apiHandle == null )
   {
      apiHandle = getAPI();
   }

   if ( apiHandle == null )
   {
      alert("Unable to locate the LMS's API Implementation.");
   }
   return apiHandle;
}

/*******************************************************************************
**
** Function findAPI(win)
** Inputs:  win - a Window Object
** Return:  If an API_1484_11 object is found, it's returned, 
**          otherwise null is returned
**
** Description:
** This function looks for an object named API in parent and opener windows
**
*******************************************************************************/
function findAPI(win)
{
   while ( (win.API_1484_11 == null)&&(win.parent != null)&&(win.parent != win) )
   {
      findAPITries++;
      
      if ( findAPITries > 500 )
      {
         alert("Error finding API -- too deeply nested.");
         return null;
      }
      win = win.parent;
   }
   
   return win.API_1484_11;
}

/*******************************************************************************
**
** Function getAPI()
** Inputs:  none
** Return:  If an API object is found, it's returned, otherwise null is returned
**
** Description:
** This function looks for an object named API, first in the current window's 
** frame hierarchy and then, if necessary, in the current window's opener window
** hierarchy (if there is an opener window).
**
*******************************************************************************/
function getAPI()
{
   var theAPI = findAPI(window);
   if ( (theAPI == null) && (window.opener != null) && (typeof(window.opener)
                                                        != "undefined") )
   {
      theAPI = findAPI(window.opener);
   }
   if ( theAPI == null )
   {
      alert("Unable to find an API adapter");
   }
   return theAPI;
}

/*******************************************************************************
**
** Function childrenGetRequest(name, elementRequestArr)
** Inputs:  element name and the elemntRequestArr variable
** Return:  Returns the list of children to the 1.2 SCO
**
** Description:
** The Wrapper acts alone and returns the 1.2 conformant list of children for
** the particular element.
*******************************************************************************/ 
function childrenGetRequest(name, elementRequestArr)
{
   var childrenListing = "";
   
   if ( name == "cmi.core._children" )
   {
      childrenListing = "student_id,student_name,lesson_location,credit," 
      + "lesson_status,entry, score,total_time,lesson_mode," 
      + "exit,session_time";
   }
   else if ( name == "cmi.core.score._children" )
   {
      childrenListing = "raw,min,max";
   }
   else if ( name == "cmi.student_data._children" )
   {
      childrenListing = "mastery_score,max_time_allowed,time_limit_action";
   }
   else if ( name == "cmi.objectives._children" )
   {
      childrenListing = "id,score,status";
   }
   else if ( name == "cmi.student_preference._children" )
   {
      childrenListing = "audio,language,speed,text";
   }
   else if ( name == "cmi.interactions._children" )
   {
      childrenListing = "id,objectives,time,type,correct_responses,weighting," +
                        "student_response,result,latency";
   }
   else if ( name == "cmi.objectives." + 
                                     elementRequestArr[2] + ".score._children" )
   {

      childrenListing = "raw,min,max";
   }

   return childrenListing;
}

/******************************************************************************
**
** Function: translateDataModelElement()
** Inputs: SCORM 1.2 datamodel element name 
** Return: The value of the element retrieving
**
** Description:
** translateDataModelElement take in the name of the SCORM 1.2 datamodel 
** element and converts the element to conformant SCORM 2004 syntax before 
** initiating the actuall communication to the SCORM 2004 LMS. The return value
** is then sent back to the SCORM 1.2 calling sco. Three special cases exist for
** elements that require additional more complicated conversions, they are for
** core elements, objective and interactions.
**
******************************************************************************/ 
function translateDataModelElement(name)
{
   var DataModelElementReturnVal = "";

   if (api == null)
   {
      api = getAPIHandle();
   }

   var updatedName = "";
   arrayOfComponents = name.split(".");

   switch ( arrayOfComponents[1] )
   {
   case "core":
      { 
         DataModelElementReturnVal = convertCore(name,arrayOfComponents);
         return DataModelElementReturnVal;  
      }
   case "comments":
      {
         updatedName = getNewValue(name);
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         DataModelElementReturnVal = api.GetValue(updatedName);
      }
   case "comments_from_lms":
      {
         updatedName = getNewValue(name);
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         DataModelElementReturnVal = api.GetValue(updatedName);
      }
   case "objectives":
      {
         DataModelElementReturnVal = convertObjectives(name,arrayOfComponents);
         return DataModelElementReturnVal;      
      }
   case "student_data":
      {
         updatedName = getNewValue(name);
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         DataModelElementReturnVal = api.GetValue(updatedName);
      }
   case "student_preference":
      {
         updatedName = getNewValue(name);
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         DataModelElementReturnVal = api.GetValue(updatedName);
      }
   case "suspend_data":
      {
         updatedName = getNewValue(name);
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         DataModelElementReturnVal = api.GetValue(updatedName);
      }
   case "launch_data":
      {
         updatedName = getNewValue(name);
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         DataModelElementReturnVal = api.GetValue(updatedName);
      }
   case "interactions":
      {
         DataModelElementReturnVal = convertInteractions(name,
                                                            arrayOfComponents);
         return DataModelElementReturnVal;  
      }
   }

   return DataModelElementReturnVal;
}

/******************************************************************************
**
** Function: convertCore()
** Inputs: core element name  
** Return: the value of the element retrieving
**
** Description: Special Case 1 of 3 for GetValue
** ConvertCore accepts a SCORM 1.2 datamodel element and then converts the
** element to valid SCORM 2004 syntax before passing on the call to the SCORM
** 2004 LMS. The function then returns the result to the calling function.
**
******************************************************************************/
function convertCore(name,arrayOfComponents)
{
   var coreReturnValue = "";
   var updatedName = getNewValue(name);
   if (api == null)
   {
      api = getAPIHandle();
   }
   // Special "core" case for lesson_status
   if ( updatedName == "cmi.core.lesson_status" )
   {
      if ( statusRequest == null && api.GetValue('cmi.completion_status'))
      {
         statusRequest = api.GetValue('cmi.completion_status');
      }
      else if( statusRequest == null && api.GetValue('cmi.success_status'))
      {
         statusRequest = api.GetValue('cmi.success_status');
      }

      if ( statusRequest == null )
      {
         // The cmi.core.lesson_status was never set by the SCO.
         // Return the default value of not attempted
         _InternalErrorCode = 1;
         coreReturnValue = "not attempted";
         return coreReturnValue;
      }
      else if ( statusRequest == "browsed" )
      {
         _InternalErrorCode = API_CALL_NOT_PASSED_TO_LMS;
         coreReturnValue = "browsed";
         return coreReturnValue;
      }
      else
      {
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         //coreReturnValue = api.GetValue(statusRequest);
         coreReturnValue=statusRequest;
         return coreReturnValue;
      }
   }
   else
   {
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;
      coreReturnValue = api.GetValue(updatedName);
   }
   return coreReturnValue;
}

/******************************************************************************
**
** Function: convertObjectives()
** Inputs: objectives element name 
** Return: the value of the element retrieving
**
** Description: Special Case 2 of 3 for GetValue
** convertObjectives accepts a SCORM 1.2 datamodel element and then converts the
** element to valid SCORM 2004 syntax before passing on the call to the SCORM
** 2004 LMS. The function then returns the result to the calling function.
******************************************************************************/
function convertObjectives(name,arrayOfComponents)
{
   var objReturnValue = "";
   var updatedName = getNewValue(name);

   if (api == null)
   {
      api = getAPIHandle();
   }
   
   if ( arrayOfComponents[3] == "status" )
   {
      if ( objectivesStatusRequestArr[arrayOfComponents[2]] == null )
      {
         _InternalErrorCode = 1;
         objReturnValue = "not attempted"; 
      }
      else if ( objectivesFlag == "browsed")
      {
         _InternalErrorCode = API_CALL_NOT_PASSED_TO_LMS;
         objReturnValue = "browsed";
      }
      else
      {
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         objReturnValue = api.GetValue(
                              objectivesStatusRequestArr[arrayOfComponents[2]]); 
      }
   }
   else
   {
      updatedName = getNewValue(name);
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;
      objReturnValue = api.GetValue(updatedName);
   }
   
   return objReturnValue;
}

/******************************************************************************
**
** Function: convertInteractions()
** Inputs: interactions data model name 
** Return: the value of the element retrieving
**
** Description: Special Case 3 of 3 for GetValue
** convertInteractions accepts a SCORM 1.2 datamodel element and then converts 
** the element to valid SCORM 2004 syntax before passing on the call to the 
** SCORM 2004 LMS. The function then returns the result to the calling function.
******************************************************************************/
function convertInteractions(name,arrayOfComponents)
{
   var interReturnValue = "";
   var updatedName = getNewValue(name);

   if (api == null)
   {
      api = getAPIHandle();
   }
   
   if ( arrayOfComponents[3] == "time" )
   {
      // Make appropriate call
      InternalErrorCode = API_CALL_PASSED_TO_LMS;
      var result1 = api.GetValue("cmi.interactions." + 
                                           arrayOfComponents[2] + ".timestamp");
      
      // Convert 2004 format to 1.2 format
      newtimeArray = result1.split("T");
      
      // Position 0 is thrown out 
      interReturnValue = newtimeArray[1];
   }
   else if ( arrayOfComponents[3] == "result" )
   {
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;
      interReturnValue = api.GetValue(name);

      // Check for a return value of "incorrect" can convert to "wrong"
      if ( interReturnValue == "incorrect" )
      {
         interReturnValue = "wrong";
      }
   }
   else
   {
      updatedName = getNewValue(name);
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;
      interReturnValue = api.GetValue(updatedName);
   }
   
   return interReturnValue;
}

/******************************************************************************
**
** Function: dmElementSetFunction()
** Inputs: data model element name to set and the value attempting to set
** Return: boolean value true or false if the value was correctly set
**
** Description:
** dmElementSetFunction takes in the name of the SCORM 1.2 datamodel 
** element and the desired value to set the element equal to. Prior to calling
** the SetValue call to the LMS the function converts the element to conformant
** SCORM 2004 syntax and in some cases formats the value data to meet SCORM 2004
** standards.  Upon calling the SetValue call the return value of true or false 
** is returned to the originating calling line.Three special cases exist for
** elements that require additional more complicated conversions, they are for
** core elements, objective and interactions. All other normal calls fall into
** the default case.
**
******************************************************************************/ 
function dmElementSetFunction(name, value)
{
   var setReturnValue = "";

   if (api == null)
   {
      api = getAPIHandle();
   }

   var setNameUpdate = getNewValue(name); 
   arrayOfComponents = name.split(".");

   switch ( arrayOfComponents[1] )
   {
   case "core":
      {
         setReturnValue = setConvertCore(name,value,arrayOfComponents);
         return setReturnValue;
      }
   case "objectives":
      {
         setReturnValue = setConvertObjectives(name,value,arrayOfComponents);
         return setReturnValue; 
      }    
   case "interactions":
      {
         setReturnValue = setConvertInteractions(name,value,arrayOfComponents);
         return setReturnValue; 
      }
   default:
      {
         // Normal setValue() Call
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         setReturnValue = api.SetValue(setNameUpdate, value);
      }
   }

   return setReturnValue;
}

/******************************************************************************
**
** Function: setConvertCore()
** Inputs: core data model element 
** Return: boolean true or false depending on success or failure of the call
**
** Description: Special Case 1 of 3 for SetValue
** setConvertCore accepts a valid SCORM 1.2 element and value and converts the 
** call into conformant SCORM 2004 syntax. Upon making the conversion the 
** SetValue call is made to the LMS and returns a boolean value upon the the 
** sucess or failure of the call.
**
******************************************************************************/
function setConvertCore(name,value,arrayOfComponents)
{
   var coreReturnValue = "";
   var coreUpdatedName = getNewValue(name);

   if (api == null)
   {
      api = getAPIHandle();
   }
   
   if ( name == "cmi.core.lesson_status" )
   {
      // Check setNameUpdate and determine which element to set
      if ( (value == "completed") || (value == "incomplete") || 
           (value == "not attempted") )
      {
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         coreReturnValue = api.SetValue("cmi.completion_status", value);
         statusRequest = "cmi.completion_status";
         return coreReturnValue;
      }
      else if ( (value == "passed") || (value == "failed") )
      {
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         coreReturnValue = api.SetValue("cmi.success_status", value);
         statusRequest = "cmi.success_status";
         return coreReturnValue;
      }
      else if (value == "browsed")
      {
         _InternalErrorCode = API_CALL_NOT_PASSED_TO_LMS;
         coreReturnValue = true;
         statusRequest = "browsed";
         return coreReturnValue;
      }

   }
   else if ( name == "cmi.core.session_time" )
   {
      timeArray = new Array(4);
      timeArray = value.split(":");
      
      hours = timeArray[0];
      minutes = timeArray[1];
      seconds = timeArray[2];
      
      var newvalue = "PT" + hours + "H" + minutes + "M" + seconds + "S";
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;
      setReturnValue = api.SetValue(coreUpdatedName, newvalue);
      return coreReturnValue;      
   }
   else
   { // Normal core set value call
      // Normal setValue() Call         
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;
      setReturnValue = api.SetValue(coreUpdatedName, value);
      return coreReturnValue;
   }
   
   return coreReturnValue;
}

/******************************************************************************
**
** Function: setConvertObjectives()
** Inputs: objectives data model element 
** Return: boolean true or false depending on success or failure of the call
**
** Description: Special Case 2 of 3 for SetValue
** setConvertObjectives accepts a valid SCORM 1.2 element and value and converts  
** the call into conformant SCORM 2004 syntax. Upon making the conversion the 
** SetValue call is made to the LMS and returns a boolean value upon the the 
** sucess or failure of the call.
**
******************************************************************************/
function setConvertObjectives(name,value,arrayOfComponents)
{
   var objReturnValue = "";
   var objUpdatedName = getNewValue(name);

   if (api == null)
   {
      api = getAPIHandle();
   }
   
   if ( arrayOfComponents[3] == "status" )
   {
      if ( (value == "passed") || (value == "failed") )
      {
         // Reset Objectives Flag
         objectivesFlag = "";
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         objReturnValue = api.SetValue("cmi.objectives." + 
                               arrayOfComponents[2] + ".success_status", value);
         objectivesStatusRequestArr[arrayOfComponents[2]] = "cmi.objectives." + 
                                       arrayOfComponents[2] + ".success_status";
         return objReturnValue;
      }
      else if ( (value == "completed") || (value == "incomplete") || 
                (value == "not attempted") )
      {
         // Reset Objectives Flag
         objectivesFlag = "";
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         objReturnValue = api.SetValue("cmi.objectives." + arrayOfComponents[2]+ 
                                       ".completion_status", value);
         objectivesStatusRequestArr[arrayOfComponents[2]] = "cmi.objectives." + 
                                    arrayOfComponents[2] + ".completion_status";
         return objReturnValue;       
      }
      else if ( value == "browsed")
      {
        _InternalErrorCode = API_CALL_NOT_PASSED_TO_LMS;
        // Set objectives flag
        objectivesFlag = "browsed";
        objReturnValue = true;
        return objReturnValue;
      }
   }
   else
   { // Normal set objective call
      // Normal setValue() Call
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;          
      objReturnValue = api.SetValue(objUpdatedName, value);
      return objReturnValue;
   }
   
   return objReturnValue;
}

/******************************************************************************
**
** Function: setConvertInteractions()
** Inputs: interactions data model element 
** Return: boolean true or false depending on success or failure of the call
**
** Description: Special Case 3 of 3 for SetValue
** setConvertInteractions accepts a valid SCORM 1.2 element and value and   
** converts the call into conformant SCORM 2004 syntax. Upon making the  
** conversion the SetValue call is made to the LMS and returns a boolean value  
** upon the the sucess or failure of the call.
**
******************************************************************************/
function setConvertInteractions(name,value,arrayOfComponents)
{
   var interReturnValue = "";
   var interUpdatedName = getNewValue(name);

   if (api == null)
   {
      api = getAPIHandle();
   }
   
   if ( arrayOfComponents[3] == "latency" )
   {
      timeArray = new Array(4);
      timeArray = value.split(":");
      
      hours = timeArray[0];
      minutes = timeArray[1];
      seconds = timeArray[2];
      
      var newvalue = "PT" + hours + "H" + minutes + "M" + seconds + "S"; 
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;        
      interReturnValue = api.SetValue(name, newvalue);
      return interReturnValue;     
   }
   else if ( arrayOfComponents[3] == "time" )
   {
      // Convert the time format to correct format
      var now = new Date();
      var year = now.getYear();
      var month = now.getMonth();
      
      if ( month <= 9 )
      {
         month = "0" + month;
      }

      var day = now.getDay();
      
      if ( day <= 9 )
      {
         day = "0" + day;
      }

      var newValue = year + "-" + month + "-" + day + "T" + value;
      
      // Setting interactions.timestamp to updated 2004 time format
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;
      var result1 = api.SetValue("cmi.interactions." + arrayOfComponents[2] + 
                                 ".timestamp", newValue);
      return result1;
   }
   else if ( arrayOfComponents[3] == "result" )
   {
      // Check Value sending in to set
      if ( value == "wrong" )
      {
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         interReturnValue = api.SetValue(interUpdatedName, "incorrect");
         return interReturnValue;
      }
      else
      {
         _InternalErrorCode = API_CALL_PASSED_TO_LMS;
         interReturnValue = api.SetValue(interUpdatedName, value);
         return interReturnValue;
      }
   }
   else
   { // Normal core set value call
      // Normal setValue() Call         
      _InternalErrorCode = API_CALL_PASSED_TO_LMS;
      interReturnValue = api.SetValue(interUpdatedName, value);
      return interReturnValue;
   }
   
   return interReturnValue;
}

/******************************************************************************
**
** Function: fillKeyList()
** Inputs:  None
** Return:  None
**
** Description:
** fillKeyList is called upon initiation of the file and populates a list array
** used in the getNewValue function. The finished list contains the conformant
** SCORM 1.2 data model elements that may need converted to SCORM 2004.  
**
******************************************************************************/ 
function fillKeyList()
{
   // Fill the list of keys (old data model elements)
   keyList[0] = "cmi.core.student_id";
   keyList[1] = "cmi.core.student_name";
   keyList[2] = "cmi.core.lesson_location";
   keyList[3] = "cmi.core.credit";
   keyList[4] = "cmi.core.entry";
   keyList[5] = "cmi.core.score.raw";
   keyList[6] = "cmi.core.score.max";
   keyList[7] = "cmi.core.score.min";
   keyList[8] = "cmi.core.total_time";
   keyList[9] = "cmi.core.lesson_mode";
   keyList[10] = "cmi.core.exit";
   keyList[11] = "cmi.core.session_time";
   keyList[12] = "cmi.core.score._children";
   keyList[13] = "cmi.student_preference._children";
   keyList[14] = "cmi.student_preference.audio";
   keyList[15] = "cmi.student_preference.language";
   keyList[16] = "cmi.student_preference.speed";
   keyList[17] = "cmi.student_preference.text";
   keyList[18] = "cmi.student_data.mastery_score";
   keyList[19] = "cmi.student_data.max_time_allowed";
   keyList[20] = "cmi.student_data.time_limit_action";
   keyList[21] = "cmi.comments_from_lms";
   keyList[22] = "cmi.comments";
}

/******************************************************************************
**
** Function: fillValueList()
** Inputs:  None
** Return:  None 
**
** Description:
** fillValueList is called upon initiation of the file and populates a list array
** used in the getNewValue function. This finished list contains the appropriate
** updated SCORM 2004 data model elements.
**
******************************************************************************/   
function fillValueList()
{
   // Fill the list of values (new data model elements)
   valueList[0] = "cmi.learner_id";
   valueList[1] = "cmi.learner_name";
   valueList[2] = "cmi.location";
   valueList[3] = "cmi.credit";
   valueList[4] = "cmi.entry";
   valueList[5] = "cmi.score.raw";
   valueList[6] = "cmi.score.max";
   valueList[7] = "cmi.score.min";
   valueList[8] = "cmi.total_time";
   valueList[9] = "cmi.mode";
   valueList[10] = "cmi.exit";
   valueList[11] = "cmi.session_time";
   valueList[12] = "cmi.score._children";
   valueList[13] = "cmi.learner_preference._children";
   valueList[14] = "cmi.learner_preference.audio_level";
   valueList[15] = "cmi.learner_preference.language";
   valueList[16] = "cmi.learner_preference.delivery_speed";
   valueList[17] = "cmi.learner_preference.audio_captioning";
   valueList[18] = "cmi.scaled_passing_score";
   valueList[19] = "cmi.max_time_allowed";
   valueList[20] = "cmi.time_limit_action";
   valueList[21] = "cmi.comments_from_lms.0.comment";
   valueList[22] = "cmi.comments_from_learner.0.comment";
}

/******************************************************************************
**
** Function: getNewValue()
** Inputs: SCORM 1.2 data model element 
** Return: corresponding SCORM 2004 data model element
**
** Description:
** getNewValue take in the old SCORM 1.2 datamodel element and by using the 
** previously set list arrays returns the corresponding SCORM 2004 data model
** element.
**
******************************************************************************/   
function getNewValue( key )
{
   var keyResult = key;
   
   // Check to see if result is cmi.interactions
   var checkValue = keyResult.substring(0,16);
   
   if ( checkValue == "cmi.interactions" )
   {
      // Check for cmi.interactions.n.time
      checkValue2 =  keyResult.substring(19,23);
      
      if ( checkValue2 == "time" )
      {
         // Return cmi.interactions.n.timestamp
         var subString1 = keyResult.substring(0,19);
         keyResult = subString1 + "timestamp";
      }
      else if ( checkValue2 == "stud" )
      {
         // Return cmi.interactions.n.learner_response
         var subString1 = keyResult.substring(0,19);
         keyResult = subString1 + "learner_response";
      }
   }

   for ( i=0; i < keyList.length; i++ )
   {
      if ( keyList[i] == key )
      {
         keyResult = valueList[i];
         break;
      }
   }   
   return keyResult;
}

/******************************************************************************
**
** Function: fillErrorList()
** Inputs:  None
** Return:  None 
**
** Description:
** fillErrorList creates a list array of the SCORM 1.2 error codes
** This array is used in converting the error codes.
**
******************************************************************************/ 
var errorList = new Array(25);
var errorStringList = new Array(25);
var newErrorList = new Array(25);
var errorcodeList = new Array(25);

function fillErrorList()
{
   // Fill the list of erorrs (old error codes)
   errorList[0] = "0";
   errorList[1] = "101";
   errorList[2] = "101";
   errorList[3] = "101";
   errorList[4] = "101";
   errorList[5] = "101";
   errorList[6] = "301";
   errorList[7] = "101";
   errorList[8] = "122";
   errorList[9] = "101";
   errorList[10] = "301";
   errorList[11] = "101";
   errorList[12] = "301";
   errorList[13] = "143";
   errorList[14] = "201";
   errorList[15] = "101";
   errorList[16] = "101";
   errorList[17] = "101";
   errorList[18] = "401";
   errorList[19] = "401";
   errorList[20] = "301";
   errorList[21] = "403";
   errorList[22] = "404";
   errorList[23] = "405";
   errorList[24] = "405";
   errorList[25] = "405";
}

/******************************************************************************
**
** Function: fillnewErrorList()
** Inputs:  None
** Return:  None
**
** Description:
** fillnewErrorList creates a list array containing the SCORM 2004 error codes
** This array is used in converting the error codes.
**
******************************************************************************/   
function fillnewErrorList()
{
   // Fill the list of values (new error codes)
   newErrorList[0] = "0";
   newErrorList[1] = "101";
   newErrorList[2] = "102";
   newErrorList[3] = "103";
   newErrorList[4] = "104";
   newErrorList[5] = "111";
   newErrorList[6] = "112";
   newErrorList[7] = "113";
   newErrorList[8] = "122";
   newErrorList[9] = "123";
   newErrorList[10] = "132";
   newErrorList[11] = "133";
   newErrorList[12] = "142";
   newErrorList[13] = "143";
   newErrorList[14] = "201";
   newErrorList[15] = "301";
   newErrorList[16] = "351";
   newErrorList[17] = "391";
   newErrorList[18] = "401";
   newErrorList[19] = "402";
   newErrorList[20] = "403";
   newErrorList[21] = "404";
   newErrorList[22] = "405";
   newErrorList[23] = "406";
   newErrorList[24] = "407";
   newErrorList[25] = "408";
}

/******************************************************************************
**
** Function: fillErrorStringList()
** Inputs: none
** Return: none
**
** Description:
** fillErrorStringList creates a list array with SCORM 1.2 error strings.
**
******************************************************************************/
function fillErrorStringList()
{
   // Fill the list of 1.2 erorr strings
   errorStringList[0] = "No error";
   errorStringList[1] = "General Exception";
   errorStringList[2] = "Invalid Augment error";
   errorStringList[3] = "Element Cannot have Children";
   errorStringList[4] = "Element not an array - cannot have count";
   errorStringList[5] = "Not Initialized";
   errorStringList[6] = "Not implemented error";
   errorStringList[7] = "Invalid set value, element is a keyword";
   errorStringList[8] = "Element is read only";
   errorStringList[9] = "Element is write only";
   errorStringList[10] = "Incorrect Data Type";
}   


/******************************************************************************
**
** Function: fillErrorCodeList()
** Inputs: none 
** Return: none 
**
** Description:
** fillErrorCodeList creates a list array with the SCORM 1.2 error codes
** to create a mapping to the error codes with the errorStrings.
**
******************************************************************************/   
function fillErrorCodeList()
{
   // Fill the list of 1.2 erorr codes
   errorcodeList[0] = "0";
   errorcodeList[1] = "101";
   errorcodeList[2] = "201";
   errorcodeList[3] = "202";
   errorcodeList[4] = "203";
   errorcodeList[5] = "301";
   errorcodeList[6] = "401";
   errorcodeList[7] = "402";
   errorcodeList[8] = "403";
   errorcodeList[9] = "404";
   errorcodeList[10] = "405";
}   

/******************************************************************************
**
** Function: initializeConversionTables()
** Inputs: none 
** Return: none 
**
** Description:
** initializeConversionTables calls all the functions that create the list
** arrays that provide the information for the conversions between SCORM 1.2
** and SCORM 2004 data model elements and errorcodes.
**
******************************************************************************/ 
function initializeConversionTables()
{
   fillKeyList();
   fillValueList();
   fillErrorList();
   fillnewErrorList();
   fillErrorCodeList();
   fillErrorStringList();
}

/******************************************************************************
**
** Function: getNewErrorValue()
** Inputs: SCORM 1.2 error code 
** Return: SCORM 2004 Error code
**
** Description:
** getNewErrorValue accepts a SCORM 1.2 error code and using the the key and 
** value list arrays converts the SCORM 1.2 call into a valid SCORM 2004 error
** code
**
******************************************************************************/  
function getNewErrorValue( error )
{
   var result = error;
   
   for ( i=0; i < errorList.length; i++ )
   {
      if ( errorList[i] == error )
      {
         result = newErrorList[i];
         break;
      }
   }
   return result;
}

/******************************************************************************
**
** Function: getOldErrorValue()
** Inputs:  SCORM 2004 error code 
** Return:  SCORM 1.2 error code
**
** Description: Function to retrieve the 1.2 error code
** getOldErrorValue accepts a SCORM 2004 error code and returns the 
** corresponding SCORM 1.2 error code.
**
******************************************************************************/ 
function getOldErrorValue( error )
{
   var result = error;
   
   for ( i=0; i < newErrorList.length; i++ )
   {
      if ( newErrorList[i] == error )
      {
         result = errorList[i];
         break;
      }
   }
   return result;
}

/******************************************************************************
**
** Function: getErrorString()
** Inputs: error code 
** Return: appropriate SCORM 1.2 error string
**
** Description: Function to retrieve the 1.2 error string
** getErrorString accepts an error code value and returns the coresponding 
** SCORM 1.2 error string.
**
******************************************************************************/    
function getErrorString( error )
{
   var result = error;
   
   for ( i=0; i < errorcodeList.length; i++ )
   {
      if ( errorcodeList[i] == error )
      {
         result = errorStringList[i];
         break;
      }
   }
   return result;
}


// The commented code is an example of one method in using this Conversion
// tool to handle multiple states.  For example the default handling of status 
// variables are handled via JavaScript Global Variables inturn lasting only 
// one learner attempt.  If the developer would like to maintain the status 
// variables over multiple learner sessions (within a leaner attempt) this code 
// could be used.
/**************************************************************************
**
** Function: setcookie()
** Inputs:  name - name of the cookie
**          value - value of the cookie
**          expires - value of the expiration date
** Return:  none
**
** Description:
** Used to create a cookie in order for the player to keep track of
** lesson status.
**
**************************************************************************/     
//function setCookie(name, value, expires, path, domain, secure) 
//{
//    document.cookie = name + "=" + escape(value) + 
//    ((expires == null) ? "" : "; expires=" + expires.toGMTString()) +
//    ((path == null) ? "" : "; path=" + path) +
//    ((domain == null) ? "" : "; domain=" + domain) +
//    ((secure == null) ? "" : "; secure");
//}

/**************************************************************************
**
** Function: getcookie()
** Inputs:  name - name of the cookie
** Return:  The cookie or Null if it doesnt exist
**
** Description:
** Used to retrieve a cookie in order for the player to keep track of
** lesson status.
**
**************************************************************************/ 
//function getCookie(name)
//{
//      var cname = name + "=";               
//      var dc = document.cookie;             
//      if (dc.length > 0) 
//      {              
//       begin = dc.indexOf(cname);       
//       if (begin != -1) 
//      {           
//       begin += cname.length;       
//       end = dc.indexOf(";", begin);
//       if (end == -1) end = dc.length;
//       return unescape(dc.substring(begin, end));
//     } 
//   }
//   return "e";
//}
