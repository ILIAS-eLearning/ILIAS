<?php
$r = new HttpRequest('http://www.google.com/search');

// store Googles cookies in a dedicated file
touch('google.txt');
$r->setOptions(
	array(	'cookiestore'	=> 'google.txt',
	)
);

$r->setQueryData(
	array(	'q'		=> '+"pecl_http" -msg -cvs -list',
			'hl'	=> 'de'
	)
);

// HttpRequest::send() returns an HttpMessage object
// of type HttpMessage::RESPONSE or throws an exception
try {
	print $r->send()->getBody();
} catch (HttpException $e) {
	print $e;
}
?>
