<?php

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }
global $getstylesheet;
global $theme;
$options = array(
    "chatboxHeight"                 => array('textbox','Set the Height of the Chat (Minimum Height can be 350)'),
    "chatboxWidth"                 => array('textbox','Set the Width of the Chat (Minimum Width can be 300)'),
    );

if (empty($_GET['process'])) {
    include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

    $form = '';

    foreach ($options as $option => $result) {
        $req = '';
        if($option == 'chatboxHeight' OR $option == 'chatboxWidth') {
            $req = 'required';
        }
        $form .= '<div id="'.$option.'"><div class="titlelong" >'.$result[1].'</div><div class="element">';
        if ($result[0] == 'textbox') {
            $form .= '<input type="text" class="inputbox" name="'.$option.'" value="'.${$option}.'" '.$req.'>';
        }
        $form .= '</div><div style="clear:both;padding:7px;"></div></div>';
    }

    echo <<<EOD
                <!DOCTYPE html>
                <html>
                    <head>
                        <script type="text/javascript" src="../js.php?admin=1"></script>
                        <script type="text/javascript" language="javascript">
                            $(function() {
                                setTimeout(function(){
                                    resizeWindow();
                                },200);
                            });

                            function resizeWindow() {
                                window.resizeTo(($("form").outerWidth()+window.outerWidth-$("form").outerWidth()), ($('form').outerHeight()+window.outerHeight-window.innerHeight));
                            }
                        </script>
                        $getstylesheet
                    </head>
                <body>
                    <form style="height:100%" action="?module=dashboard&action=loadthemetype&type=theme&name=synergy&process=true" onsubmit="return validate();" method="post">
                    <div id="content" style="width:auto">
                            <h2>Generate Embed Code</h2>
                            <h3>If you are unsure about any value, please proceed with default value</h3>
                            <div>
                                <div id="centernav" style="width:700px">
                                    $form
                                </div>
                            </div>
                            <div style="clear:both;padding:7.5px;"></div>
                            <input type="submit" value="Generate Code" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>
                         <div style="clear:both"></div>
                           </div>
                        </form>
                </body>
                <script>
                    function validate(){
                        var cbHeight = parseInt($("input:[name=chatboxHeight]").val());
                        $("input:[name=chatboxHeight]").val(cbHeight)
                        var cbWidth = parseInt($("input:[name=chatboxWidth]").val());
                        $("input:[name=chatboxWidth]").val(cbWidth);

                        if(cbHeight < 350) {
                            alert('Height must be greater than 350');
                            return false;
                        } else if(cbWidth < 300){
                            alert('Width must be greater than 300');
                            return false;
                        } else {
                            return true;
                        }
                    }
                </script>
                </html>
EOD;
        } else {
        	include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'config.php');
            $embed_code = '&lt;div id="cometchat_embed_synergy_container" style="width:'.$_POST['chatboxWidth'].'px;height:'.$_POST['chatboxHeight'].'px;" >&lt;/div&gt;&lt;script src="'.BASE_URL.'js.php?type=core&name=embedcode" type="text/javascript"&gt;&lt;/script&gt;&lt;script&gt;var iframeObj = {};iframeObj.module="synergy";iframeObj.style="min-height:350px;min-width:300px;";iframeObj.src="'.BASE_URL.'cometchat_popout.php"; if(typeof(addEmbedIframe)=="function"){addEmbedIframe(iframeObj);}&lt;/script&gt;';
            echo <<<EOD
                <!DOCTYPE html>
                <html>
                    <head>
                        <script type="text/javascript" src="../js.php?admin=1"></script>
                        <script type="text/javascript" language="javascript">
                            $(function() {
                                setTimeout(function(){
                                    resizeWindow();
                                },200);
                            });

                            function resizeWindow() {
                                window.resizeTo((520), (190+window.outerHeight-window.innerHeight));
                            }
                        </script>
                        <style>textarea { border:1px solid #ccc; color: #333; font-family:verdana; font-size:12px; }</style>
                    </head>
                <body style='overflow:hidden'>
                    <textarea readonly="" style="width:500px;height:170px">{$embed_code}</textarea>
                </body>
                </html>
EOD;
       }
?>
