
function LMSGetValue(dataModelElement){
 //alert("get");
 return window.document.APIAdapter.LMSGetValue(dataModelElement);
}



function LMSSetValue(dataModelElement,elemValue){



return window.document.APIAdapter.LMSSetValue(dataModelElement,elemValue);

//alert(retval);

//return retval;

 //return obj.LMSSetValue(dataModelElement,elemValue);

}



function LMSInitialize(inString){


 return window.document.APIAdapter.LMSInitialize(inString);

}



function LMSFinish(inString){


//alert("finish");
 return window.document.APIAdapter.LMSFinish(inString);

}



function LMSCommit(inString){

 return window.document.APIAdapter.LMSCommit(inString);

}



function LMSGetLastError(){

 return window.document.APIAdapter.LMSGetLastError();

}



function LMSGetErrorString(errorNumber){

 return window.document.APIAdapter.LMSGetErrorString(errorNumber);

}



function LMSGetDiagnostics(inString){

return window.document.APIAdapter.LMSGetDiagnostics(inString);

}