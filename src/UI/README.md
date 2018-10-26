# The ILIAS UI-Framework

The ILIAS UI-Framework helps you to implement GUIs consistent with the guidelines
of the Kitchen Sink.

## Use Kitchen Sink Concepts

The ILIAS UI-Framework deals with the concepts found in the Kitchen Sink. In fact,
this framework and the Kitchen Sink are heavily related. You won't need to think
about HTML if you're using this framework. You also won't need to think about
the implementation you are using, the device your GUI is displayed on or the
CSS-classes you need to use. You will be able to talk to other people (like users
or designers) using the same concepts and problem space as they do. This is also
not a templating framework.

## Compose GUIs from Simple Parts

In the ILIAS UI-Framework, GUIs are described by composing large chunks from
smaller components. The available components and their possible compositions are
described in the Kitchen Sink. The single components only have little  configuration,
complex GUIs emerge from simple parts. You also won't need to modify existing
components, just use them as provided.

## Correctness by Construction and Testability

The design of the ILIAS UI-Framework makes it possible to identify lots of
guideline violations during the construction of a GUI and turn them into errors
or exceptions in PHP. This gives you the freedom to care about your GUI instead
of the guidelines it should conform to. You also can check your final GUI for
Kitchen Sink compliance using the procedures the framework provides for Unit
Testing.

## Using the Framework

As a user of the ILIAS UI-Framework your entry point to the framework is provided
via the dependency injection container `$DIC->ui()->factory()`, which gives you
access to the main factory implementing ILIAS\UI\Factory.

### How to Discover the Components in the Framework?

