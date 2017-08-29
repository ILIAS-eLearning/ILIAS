<?PHP

$shell['title1'] = "jQuery outside events";
$shell['link1']  = "http://benalman.com/projects/jquery-outside-events-plugin/";

ob_start();
?>
  <a href="http://benalman.com/projects/jquery-outside-events-plugin/">Project Home</a>,
  <a href="http://benalman.com/code/projects/jquery-outside-events/docs/">Documentation</a>,
  <a href="http://github.com/cowboy/jquery-outside-events/">Source</a>
<?
$shell['h3'] = ob_get_contents();
ob_end_clean();

$shell['jquery'] = 'jquery-1.4.2.js';

$shell['shBrush'] = array( 'JScript' );

?>
