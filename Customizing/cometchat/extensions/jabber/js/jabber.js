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

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.php");

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");

if (file_exists (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
	include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
}

?>

function login_gtalk(session,username) {
	var currenttime = new Date();
	currenttime = parseInt(currenttime.getTime()/1000);

	$.getJSON("<?php echo $cometchatServer;?>j?json_callback=?", {action:'login', username: username, password: session, session_key: session, server: '<?php echo $jabberServer;?>', port: '<?php echo $jabberPort;?>', id: '<?php echo $gtalkAppId;?>', key: '<?php echo $gtalkSecretKey;?>'} , function(data){

		if (data[0].error == '0') {
			$.cookie('cc_jabber','true',{ path: '/' });
			$.cookie('cc_jabber_id',data[0].msg,{ path: '/' });
			$.cookie('cc_jabber_type','gtalk',{ path: '/' });
			$('.container_body_2').remove();
			$('#gtalk_box').html('<span><?php echo $jabber_language[7];?></span>');

			setTimeout(function() {
				try {
					if(before == "parent") {
						parent.jqcc.ccjabber.process();
						parent.closeCCPopup('jabber');
					} else {
						parentSandboxBridge.jqcc.ccjabber.process();
						parentSandboxBridge.closeCCPopup('jabber');
					}
				} catch (e) {
					crossDomain();
				}
			}, 4000);
		} else {
			alert('<?php echo $jabber_language[9];?>');
			$('#gtalk').css('display','block');
			$('#loader').css('display','none');
		}
	});
	return false;
}

/*	$(function() {
//	$.cookie('cc_jabber','false',{ path: '/' });
//	$.getJSON("<?php echo $cometchatServer;?>j?json_callback=?", {'action':'logout'});
});*/

function crossDomain() {
	var ts = Math.round((new Date()).getTime() / 1000);
	var baseUrl = '<?php echo BASE_URL; ?>';
	baseUrl = (baseUrl.indexOf('http://') >= 0 || baseUrl.indexOf('https://') >= 0)? '':baseUrl+'/extensions/jabber';
	location.href= '//'+domain+baseUrl+'/chat.htm?ts='+ts+'&jabber='+$.cookie('cc_jabber')+'&jabber_type='+$.cookie('cc_jabber_type')+'&jabber_id='+$.cookie('cc_jabber_id');
}

// Copyright (c) 2006 Klaus Hartl (stilbuero.de)
// http://www.opensource.org/licenses/mit-license.php

$.cookie=function(a,b,c){if(typeof b!='undefined'){c=c||{};if(b===null){b='';c.expires=-1}var d='';if(c.expires&&(typeof c.expires=='number'||c.expires.toUTCString)){var e;if(typeof c.expires=='number'){e=new Date();e.setTime(e.getTime()+(c.expires*24*60*60*1000))}else{e=c.expires}d='; expires='+e.toUTCString()}var f=c.path?'; path='+(c.path):'';var g=c.domain?'; domain='+(c.domain):'';var h=c.secure?'; secure':'';document.cookie=[a,'=',encodeURIComponent(b),d,f,g,h].join('')}else{var j=null;if(document.cookie&&document.cookie!=''){var k=document.cookie.split(';');for(var i=0;i<k.length;i++){var l=$.trim(k[i]);if(l.substring(0,a.length+1)==(a+'=')){j=decodeURIComponent(l.substring(a.length+1));break}}}return j}};
