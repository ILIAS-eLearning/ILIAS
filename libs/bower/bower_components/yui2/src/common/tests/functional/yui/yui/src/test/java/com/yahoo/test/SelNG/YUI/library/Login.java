package com.yahoo.test.SelNG.YUI.library;

import com.yahoo.test.SelNG.framework.core.SelNGBase;
import static com.yahoo.test.SelNG.framework.util.ThreadSafeSeleniumSessionStorage.session;
import org.apache.log4j.Logger;
import static org.testng.Assert.assertTrue;

public class Login extends SelNGBase {

  public static Logger logger = Logger.getLogger(Login.class.getName());
  public static String PAGELOADTIME = "40000";

  public Login() throws Exception {
    super();
    // TODO Auto-generated constructor stub
  }

  public static void SignIn(String URL, String username, String password, String VerifyMessage) throws Exception {
    logger.info("Invoked SignIn method : SignIn");
    session().open(URL);
    session().waitForPageToLoad("30000");
    session().type(getPageElement("Login", "username_edt"), username);
    session().type(getPageElement("Login", "password_edt"), password);
    session().click(getPageElement("Login", "SignIn_Btn"));
    session().waitForPageToLoad("40000");
    assertTrue(session().isTextPresent(VerifyMessage));
  }


  public static void SignOut() throws Exception {
    session().click(getPageElement("Header", "SignOut_Btn"));
    session().waitForPageToLoad("40000");
  }

}
