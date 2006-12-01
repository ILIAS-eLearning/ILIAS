
del keystore.com
del Ilias.jar
del sIlias.jar
del Ilias.class

c:\jdk1.3.1_02\bin\javac Ilias.java
c:\jdk1.3.1_02\bin\keytool -genkey -alias alias -keypass password -keystore keystore.com -storepass password
c:\jdk1.3.1_02\bin\jar cf0 Ilias.jar Ilias.class
c:\jdk1.3.1_02\bin\jarsigner -keystore keystore.com -storepass password -keypass password -signedjar sIlias.jar Ilias.jar alias

