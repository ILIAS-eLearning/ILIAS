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

global $access_token;

function translate_gettoken () {

	global $bingClientID;
	global $bingClientSecret;
	global $access_token;

	if (!empty($access_token)) {
		return $access_token;
	}

	if (empty($bingClientID) || empty($bingClientSecret)) {
		return;
	}

	if(!function_exists('curl_version')){
		return;
	}

	$url = 'https://datamarket.accesscontrol.windows.net/v2/OAuth2-13';

	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_POSTFIELDS,'client_id='.urlencode($bingClientID).'&'.'client_secret='.urlencode($bingClientSecret).'&'.'scope='.urlencode('http://api.microsofttranslator.com').'&'.'grant_type='.'client_credentials');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$result = curl_exec($ch);
	curl_close($ch);
	$data = json_decode($result);
	$access_token = $data->access_token;

	return $access_token;
}

function removeBOM($str = "") {
	if (substr($str, 0, 3) == pack("CCC",0xef,0xbb,0xbf)) {
		$str=substr($str, 3);
	}
	return $str;
}


function translate_text ($text, $from = 'en', $to = 'en') {

	global $bingClientID;
	global $bingClientSecret;

	try {
		$token = translate_gettoken();

		if (empty($token)) {
			return false;
		}

		if(!function_exists('curl_version')){
			return false;
		}

		$url = 'http://api.microsofttranslator.com/v2/Ajax.svc/Detect?text='.urlencode($text).'&appId=';

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $token));
		$result = curl_exec($ch);
		curl_close($ch);

		$language = removeBOM(str_replace('"', '', $result));

		if ($language == $to || empty($to) || empty($language)){
			return false;
		}

		$url = 'http://api.microsofttranslator.com/v2/Ajax.svc/GetTranslations?text='.urlencode($text).'&appId=&from='.$language.'&to='.$to.'&maxTranslations=1';

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $token));
		$result = curl_exec($ch);
		curl_close($ch);

		$result = json_decode(removeBOM($result));

		return $result->Translations[0]->TranslatedText;

	} catch (Exception $e) {
		return false;
	}

}


function translate_languages () {

	global $bingClientID;
	global $bingClientSecret;

	try {
		$token = translate_gettoken();

		if (empty($token)) {
			return false;
		}

		if(!function_exists('curl_version')){
			return false;
		}

		$url = 'http://api.microsofttranslator.com/v2/Ajax.svc/GetLanguagesForTranslate?appId=';

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $token));
		$result = curl_exec($ch);
		curl_close($ch);

		$languages = json_decode(removeBOM($result));

		$languagestring = '["'.implode('","',$languages).'"]';

		$url = 'http://api.microsofttranslator.com/v2/Ajax.svc/GetLanguageNames?locale=en&appId=&languageCodes='.urlencode($languagestring);

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $token));
		$result = curl_exec($ch);
		curl_close($ch);

		$result = json_decode(removeBOM($result));

		$return = array();

		foreach ($result as $id => $value) {
			$return[$languages[$id]] = $value;
		}

		return $return;

	} catch (Exception $e) {
		return false;
	}

}