function init(){
  obj=window.document.APIAdapter;
  
}

function LMSGetValue(dataModelElement){
 return obj.LMSGetValue(dataModelElement);
}

function LMSSetValue(dataModelElement,elemValue){
 return obj.LMSSetValue(dataModelElement,elemValue);
}

function LMSInitialize(inString){
 return obj.LMSInitialize(inString);
}

function LMSFinish(inString){

 return obj.LMSFinish(inString);
}

function LMSCommit(inString){
 return obj.LMSCommit(inString);
}

function LMSGetLastError(){
 return obj.LMSGetLastError();
}

function LMSGetErrorString(errorNumber){
 return obj.LMSGetErrorString(errorNumber);
}

function LMSGetDiagnostics(inString){
 return obj.LMSGetDiagnostics(inString);
}