The factories provided by the framework are structured in the same way as the
taxonomy given in the [KS-Layout](http://www.ilias.de/docu/goto_docu_wiki_wpage_3852_1357.html#ilPageTocA11).
The main factory provides methods for every node or leaf in the `Class`-Layer
of the Kitchen Sink Taxonomy. Using that method you get a sub factory if methods
corresponds to a node in the layout. If the method corresponds to a leaf in the
layout, you get a PHP representation of the component you chose. Since the Jour
Fixe decides upon entries in the Kitchen Sink, the factories in the framework
only contain entries `Accepted` by the JF. Creating a component with the
framework thus just means following the path from the `Class` to the leaf you
want to use in your GUI.

The entries of the Kitchen Sink are documented in this framework in a machine
readable form. That means you can rely on the documentation given in the
interfaces to the factories, other representations of the Kitchen Sink are
derived from there. This also means you can chose to use the [documentation of the
Kitchen Sink in ILIAS](http://www.ilias.de/docu/goto_docu_wiki_wpage_4009_1357.html)
to check out the components.

### How to Use the Components of the Framework?

With the ILIAS UI-Framework you describe how your GUI is structured instead of
instructing the system to construct it for you. The main principle for the description
of GUIs is composition.

You declare you components by providing a minimum set of properties and maybe
other components that are bundled in your component. All compents in the framework
strive to only use a small amount of required properties and provide sensible
defaults for other properties.

Since the representation of the components are implemented as immutable objects,
you can savely reuse components created elsewhere in your code, or pass your
component to other code without being concerned if the other code modifies it.

[Example 1](examples/Button/Primary/base.php)
[Example 2](examples/Glyph/Mail/mail_example.php)

## Implementing Elements in the Framework

As an implementor of components in the ILIAS UI-Framework you need to stick to
some [rules](docu/rules.md), to make sure the framework behaves in a uniform and
predictable way accross all components. Since a lot of code will rely on the
framework and the Kitchen Sink is coupled to the framework, there also are processes
to introduce new components in the framework and modify existing components.

### How to Introduce a New Component?

New components are introduced in the UI-Framework and the Kitchen Sink in
parallel to maintain the correspondence between the KS and the UI-Framework.

An entry in the Kitchen Sink passes through three states:

* **To be revised**: The entry is still being worked on. Just use a local copy
  or a fork of the ILIAS repository and try out what ever you want.
* **Proposed**: The entry has been revisited and is proposed to the Jour Fixe,
  but has not yet been decided upon. To enter this state, create a pull request
  against  the ILIAS trunk containing your proposed component and take it to the
  Jour Fixe. You need to provide a (mostly) complete definition of the component
  but an implementation is not required at this point. Your will have better
  chances if you also bring some visual representation of your new component,
  you may use the ILIAS edge branch for that.
* **Accepted**: The entry has been accepted by the JF. This, as allways, might
  need some iterations on the component.

These states are represented by using functionality of git and GitHub. After
acceptance, the new entry is part of the Kitchen Sink as well as part of the
source code in the trunk.

### How to Implement a Component?

If you would like to implement a new component to the framework you should perform the following tasks:

1. Add your new component into the respective factory interface. E.g. If you introduce a component of a completely new type, you MUST add the description to the main factory (src/UI/Factory.php). If you add a new type of a button, you MUST add the description to the existing factory for buttons, located at src/UI/Component/Button/Factory.
2. The description MUST use the following template:

    ``` php
    /**
    * ---
    * description:
    *   purpose: What is to be done by this control
    *   composition: What is this control composed of
    *   effect: What happens if the control is operated
    *   rivals:
    *     Rival 1: What other controls are similar, what is their distinction
    *
    * background: Relevant academic information
    * context: 
    *     - The context states: where this control is used specifically with examples (this list might not be complete) and how common is this control used
    *
    * rules:
    *   usage:
    *     1: Where and when an element is to be used or not.
    *   composition:
    *     1: How this component is to be assembled.
    *   interaction:
    *     1: How the interaction with this object takes place.
    *   wording:
    *     1: How the wording of labels or captions must be.
    *   style:
    *     1: How this element should look like.
    *   ordering:
    *     1: How different elements of this instance are to be ordered.
    *   responsiveness:
    *     1: How this element behaves on changing screen sizes
    *   accessibility:
    *     1: How this element is made accessible
    *
    * ---
    * @param   string $content
    * @return \ILIAS\UI\Component\Demo\Demo
    **/
    public function demo($content);
    ```

3. This freshly added function in the factory leads to an error as soon as ILIAS is opened, since the implementation
 of the factory (located at src/UI/Implementation/Factory.php) does not implement that function yet. For
 the moment, implement it, as follows:
 
    ``` php
    /**
    * @inheritdoc
    */
    public function demo($content)
    {
        throw new \ILIAS\UI\NotImplementedException();
    }
    ```

4. Next, you should think about the interface you would like to propose for this component.
 You need to model the component you want to introduce by defining its
 interface and the factory method that constructs the component. To make your
 component easy to use, it should be creatable with a minimum of parameters
 and use sensible defaults for the most of its properties. Also think about the
 use cases for your component. Make typical use cases easy to implement and
 more special use cases harder to implement. Put getters for all properties on
 your interface. Make sure you understand, that all UI components should be
 immutable, i.e. instead of defining setters `setXYZ` you must define mutators
 `withXYZ` that return copies of your component with changed properties. Try
 to use as little mutators as possible and try to make it easy to maintain the
 invariants defined in your rules when mutators will be used.
 Take care to keep it as minimal as possible. At a description for each function.
 For the demo component, this interface could look as follows (located at (src/UI/Component/Demo/Demo.php):
    ``` php
    <?php
    namespace ILIAS\UI\Component\Demo;

    /**
     * Interface Demo
     * @package ILIAS\UI\Component\Demo
     */
    interface Demo extends \ILIAS\UI\Component\Component {

        /**
         * Gets the content of this demo component
         * @return Demo
         */
        public function getContent();
    }
    ```
5. Make sure all tests are passing by executing '''phpunit tests/UI'''. For the demo component
  this means we have to add the following line to NoUIFactory in tests/UI/Base.php:
    ``` php
    public function demo($demo){}
    ```

6. Congratulations, at this point you are ready to present your work to the JF. Create
 a PR named "UI NameOfTheComponent". To make it easy for non-developers to
 follow the discussion, you MUST link to the changed/added factory classes and mock in the
 description you provide for your PR. Further, it would be wise to enhance your work
 with a little mockup. This makes it much easier to discuss the new component at the
 JF. So best create such an example located and also link it in your comment, e.g. at
 src/UI/examples/Demo/mockup.php:
    ``` php
    <?php
    function mockup() {
        return "<h1>Hello Demo!</h1>";
    }
    ```
   If needed, you can also add JS-logic (e.g. src/UI/examples/Demo/mockup.php):
    ``` php
    <?php
    function script() {
        return "<script>console.log('Hello Demo');</script>Open your JS console!";
    }
    ```
   However best might be to just provide a screenshoot showing what the component will
   look like:
    ``` php
    function mockup() {
	    global $DIC;
 	    $f = $DIC->ui()->factory();
 	    $renderer = $DIC->ui()->renderer();

 	    $mockup = $f->image()->responsive("src/UI/examples/Demo/mockup.png");
 	    return $renderer->render($mockup);
    }
    ```

7. Next you should create the necessary tests for the new component. At least provide tests
  for all interface methods and the rendering.
  For the demo component this looks as follows (located at tests/UI/Component/Demo/DemoTest.php):
   ``` php
    <?php

    require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
    require_once(__DIR__."/../../Base.php");

    use \ILIAS\UI\Component as C;

    /**
     * Test on demo implementation.
     */
    class DemoTest extends ILIAS_UI_TestBase {

        public function test_implements_factory_interface() {
            $f = new \ILIAS\UI\Implementation\Factory();

            $this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
            $demo = $f->demo("Demo Implementation!");
            $this->assertInstanceOf( "ILIAS\\UI\\Component\\Demo\\Demo", $demo);
        }

        public function test_get_content() {
            $f = new \ILIAS\UI\Implementation\Factory();
            $demo = $f->demo("Demo Implementation!");

            $this->assertEquals($demo->getContent(), "Demo Implementation!");
        }

        public function test_render_content() {
            $r = $this->getDefaultRenderer();
            $f = new \ILIAS\UI\Implementation\Factory();
            $demo = $f->demo("Demo Implementation!");


            $html = $r->render($demo);

            $expected_html = '<h1 class="il-demo">Demo Implementation!</h1>';

            $this->assertHTMLEquals($expected_html, $html);
        }
    }
    ```

8. Currently you will only get the NotImplementedException you throwed previously. That needs to be changed.
  First, add an implementation for the new interface (add it at src/UI/Implementation/Component/Demo/Demo.php):
    ``` php
    <?php
    namespace ILIAS\UI\Implementation\Component\Demo;

    use ILIAS\UI\Component\Demo as D;
    use ILIAS\UI\Implementation\Component\ComponentHelper;

    class Demo implements D\Demo {
        use ComponentHelper;

        /**
         * @var string
         */
        protected $content;

        /**
         * @param $content
         */
        public function __construct($content){
            $this->checkStringArg("title", $content);

            $this->content = $content;
        }

        /**
         * @inheritdoc
         */
        public function getContent(){
            return $this->content;
        }
    }
    ```
9. Next, make the factory return the new component (change demo() of src/UI/Implementation/Factory.php):

    ``` php
    return new Component\Demo\Demo($content);
    ```

10. Then, implement the renderer at src/UI/Implementation/Component/Demo/Demo.php:
    ``` php
    <?php

    /* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

    namespace ILIAS\UI\Implementation\Component\Demo;

    use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
    use ILIAS\UI\Renderer as RendererInterface;
    use ILIAS\UI\Component;

    class Renderer extends AbstractComponentRenderer {
        /**
         * @inheritdocs
         */
        public function render(Component\Component $component, RendererInterface $default_renderer) {
            $this->checkComponent($component);
            $tpl = $this->getTemplate("tpl.demo.html", true, true);
            $tpl->setVariable("CONTENT",$component->getContent());
            return $tpl->get();
        }

        /**
         * @inheritdocs
         */
        protected function getComponentInterfaceName() {
            return array(Component\Demo\Demo::class);
        }
    }
    ```
11. Finally you need the template used to render your component. Create it at src/UI/templates/default/Demo/tpl.demo.html:
     ``` php
    <h1 class="il-demo">{CONTENT}</h1>
     ```
12. Execute the UI tests again. At this point, everything should pass. Thanks, you just made ILIAS more powerful!
13. Optional: It is possible good to add an examples demonstrating the usage of your new component.
  The example for the demo looks as follows (located at src/UI/examples/Demo/render.php):
    ``` php
      <?php
      function render() {
          //Init Factory and Renderer
          global $DIC;
          $f = $DIC->ui()->factory();
          $renderer = $DIC->ui()->renderer();

          $demo = $f->demo("Demo rendered by template!");


          return $renderer->render($demo);
      }
    ```
14. Optional: You might need to add some less, to make your new component look nice. However, only do that
 if this is really required. Use bootstrap classes as much as possible. If you really need to add
 additional less, use existing less variables whenever appropriate. If you add a new variable, add the il- prefix
 to mark the as special ILIAS less variable and provide the proper description. For the demo this could look as
 follows (located at src/UI/templates/default/Demo/demo.less):
    ``` less
    .il-demo{
     color: @il-demo-color;
    }
    ```
15. Include the new less file to delos (located at templates/default/less/delos.less):
    ``` less
    @import "@{uibase}Demo/demo.less";
    ```

16. Optional add the new variables to the variables.less file (located at templates/default/less/variables.less):
    ``` less
    //== Demo Component
    //
    //## Those variables are only used for demo purposes
    //** Color of the text shown in the demo
    @il-demo-color: @brand-danger;
    ```
17. Optional: Recompile the less to see the effect by typing lessc templates/default/delos.less > templates/default/delos.css

18. Optional: If your component introduces a new factory, do not forget to wire it up in the according
    location of the initialisation. Have a look into `ilInitialisation::initUIFramework` in
    `Services/Init/class/class.ilInitialisation.php`.


### How to Change an Existing Component?

TODO: write me

## Abstraction of Javascript in the Framework

_Note: The concept described in this section is not yet fully finalized._

The functionality of some components relies on javascript. As an example, a modal is showed and closed by 
clicking on some triggerer component. As a user of the framework, you should not worry about writing the javascript
logic for this kind of interactions.

### About Triggerables, Triggerer and Signals

Before describing the concept in more detail, you should be aware of the following definitions:
* **Signal** A signal describes a javascript action of a component which can be triggered by another component of the
framework.
* **Triggerable** A component offering some signals that can be triggered by other components.
* **Triggerer** A component triggering a signal of another component. 
* **Event** The javascript event on which a signal is being triggered, e.g. `click`, `hover` etc.

Again, consider the example if a user opens a modal by clicking on a button:

* Signal: Show Modal
* Triggerable: Modal
* Triggerer: Button
* Event: Click 

### How to trigger Signals of Components

This code snippet shows how to open a modal by clicking on a button:
```php
$modal = $factory->modal()->roundtrip('Title', $factory->legacy('Hello World'));
$button = $factory->button()->standard('Open Modal', '#')
  ->withOnClick($modal->getShowSignal());
```
The button is a triggerer component. As such, it offers the method `withOnClick` which takes any `Signal` 
offered by a triggerable. This is how the framework connects triggerer components with signals of triggerable components.
Similar to the click event, there exist methods `withOnHover` and `withOnLoad` to abstract the javascript events on
which a signal is being triggered.

#### Attention: Immutable Objects and Signals
Each triggerer component stores the signals it triggers. By cloning a component, these signals are cloned as well.
This means that a cloned component may trigger the same signals as the original. Consider the following example:
 ```php
$modal = $factory->modal()->roundtrip('Title', $factory->legacy('Hello World'));
$button1 = $factory->button()->standard('Open Modal', '#')
  ->withOnClick($modal->getShowSignal());
$button2 = $button1->withLabel('Open the same Modal');   
 ```
In the example above, `$button2` will open the same modal as `$button1`. In order to reset any triggered signals, use
 the method `$button2->withResetTriggeredSignals()`.

### Implementing a Triggerer Component

Any component acting as triggerer must implement the `Triggerer` interface. This interface is further extended by 
interfaces describing the javascript event on which a signal is being triggered. Currently, there exist the `Clickable`,
 `Hoverable` and `Onloadable` interfaces. Please check out the button component for an example implementation.

### Implementing a Triggerable Component

Any component acting as triggerable must implement the `Triggerable` interface. In addition, it must offer at least
one signal that can be triggered by other components. The renderer of the triggerable component is also responsible
to execute the javascript logic if any signal is getting triggered. Please check out the modal component for an example
implementation. The next section explains how the concept of signals/triggerer/triggerable is abstracted in javascript.

### Technical Details

The magic how everything is glued together on the javascript side takes part in the renderers of the triggerer and 
triggerable components:
* Triggerer: The renderer of the triggerer component knows which signals are triggered on which events. It registers
a new event handler on the component (e.g. on click/hover) which will trigger the signal as a custom javascript event.
* Triggerable: The renderer of the triggerable component knows the signals and the javascript logic which must be
executed if any of the signals is getting triggered.

Each signal has a unique alphanumeric ID. The triggerer uses this ID to trigger a custom javascript event which
 will be handled by some event handler from the triggerable. In order to understand this concept, take a look at the 
 javascript code that is getting generated by the renderers if a button opens a modal on click:
 
**Renderer of button**  
The renderer of the button generates the HTML for the button AND registers the event handler for the button click.
This event handler triggers a custom javascript event with the same name as the ID of show signal of the modal:
 ```
 <button id="button1">Open Modal</button>
 <script>
 $('#button1').on('click', function() {
   $(this).trigger('id_of_signal_to_open_the_modal',
      { 
        'id' : 'id_of_signal_to_open_the_modal',
        'triggerer' : $(this),
        'event' : 'click',
        'options' : {}
      }
      return false;
 });
 </script>
 ```
Note that some event data is passed with the event, such as the triggerer, event and event options. This allows the
event handler of the modal to identify the triggerer.

**Renderer of modal**  
The renderer of the modal generates the HTML for the modal AND registers an event handler on the ID of the show signal.
The event handler calls some javascript logic to show the modal.
```
<div class="modal" id="modal1"> ... </div>
<script>
$(document).on('id_of_signal_to_open_the_modal', function(event, signalData) { 
  il.UI.modal.showModal('modal1', signalData);
});
</script>
```
Note: `signalData` contains the event data passed by the triggerer, e.g. `signalData.triggerer` holds the JQuery
object of the button.

For more information on events in javascript, in the context of JQuery: http://api.jquery.com/category/events/

## FAQ

### There are so many rules, is that really necessary?

The current state of the art in ILIAS GUI creation was dubbed "The GUI Anarchy"
by some smart person. The introduction of the ILIAS UI framework aims at bringing
more structure in the GUIs of ILIAS. As one (or two) maintainers for all things
GUI of ILIAS is no option for several reasons and the current state (without rules)
is anarchy, rules seem to be the only sensible option to get some structure. All
exisiting rules have a purpose, but there might be a more terse way to explain
them. If you have found it, we'll be glad to accept your PR.

### How do I know where my component is rendered?

For some use cases you might get to the point where you want to know where your
component is rendered to emit different HTML in your renderer. A general idea of
the UI framework is that components have their unique look that is recognisable
throughout the system, which is the exact reason you could not find a simple way
to get to know where your component is rendered.

There still might be circumstances where a context dependent rendering is indeed
required. The [Renderer](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/src/UI/Renderer.php)
offers a `withAdditionalContext` method for that purpose, which can be used to
alter the selection of the renderer for your component. Before using it, consider
if you really require a different look in a different context and, if that is indeed
the case, whether you could achieve the same effect by using CSS. The class [FSLoader](src/UI/Implementation/Render/FSLoader.php)
contains directions how to introduce new renderers for different contexts in your
component.

### I don't understand that stuff, is there anyone who can explain it to me?

Yes. Ask Richard Klees <richard.klees@concepts-and-training.de> or Timon Amstutz
 <timon.amstutz@ilub.unibe.ch>.
