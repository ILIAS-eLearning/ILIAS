// Author: Ralph Barthel ILIAS Open Source
// Decompiler options: packimports(3) 
// Source File Name:   Ilias.java

import java.applet.Applet;
import java.io.*;
import java.net.URL;
import java.net.URLConnection;

public class Ilias extends Applet
{
String user_id;
String item_id;
    public Ilias()
    {
    }

    public void init()
    {
	    user_id=getParameter("user_id");
	    item_id=getParameter("item_id");
    }

    public String LMSInitialize(String s)
    {
        return getData("lmsInitialize","");
    }

    public String LMSFinish(String s)
    {
        return getData("lmsFinish",s);
    }

    public String LMSGetValue(String s)
    {
        
        //String s1 = saveData(s);
       // return s1;
         return getData("lmsGetValue",s);
    }

    public String LMSSetValue(String s, String s1)
    {
        return setData("lmsSetValue",s, s1);
    }

    public String LMSCommit(String s)
    {
        return "true";
    }

    public String LMSGetLastError()
    {
        return "";
    }

    public String LMSGetErrorString(String s)
    {
        return "";
    }

    public String LMSGetDiagnostics(String s)
    {
        return "";
    }

    private String getData(String functionName, String elem)
    {
       String urlStr="function="+functionName+"&var="+elem+"&user_id="+user_id+"&item_id="+item_id;
       return callServer(urlStr);
    }

    private String setData(String functionName, String elem, String value)
    {
        String urlStr="function="+functionName+"&var="+elem+"&value="+value+"&user_id="+user_id+"&item_id="+item_id;
       return callServer(urlStr);
    }

    private String callServer(String urlString)
    {
        String s1 = "";
        try
        {
            URL url = new URL(getCodeBase() + "scorm_server.php?"+ urlString);
            URLConnection urlconnection = url.openConnection();
            DataInputStream datainputstream = new DataInputStream(urlconnection.getInputStream());
            int i;
            while((i = datainputstream.read()) >= 0) 
                s1 = s1 + (char)i;

            datainputstream.close();
        }
        catch(IOException ioexception)
        {
	    return "erroe";
           // System.err.println(ioexception);
        }
        return s1;
    }
}
