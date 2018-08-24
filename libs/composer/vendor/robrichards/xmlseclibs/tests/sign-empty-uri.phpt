--TEST--
Signature Forcing Empty URI
--FILE--
<?php
require(dirname(__FILE__) . '/../xmlseclibs.php');

if (file_exists(dirname(__FILE__) . '/sign-empty-uri.xml')) {
    unlink(dirname(__FILE__) . '/sign-empty-uri.xml');
}

$doc = new DOMDocument();
$doc->load(dirname(__FILE__) . '/basic-doc.xml');

$objDSig = new XMLSecurityDSig();

$objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

$objDSig->addReference($doc, XMLSecurityDSig::SHA1, array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'), array('force_uri' => true));

$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type'=>'private'));
/* load private key */
$objKey->loadKey(dirname(__FILE__) . '/privkey.pem', TRUE);

/* if key has Passphrase, set it using $objKey->passphrase = <passphrase> " */


$objDSig->sign($objKey);

/* Add associated public key */
$objDSig->add509Cert(file_get_contents(dirname(__FILE__) . '/mycert.pem'));

$objDSig->appendSignature($doc->documentElement);
$doc->save(dirname(__FILE__) . '/sign-empty-uri.xml');

$sign_output = file_get_contents(dirname(__FILE__) . '/sign-empty-uri.xml');
$sign_output_def = file_get_contents(dirname(__FILE__) . '/sign-empty-uri.res');
if ($sign_output != $sign_output_def) {
	echo "NOT THE SAME";
}
echo "DONE";
?>
--CLEAN--
<?php
unlink(dirname(__FILE__) . '/sign-empty-uri.xml');
?>
--EXPECTF--
DONE
