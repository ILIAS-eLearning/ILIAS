<?php

/*

CometChat
Copyright (c) 2014 Inscripts

CometChat ('the Software') is a copyrighted work of authorship. Inscripts 
retains ownership of the Software and any copies of it, regardless of the 
form in which the copies may exist. This license is not a sale of the 
original Software or any copies.

By installing and using CometChat on your server, you agree to the following
terms and conditions. Such agreement is either on your own behalf or on behalf
of any corporate entity which employs you or which you represent
('Corporate Licensee'). In this Agreement, 'you' includes both the reader
and any Corporate Licensee and 'Inscripts' means Inscripts (I) Private Limited:

CometChat license grants you the right to run one instance (a single installation)
of the Software on one web server and one web site for each license purchased.
Each license may power one instance of the Software on one domain. For each 
installed instance of the Software, a separate license is required. 
The Software is licensed only to you. You may not rent, lease, sublicense, sell,
assign, pledge, transfer or otherwise dispose of the Software in any form, on
a temporary or permanent basis, without the prior written consent of Inscripts. 

The license is effective until terminated. You may terminate it
at any time by uninstalling the Software and destroying any copies in any form. 

The Software source code may be altered (at your risk) 

All Software copyright notices within the scripts must remain unchanged (and visible). 

The Software may not be used for anything that would represent or is associated
with an Intellectual Property violation, including, but not limited to, 
engaging in any activity that infringes or misappropriates the intellectual property
rights of others, including copyrights, trademarks, service marks, trade secrets, 
software piracy, and patents held by individuals, corporations, or other entities. 

If any of the terms of this Agreement are violated, Inscripts reserves the right 
to revoke the Software license at any time. 

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."modules.php");

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");

require_once("twitteroauth/twitteroauth.php"); //Path to twitteroauth library

$connection = new TwitterOAuth($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
function auto_link_text($text) {
   $pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
   $callback = create_function('$matches', '
       $url       = array_shift($matches);
       
       return sprintf(\'<a target="_blank" href="%s">%s</a>\', $url, $url);
   ');

   return preg_replace_callback($pattern, $callback, $text);
}
$followers = $connection->get("https://api.twitter.com/1.1/followers/list.json?cursor=-1&screen_name=".$twitteruser."&count=48");
$followersHTML = '';
$tweetsHTML = '';

if(isset($followers->errors)) {
	echo "<div style='background: white;'>Please configure this module using CometChat Administration Panel.</div>"; exit;
} else {
	foreach ($followers->users as $follower) {
		$followersHTML .= '<a target="_blank" href="http://www.twitter.com/'.$follower->screen_name.'"><img width=24 height=24 src="'.str_replace('normal', 'mini', $follower->profile_image_url).'" alt="'.$follower->name.'" title="'.$follower->name.'"></a>';
	}

	$tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitteruser."&count=".$notweets);
	
	foreach ($tweets as $tweet) {
		$tweetsHTML .= '<li class="tweet">'.auto_link_text($tweet->text).'<br /><small class="chattime" timestamp="'.strtotime($tweet->created_at).'"></small></li>';

	}
}

$extrajs = '';
if ($sleekScroller == 1) {
	$extrajs = '<script>jqcc=jQuery;</script><script src="../../js.php?type=core&name=scroll"></script>';
}

echo <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="pragma" content="no-cache">
		<meta http-equiv="expires" content="-1">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8"/> 
		<link type="text/css" rel="stylesheet" media="all" href="../../css.php?type=module&name=twitter" /> 
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
		<script src="../../js.php?type=module&name=twitter"></script>
		{$extrajs}
		<script>
		$(function() {
			if (jQuery().slimScroll) {
				$('#tweets').slimScroll({height: '310px',width: '323px',allowPageScroll: false});
				$("#tweets").css("height","290px");			
				$('#tweets_wrapper').css("height","310px");
			}
		});

		</script>
	</head>
	<body>
		<div style="width:100%;margin:0 auto;margin-top: 0px;height: 100%;">
			<div class="container">
				<div class="followme">
					<a target="_blank" href="http://www.twitter.com/{$twitteruser}"><img src="themes/{$theme}/images/follow.png"></a><br>
					<div id="followers">{$followersHTML}</div>
				</div>
				<div id="tweets_wrapper" style="width: 324px;height:300px;overflow:auto">
					<ul id="tweets" style="width: auto;">
						{$tweetsHTML}
					</ul>
				</div>
				<div style="clear:both"></div>
			</div>
		</div>
	</body>
</html>
EOD;
?>