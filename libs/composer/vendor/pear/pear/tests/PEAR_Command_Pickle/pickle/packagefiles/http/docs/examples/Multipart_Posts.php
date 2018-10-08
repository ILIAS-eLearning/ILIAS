<?php
$r = new HttpRequest('http://dev.iworks.at/.print_request.php', HTTP_METH_POST);

// if redirects is set to true, a single redirect is allowed;
// one can set any reasonable count of allowed redirects
$r->setOptions(
	array(	'cookies'	=> array('MyCookie' => 'has a value'),
			'redirect'	=> true,
	)
);

// common form data
$r->setPostFields(
	array(	'name'	=> 'Mike',
			'mail'	=> 'mike@php.net',
	)
);
// add the file to post (form name, file name, file type)
$r->addPostFile('image', 'profile.jpg', 'image/jpeg');

try {
	print $r->send()->getBody();
} catch (HttpException $e) {
	print $e;
}
?>
