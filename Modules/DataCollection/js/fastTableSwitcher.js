/**
 * Created with JetBrains PhpStorm.
 * User: oskar
 * Date: 11/19/12
 * Time: 10:12 AM
 * To change this template use File | Settings | File Templates.
 */
window.onload = function(){
    document.getElementById("table_id").onchange = function(){
        document.getElementById("ilToolbar").submit();
    }
    document.getElementsByName("cmd[doTableSwitch]")[0].style.display = 'none';
}