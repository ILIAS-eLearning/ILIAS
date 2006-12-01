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
	String session_id;
	String code_base;

  public Ilias() {}

 	public void init()
  {
  /*  user_id = getParameter("user_id");
	  item_id = getParameter("item_id");
		session_id = getParameter("session_id");
		code_base = getParameter("code_base");						
		*/
  }

	public String SetParameter(String user_id, String item_id, String session_id, String code_base)
	{
		this.user_id=user_id;
		this.item_id=item_id;
		this.session_id=session_id;
		this.code_base=code_base;
		
		return "true";
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
    return callServer("function="+functionName+"&var="+elem+"&user_id="+user_id+"&item_id="+item_id);
  }

  private String setData(String functionName, String elem, String value)
  {   	
    return callServer("function="+functionName+"&var="+elem+"&value="+value+"&user_id="+user_id+"&item_id="+item_id);
  }

  private String callServer(String urlString)
  {
   	String s1 = "";
    try
    {
    	//URL _url = new URL(getCodeBase() + "scorm_server.php?PHPSESSID="+session_id+"&"+urlString);
    	URL _url = new URL(code_base+"/scorm_server.php?PHPSESSID="+session_id+"&"+urlString);        		
    System.out.println(_url);
			URLConnection _urlconnection=_url.openConnection();	                        
		System.out.println(_urlconnection);
      DataInputStream _dataInputStream = new DataInputStream(_urlconnection.getInputStream());            	
		System.out.println(_dataInputStream);
      int i;						
			while((i = _dataInputStream.read()) >= 0) 
      	s1 = s1 + (char)i;
						
			_dataInputStream.close();
		}
    catch(Exception excptn)
    {        	
			excptn.printStackTrace();
	   	return excptn.getLocalizedMessage();
    }
    return s1;                
  }
  
  public String dummy()
  {
  	return "dummy";
  }

}

