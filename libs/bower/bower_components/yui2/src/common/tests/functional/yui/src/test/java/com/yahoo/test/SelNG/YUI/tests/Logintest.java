package com.yahoo.test.SelNG.YUI.tests;

import com.yahoo.test.SelNG.YUI.tests.CommonTest;
import com.yahoo.test.SelNG.YUI.library.Login;
import com.yahoo.test.SelNG.framework.util.SelNGRetryAnalyzer;
import org.apache.log4j.Logger;
import static org.testng.Assert.fail;
import org.testng.annotations.Test;
import com.yahoo.test.SelNG.framework.commonlibraries.Yahoologin;
import com.yahoo.test.SelNG.framework.util.DataIterator;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;

import java.util.Iterator;


public class Logintest extends CommonTest {
  public static Logger logger = Logger.getLogger(Logintest.class.getName());


  @Test(groups = {"demojiojo"}, retryAnalyzer = SelNGRetryAnalyzer.class)
  public void Verifylogin() {
    logger.info("Invoked Verifylogin Method");
    try {
      String username = getTestInputData("Myproject1Tests", "username");
      String password = getTestInputData("Myproject1Tests", "password");
      Login.SignIn(config.getString("BASEURL"), username, password, "Hi,");

    } catch (Throwable t) {
      t.printStackTrace(System.out);
      logger.error(t);
      fail();
    }
  }
}
