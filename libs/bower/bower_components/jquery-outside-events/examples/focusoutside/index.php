<?PHP

include "../index.php";

$shell['title3'] = "focusoutside";

$shell['h2'] = 'Why focus something, when you can.. Um, nevermind.';

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
    .bind( 'focusin', function(event){
      elems
        .removeClass( 'event-outside' )
        .children( '.event-target' )
          .text( ' ' );
    })
    .trigger( 'focusin' );
  
  // Bind the 'focusoutside' event to each test element.
  elems.bind( 'focusoutside', function(event){
    var elem = $(this),
      target = $(event.target),
      
      // Update the text to reference the event.target element.
      text = 'Focused: ' + target[0].tagName.toLowerCase()
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
  padding: 0.5em;
  margin-left: 0.5em;
  white-space: nowrap;
  line-height: 1.5em;
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

input.outside,
#test input {
  font-size: 10px;
  border: 1px solid #000;
  padding: 0.1em 0.3em;
  width: 50px;
}

#test .bind-me,
.event-target {
  display: inline-block;
  width: 130px;
  overflow: hidden;
  white-space: pre;
  vertical-align: middle;
}

#test .bind-me {
  width: 180px;
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

<h2>The focusoutside event, bound to a few elements</h2>

<p>Just focus some inputs, and see for yourself!</p>

<input type="text" class="outside" value="outside" id="outside">

<div id="test">
  <input type="text" value="top" id="top">
  <span class="event-target"></span>
  
  <div>
      <input type="text" value="a" id="a">
      <span class="event-target"></span>
      <div id="b">
          <input type="text" value="b" id="b">
          <span class="event-target"></span>
      </div>
  </div>
  
  <div>
      <input type="text" value="c" id="c">
      <span class="event-target"></span>
      <span class="bind-me"><input type="text" value="d" id="d"> <span class="event-target"></span> </span>
      <span class="bind-me"><input type="text" value="e" id="e"> <span class="event-target"></span> </span>
  </div>
  
  <div>
      <input type="text" value="f" id="f">
      <span class="event-target"></span>
      <div>
          <input type="text" value="g" id="g">
          <span class="event-target"></span>
          <span class="bind-me"><input type="text" value="h" id="h"> <span class="event-target"></span> </span>
          <span class="bind-me"><input type="text" value="i" id="i"> <span class="event-target"></span> </span>
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
