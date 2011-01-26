javac -source 1.3 -target 1.3 IliasApiAdapterApplet.java -classpath gnu.jar:PfPLMS-API-adapter-core.jar
rm IliasApiAdapterApplet.jar
jar cfv IliasApiAdapterApplet.jar IliasApiAdapterApplet.class IliasApiAdapterApplet\$1.class IliasApiAdapterApplet\$2.class
rm IliasApiAdapterApplet.class IliasApiAdapterApplet\$[12].class
