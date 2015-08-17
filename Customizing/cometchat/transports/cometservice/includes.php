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
 $callbackfn = '';
 if(!empty($_GET['callbackfn']) && $_GET['callbackfn'] == 'desktop'){
    $desktopmode = 1;
 }else{
    $desktopmode = 0;
 }
 include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'comet.js');
 ?>
var cometid = '';
var comet = COMET.init({
    'subscribe_key': '<?php echo KEY_B;?>',
    'desktop': '<?php echo $desktopmode;?>',
    'baseurl': '<?php echo BASE_URL;?>',
    'ssl': (window.location.protocol=='https:') ? true : false
});
function cometcall_function(id, td, calleeAPI){
    var timetoken = jqcc.cookie('<?php echo $cookiePrefix; ?>timetoken')||0;
    cometid = id;
    comet.subscribe({
        channel: id,
        timetoken: timetoken
    }, function(incoming){
        <?php
         if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'realtimetranslate'.DIRECTORY_SEPARATOR.'config.php')) {
             include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'realtimetranslate'.DIRECTORY_SEPARATOR.'config.php');
             if($useGoogle == 1 && !empty($googleKey)){
         ?>
                if(jqcc.cookie('<?php echo $cookiePrefix;?>lang')){
                    var lang = jqcc.cookie('<?php echo $cookiePrefix;?>lang');
                    jqcc.ajax({
                        url: "https://www.googleapis.com/language/translate/v2?key=<?php echo $googleKey;?>&callback=?",
                        data: {q: incoming.message, target: lang},
                        dataType: 'jsonp',
                        success: function(data){
                            if(typeof(data.data)!="undefined"){
                                incoming.message = data.data.translations[0].translatedText+' <span class="untranslatedtext">('+incoming.message+')</span>';
                            }
                            if(typeof (jqcc[calleeAPI].addMessages)=="function"){
                                jqcc[calleeAPI].addMessages([{ "id": incoming.id, "from": incoming.from, "message": incoming.message, "self": incoming.self, "old": 0, "selfadded": 0, "sent": parseInt(incoming.sent)+td}]);
                            }
                        }
                    });
                }else{
                    if(typeof (jqcc[calleeAPI].addMessages)=="function"){
                        jqcc[calleeAPI].addMessages([{ "id": incoming.id, "from": incoming.from, "message": incoming.message, "self": incoming.self, "old": 0, "selfadded": 0, "sent": parseInt(incoming.sent)+td}]);
                    }
                }
                <?php
                 } else {
                ?>
                    if(typeof (jqcc[calleeAPI].addMessages)=="function"){
                        jqcc[calleeAPI].addMessages([{ "id": incoming.id, "from": incoming.from, "message": incoming.message, "self": incoming.self, "old": 0, "selfadded": 0, "sent": parseInt(incoming.sent)+td}]);
                    }
                <?php
                 }
         } else { ?>
            if(typeof (jqcc[calleeAPI].addMessages)=="function"){
                jqcc[calleeAPI].addMessages([{ "id": incoming.id, "from": incoming.from, "message": incoming.message, "self": incoming.self, "old": 0, "selfadded": 0, "sent": parseInt(incoming.sent)+td}]);
            }
        <?php
        }
        ?>
    });
}
function chatroomcall_function(id,userid){
    comet.subscribe({
        channel: id,
        timetoken: 0
    }, function(incoming){
        <?php
         if(file_exists(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'realtimetranslate'.DIRECTORY_SEPARATOR.'config.php')) {
         include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'realtimetranslate'.DIRECTORY_SEPARATOR.'config.php');
         if($useGoogle == 1 && !empty($googleKey)){
         ?>
        if(jqcc.cookie('<?php echo $cookiePrefix;?>lang') && incoming.fromid != userid){
            var lang = jqcc.cookie('<?php echo $cookiePrefix;?>lang');
            jqcc.ajax({
                url: "https://www.googleapis.com/language/translate/v2?key=<?php echo $googleKey;?>&callback=?",
                data: {q: incoming.message, target: lang},
                dataType: 'jsonp',
                success: function(data){
                    if(typeof(data.data)!="undefined"){
                        incoming.message = data.data.translations[0].translatedText+' <span class="untranslatedtext">('+incoming.message+')</span>';
                    }
                    $.cometchat.setChatroomVars('newMessages', $.cometchat.getChatroomVars('newMessages')+1);
                    $[$.cometchat.getChatroomVars('calleeAPI')].addChatroomMessage(incoming.fromid, incoming.message, parseInt(incoming.sent), '1', parseInt(incoming.sent), incoming.from);
                }
            });
        }else{
            $.cometchat.setChatroomVars('newMessages', $.cometchat.getChatroomVars('newMessages')+1);
            $[$.cometchat.getChatroomVars('calleeAPI')].addChatroomMessage(incoming.fromid, incoming.message, parseInt(incoming.sent), '1', parseInt(incoming.sent), incoming.from);
        }
        <?php
         } else { ?>
            $.cometchat.setChatroomVars('newMessages', $.cometchat.getChatroomVars('newMessages')+1);
            $[$.cometchat.getChatroomVars('calleeAPI')].addChatroomMessage(incoming.fromid, incoming.message, parseInt(incoming.sent), '1', parseInt(incoming.sent), incoming.from);
        <?php
         }
         } else { ?>
            $.cometchat.setChatroomVars('newMessages', $.cometchat.getChatroomVars('newMessages')+1);
            $[$.cometchat.getChatroomVars('calleeAPI')].addChatroomMessage(incoming.fromid, incoming.message, parseInt(incoming.sent), '1', parseInt(incoming.sent), incoming.from);
        <?php } ?>
    });
}
function cometuncall_function(id){
    comet.unsubscribe({channel: id});
}