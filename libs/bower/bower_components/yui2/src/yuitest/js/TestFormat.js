YAHOO.namespace("tool.TestFormat");

(function(){

    /**
     * Returns test results formatted as a JSON string. Requires JSON utility.
     * @param {Object} result The results object created by TestRunner.
     * @return {String} An XML-formatted string of results.
     * @namespace YAHOO.tool.TestFormat
     * @method JSON
     * @static
     */
    YAHOO.tool.TestFormat.JSON = function(results) {
        return YAHOO.lang.JSON.stringify(results);
    };

    /* (intentionally not documented)
     * Simple escape function for XML attribute values.
     * @param {String} text The text to escape.
     * @return {String} The escaped text.
     */
    function xmlEscape(text){
        return text.replace(/["'<>&]/g, function(c){
            switch(c){
                case "<":   return "&lt;";
                case ">":   return "&gt;";
                case "\"":  return "&quot;";
                case "'":   return "&apos;";
                case "&":   return "&amp;";
            }
        });
    } 

    /**
     * Returns test results formatted as an XML string.
     * @param {Object} result The results object created by TestRunner.
     * @return {String} An XML-formatted string of results.
     * @namespace YAHOO.tool.TestFormat
     * @method XML
     * @static
     */
    YAHOO.tool.TestFormat.XML = function(results) {

        function serializeToXML(results){
            var l   = YAHOO.lang,
                xml = "<" + results.type + " name=\"" + xmlEscape(results.name) + "\"";
            
            if (l.isNumber(results.duration)){
                xml += " duration=\"" + results.duration + "\"";
            }
            
            if (results.type == "test"){
                xml += " result=\"" + results.result + "\" message=\"" + xmlEscape(results.message) + "\">";
            } else {
                xml += " passed=\"" + results.passed + "\" failed=\"" + results.failed + "\" ignored=\"" + results.ignored + "\" total=\"" + results.total + "\">";
                for (var prop in results) {
                    if (l.hasOwnProperty(results, prop) && l.isObject(results[prop]) && !l.isArray(results[prop])){
                        xml += serializeToXML(results[prop]);
                    }
                }        
            }

            xml += "</" + results.type + ">";
            
            return xml;    
        }

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" + serializeToXML(results);

    };


    /**
     * Returns test results formatted in JUnit XML format.
     * @param {Object} result The results object created by TestRunner.
     * @return {String} An XML-formatted string of results.
     * @namespace YAHOO.tool.TestFormat
     * @method JUnitXML
     * @static
     */
    YAHOO.tool.TestFormat.JUnitXML = function(results) {


        function serializeToJUnitXML(results){
            var l   = YAHOO.lang,
                xml = "",
                prop;
                
            switch (results.type){
                //equivalent to testcase in JUnit
                case "test":
                    if (results.result != "ignore"){
                        xml = "<testcase name=\"" + xmlEscape(results.name) + "\">";
                        if (results.result == "fail"){
                            xml += "<failure message=\"" + xmlEscape(results.message) + "\"><![CDATA[" + results.message + "]]></failure>";
                        }
                        xml+= "</testcase>";
                    }
                    break;
                    
                //equivalent to testsuite in JUnit
                case "testcase":
                
                    xml = "<testsuite name=\"" + xmlEscape(results.name) + "\" tests=\"" + results.total + "\" failures=\"" + results.failed + "\">";
                
                    for (prop in results) {
                        if (l.hasOwnProperty(results, prop) && l.isObject(results[prop]) && !l.isArray(results[prop])){
                            xml += serializeToJUnitXML(results[prop]);
                        }
                    }              
                    
                    xml += "</testsuite>";
                    break;
                
                case "testsuite":
                    for (prop in results) {
                        if (l.hasOwnProperty(results, prop) && l.isObject(results[prop]) && !l.isArray(results[prop])){
                            xml += serializeToJUnitXML(results[prop]);
                        }
                    } 

                    //skip output - no JUnit equivalent                    
                    break;
                    
                case "report":
                
                    xml = "<testsuites>";
                
                    for (prop in results) {
                        if (l.hasOwnProperty(results, prop) && l.isObject(results[prop]) && !l.isArray(results[prop])){
                            xml += serializeToJUnitXML(results[prop]);
                        }
                    }              
                    
                    xml += "</testsuites>";            
                
                //no default
            }
            
            return xml;
     
        }

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" + serializeToJUnitXML(results);
    };
    
    /**
     * Returns test results formatted in TAP format.
     * For more information, see <a href="http://testanything.org/">Test Anything Protocol</a>.
     * @param {Object} result The results object created by TestRunner.
     * @return {String} A TAP-formatted string of results.
     * @namespace YAHOO.tool.TestFormat
     * @method TAP
     * @static
     */
    YAHOO.tool.TestFormat.TAP = function(results) {
    
        var currentTestNum = 1;

        function serializeToTAP(results){
            var l   = YAHOO.lang,
                text = "";
                
            switch (results.type){

                case "test":
                    if (results.result != "ignore"){

                        text = "ok " + (currentTestNum++) + " - " + results.name;
                        
                        if (results.result == "fail"){
                            text = "not " + text + " - " + results.message;
                        }
                        
                        text += "\n";
                    } else {
                        text = "#Ignored test " + results.name + "\n";
                    }
                    break;
                    
                case "testcase":
                
                    text = "#Begin testcase " + results.name + "(" + results.failed + " failed of " + results.total + ")\n";
                                    
                                    
                    for (prop in results) {
                        if (l.hasOwnProperty(results, prop) && l.isObject(results[prop]) && !l.isArray(results[prop])){
                            text += serializeToTAP(results[prop]);
                        }
                    }              
                                                        
                    text += "#End testcase " + results.name + "\n";
                    
                    
                    break;
                
                case "testsuite":

                    text = "#Begin testsuite " + results.name + "(" + results.failed + " failed of " + results.total + ")\n";                
                
                    for (prop in results) {
                        if (l.hasOwnProperty(results, prop) && l.isObject(results[prop]) && !l.isArray(results[prop])){
                            text += serializeToTAP(results[prop]);
                        }
                    }   

                    text += "#End testsuite " + results.name + "\n";
                    break;

                case "report":
                
                    for (prop in results) {
                        if (l.hasOwnProperty(results, prop) && l.isObject(results[prop]) && !l.isArray(results[prop])){
                            text += serializeToTAP(results[prop]);
                        }
                    }             
                    
                //no default
            }
            
            return text;
     
        }

        return "1.." + results.total + "\n" + serializeToTAP(results);
    };   

})();