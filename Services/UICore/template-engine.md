# Template Engine

As a component developer you should rarely need to use the Template Engine directly since you should use the components of the [UI framework](../../src/UI) whenever possible.

But if you e.g. need to implement UI components yourself you need to understand the basics of the template engine.

The main idea of using templates is the separation of style (css), layout (views) and PHP code (controllers). All layout information is stored in .html template files. These files contain HTML markup and placeholders that are dynamically replaced by controller code.
 
## Placeholders and Blocks

```
[...]
<!-- BEGIN address -->
<div class="ilProfileSection">
	<h3 class="ilProfileSectionHead">{TXT_ADDRESS}</h3>
	<!-- BEGIN address_line -->
	<div>{TXT_ADDRESS_LINE}</div>
	<!-- END address_line -->
</div>
<!-- END address -->
[...]
```

This example contains placeholders `{TXT_ADDRESS}` and `{TXT_ADRESS_LINE}`and the block definition for `address_line` and `address`.

```
$tpl = new ilTemplate("tpl.address.html", true, true, Services/User");
$tpl->setCurrentBlock("address_line");
foreach($address as $line)
{
	if(trim($line))
	{
		$tpl->setVariable("TXT_ADDRESS_LINE", trim($line));
		$tpl->parseCurrentBlock();
	}
}
$tpl->setCurrentBlock("address");
$tpl->setVariable("TXT_ADDRESS", $lng->txt("address"));
$tpl->parseCurrentBlock();

$html = $tpl->get()
```

To fill a template you need an instance of `ilTemplate`. Start iterating inner blocks (`setCurrentBlock()`, `parseCurrentBlock()`) and fill their placeholders using `setVariable()`. Work subsequently through all blocks from inner to outer blocks.

To render the content of your template you need to call `$tpl->get()`.

Now you want to include your HTML in the main ILIAS layout.

## Main Template

The UI framework supports you with a global main template which supports methods to include output on an ILIAS screen.


```
$tpl = new ilTemplate("tpl.address.html", true, true, Services/User");
[...]

// get main template
$main_tpl = $DIC->ui()->mainTemplate();

// set content (center column)
$main_tpl->setContent($tpl->get());

// set title
$main_tpl->setTitle($title);

// set title icon
$main_tpl->setTitleIcon(ilUtil::getImagePath("icon_cat.gif"));

// set description section
$main_tpl->setDescription($description);

// set content of right column
$main_tpl->->setRightContent($right_content_html);
```