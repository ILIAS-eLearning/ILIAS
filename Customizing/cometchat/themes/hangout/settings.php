<?php

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }
global $getstylesheet;
global $theme;
$options = array(
    "barPadding"                    => array('textbox','Padding of bar from the end of the window'),
    "showSettingsTab"               => array('choice','Show Settings tab'),
    "showOnlineTab"                 =>array('choice','Show Who`s Online tab'),
    "showModules"                   =>array('choice','Show Modules in Who\'s Online tab')
    );

if (empty($_GET['process'])) {
    include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

    $form = '';

    foreach ($options as $option => $result) {
        $req = '';
        $form .= '<div id="'.$option.'"><div class="titlelong" >'.$result[1].'</div><div class="element">';
        if ($result[0] == 'textbox') {
            $form .= '<input type="text" class="inputbox" name="'.$option.'" value="'.${$option}.'" '.$req.'>';
        }
        if ($result[0] == 'choice') {
            if (${$option} == 1) {
                $form .= '<input type="radio" name="'.$option.'" value="1" checked>Yes <input type="radio" name="'.$option.'" value="0" >No';
            } else {
                $form .= '<input type="radio" name="'.$option.'" value="1" >Yes <input type="radio" name="'.$option.'" value="0" checked>No';
            }
        }
        if ($result[0] == 'dropdown') {
            $form .= '<select  id="'.$option.'Opt" name="'.$option.'">';
            foreach ($result[2] as $opt) {
                if ($opt == ${$option}) {
                    $form .= '<option value="'.$opt.'" selected>'.ucwords($opt);
                } else {
                    $form .= '<option value="'.$opt.'">'.ucwords($opt);
                }
            }
            $form .= '</select>';
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
                                window.resizeTo(($("form").outerWidth(false)+window.outerWidth-$("form").outerWidth(false)), ($('form').outerHeight(false)+window.outerHeight-window.innerHeight));
                            }
                        </script>
                        $getstylesheet
                    </head>
                <body>
                    <form style="height:100%" action="?module=dashboard&action=loadthemetype&type=theme&name=hangout&process=true" onsubmit="return validate();" method="post">
                    <div id="content" style="width:auto">
                            <h2>Settings</h2>
                            <h3>If you are unsure about any value, please skip them</h3>
                            <div>
                                <div id="centernav" style="width:700px">
                                    $form
                                </div>
                            </div>
                            <div style="clear:both;padding:7.5px;"></div>
                            <input type="submit" value="Update Settings" class="button">&nbsp;&nbsp;or <a href="javascript:window.close();">cancel or close</a>
                         <div style="clear:both"></div>
                           </div>
                        </form>
                </body>
                <script>
                    function validate(){
                        if(($("input:[name=chatboxHeight]").val()) < 200) {
                            alert('Height must be greater than 200');
                            return false;
                        } else if(($("input:[name=chatboxWidth]").val()) < 230){
                            alert('Width must be greater than 230');
                            return false;
                        } else {
                            return true;
                        }
                    }
                </script>
                </html>
EOD;
        } else {
            $data = '';
            foreach ($_POST as $option => $value) {
                $data .= '$'.$option.' = \''.$value.'\';'."\t\t\t// ".$options[$option][1]."\r\n";
            }
            if (!empty($data)) {
                configeditor('SETTINGS',$data,0,dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');
            }
            header("Location:?module=dashboard&action=loadthemetype&type=theme&name=hangout");
       }
?>
