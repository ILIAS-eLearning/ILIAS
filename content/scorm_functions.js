function init(){

  API=this.frames[0].window.document.APIAdapter;
  
}

function LMSGetValue(dataModelElement){

 return API.LMSGetValue(dataModelElement);
}

function LMSSetValue(dataModelElement,elemValue){
alert("called");
retval=API.LMSSetValue(dataModelElement,elemValue);
alert(retval);
return retval;
 //return obj.LMSSetValue(dataModelElement,elemValue);
}

function LMSInitialize(inString){

 return API.LMSInitialize(inString);
}

function LMSFinish(inString){

 return API.LMSFinish(inString);
}

function LMSCommit(inString){
 return API.LMSCommit(inString);
}

function LMSGetLastError(){
 return API.LMSGetLastError();
}

function LMSGetErrorString(errorNumber){
 return API.LMSGetErrorString(errorNumber);
}

function LMSGetDiagnostics(inString){
 return API.LMSGetDiagnostics(inString);
}