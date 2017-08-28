<?PHP

include "../index.php";

$shell['title3'] = "dblclickoutside";

$shell['h2'] = 'Why double-click something, when you can double-click everything else?';

// ========================================================================== //
// SCRIPT
// ========================================================================== //

ob_start();
?>
$(function(){
  
  // Elements on which to bind the event.
  var elems = $('#test, #test div, #test .bind-me');
  
  // Clear any previous highlights and text.
  $(document)
    .bind( 'dblclick', function(event){
      elems
        .removeClass( 'event-outside' )
        .children( '.event-target' )
          .text( ' ' );
    })
    .trigger( 'dblclick' );
  
  // Bind the 'dblclickoutside' event to each test element.
  elems.bind( 'dblclickoutside', function(event){
    var elem = $(this),
      target = $(event.target),
      
      // Update the text to reference the event.target element.
      text = 'Double-clicked: ' + target[0].tagName.toLowerCase()
        + ( target.attr('id') ? '#' + target.attr('id')
          : target.attr('class') ? '.' + target.attr('class').replace( / /g, '.' )
          : ' ' );
    
    // Highlight this element and set its text.
    elem
      .addClass( 'event-outside' )
      .children( '.event-target' )
        .text( text );
  });
  
});
<?
$shell['script'] = ob_get_contents();
ob_end_clean();

// ========================================================================== //
// HTML HEAD ADDITIONAL
// ========================================================================== //

ob_start();
?>
<script type="text/javascript" src="../../jquery.ba-outside-events.js"></script>
<script type="text/javascript" language="javascript">

<?= $shell['script']; ?>

$(function(){
  
  // Syntax highlighter.
  SyntaxHighlighter.highlight();
  
});

</script>
<style type="text/css" title="text/css">

/*
bg: #FDEBDC
bg1: #FFD6AF
bg2: #FFAB59
orange: #FF7F00
brown: #913D00
lt. brown: #C4884F
*/

#page {
  width: 700px;
}

#test,
#test div {
  padding: 1em;
  margin-top: 1em;
}

#test .bind-me {
  padding: 0 0.5em;
  margin-left: 0.5em;
  white-space: nowrap;
  line-height: 1.6em;
}

#test,
#test div,
#test .bind-me {
  color: #ccc;
  border: 2px solid #ccc;
}

.event-outside {
  color: #0a0 !important;
  border-color: #0a0 !important;
  background-color: #cfc !important;
}

#test .bind-me,
.event-target {
  display: inline-block;
  width: 180px;
  overflow: hidden;
  white-space: pre;
  vertical-align: middle;
}

</style>
<?
$shell['html_head'] = ob_get_contents();
ob_end_clean();

// ========================================================================== //
// HTML BODY
// ========================================================================== //

ob_start();
?>
<?= $shell['donate'] ?>

<p>
  With <a href="http://benalman.com/projects/jquery-outside-events-plugin/">jQuery outside events</a> you can bind to an event that will be triggered only when a specific "originating" event occurs <em>outside</em> the element in question. For example, you can <a href="../clickoutside/">click outside</a>, <a href="../dblclickoutside/">double-click outside</a>, <a href="../mouseoveroutside/">mouse-over outside</a>, <a href="../focusoutside/">focus outside</a> (and <a href="http://benalman.com/code/projects/jquery-outside-events/docs/files/jquery-ba-outside-events-js.html#Defaultoutsideevents">over ten more</a> default "outside" events).
</p>
<p>
  You get the idea, right?
</p>

<h2>The dblclickoutside event, bound to a few elements</h2>

<p>Just click around, and see for yourself!</p>

<div id="test">
  test <span class="event-target"></span>
  
  <div id="a">
      a <span class="event-target"></span>
      <div id="b">
          b <span class="event-target"></span>
      </div>
  </div>
  
  <div id="c">
      c <span class="event-target"></span>
      <span id="d" class="bind-me">d <span class="event-target"></span> </span>
      <span id="e" class="bind-me">e <span class="event-target"></span> </span>
  </div>
  
  <div id="f">
      f <span class="event-target"></span>
      <div id="g">
          g <span class="event-target"></span>
          <span id="h" class="bind-me">h <span class="event-target"></span> </span>
          <span id="i" class="bind-me">i <span class="event-target"></span> </span>
      </div>
  </div>
</div>

<h3>The code</h3>

<div class="clear"></div>

<pre class="brush:js">
<?= htmlspecialchars( $shell['script'] ); ?>
</pre>

<?
$shell['html_body'] = ob_get_contents();
ob_end_clean();

// ========================================================================== //
// DRAW SHELL
// ========================================================================== //

draw_shell();

?>
