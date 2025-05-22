# The ILIAS UI-Framework

The ILIAS UI-Framework helps you to implement GUIs consistent with the guidelines
of the Kitchen Sink.

## Tutorial
You find a tutorial on what UI Components are and on their purpose, how they are used and what you should consider
while using them [here](../../../docs/development/devguide/tutorial/04-coding/04-ui-framework.md).

## Correctness by Construction and Testability

The design of the ILIAS UI-Framework makes it possible to identify lots of
guideline violations during the construction of a GUI and turn them into errors
or exceptions in PHP. This gives you the freedom to care about your GUI instead
of the guidelines it should conform to. You also can check your final GUI for
Kitchen Sink compliance using the procedures the framework provides for Unit
Testing.

## Condsider the overarching UX

When building views with the UI framework or adding new components to it, please
be mindful of the larger context. Every UI and their elements should follow an
overarching strategy to intuitively guide the user as much as possible to the
functions and information they are currently interested in. Coherent and
comprehensive UI concepts make ILIAS easier to use and avoid overcrowded and
confusing screens.

Here are some points to consider when creating a layered, context-sensitive UX
strategy:
* Anticipate the user intent to group, highlight, show, and hide information and
actions depending on context.
* Make use of the experience a user brings from other apps to match a mental model
they have already learned.
* Consider giving operations their own specialized interface, step, or mode rather
than building a one-size-fits-all screen but utilize common UX-concepts and already
existing UI components when you do.

Documents with recommendations on how to approach UX challenges of specific UI
components and use cases will be added below:
* [Best practices for properties and actions displayed on repository objects](docu/ux-guide-repository-objects-properties-and-actions.md)

## Implementing Elements in the Framework

To get a brief overview on how to proceed before starting to implement elements in the UI framework, we recommend to
read our [DevGuide](../../../docs/development/devguide/tutorial/05-contributing/03-ui-component.md) about this topic.

### How to Implement a Component?

If you would like to implement a new component for the framework, you should perform the following tasks:

1. Add your new component into the respective factory interface. E.g. if you introduce a component of a completely new type, you MUST add the description to the main factory (components/ILIAS/UI/src/Factory.php). If you add a new type of button, you MUST add the description to the existing factory for buttons, located at src/UI/Component/Button/Factory.
2. The description MUST use the following template:

    ```php
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
    *     1: How this element behaves on changing screen sizes.
    *   accessibility:
    *     1: How this element is made accessible.
    *
    * ---
    * @param   string $content
    * @return \ILIAS\UI\Component\Demo\Demo
    **/
    public function demo($content): ILIAS\UI\Component\Demo\Demo;
    ```

3. This freshly added function in the factory leads to an error as soon as ILIAS is opened, since the implementation
 of the factory (located at src/UI/Implementation/Factory.php) does not implement that function yet. For
 the moment, implement it as follows:

    ```php
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
 Take care to keep it as minimal as possible. Add a description for each function.
 For the demo component, this interface could look as follows (located at (src/UI/Component/Demo/Demo.php):
    ```php
    <?php declare(strict_types=1)
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
  this means we have to add the following line to NoUIFactory in UI/test/Base.php:
    ```php
    public function demo($demo){}
    ```

6. Congratulations, at this point you are ready to present your work to the JF. Create
 a PR named "UI NameOfTheComponent". To make it easy for non-developers to
 follow the discussion, you MUST link to the changed/added factory classes and mock in the
 description you provide for your PR. Further, it would be wise to enhance your work
 with a little mockup. This makes it much easier to discuss the new component at the
 JF. So best create such an example and also link it in your comment, e.g. at
 src/UI/examples/Demo/mockup.php:
    ```php
    <?php declare(strict_types=1)
    function mockup() {
        return "<h1>Hello Demo!</h1>";
    }
    ```
   If needed, you can also add JS-logic (e.g. src/UI/examples/Demo/mockup.php):
    ```php
    <?php declare(strict_types=1)
    function script() {
        return "<script>console.log('Hello Demo');</script>Open your JS console!";
    }
    ```
   However best might be to just provide a screenshot showing what the component will
   look like:
    ```php
    function mockup() {
	    global $DIC;
 	    $f = $DIC->ui()->factory();
 	    $renderer = $DIC->ui()->renderer();

 	    $mockup = $f->image()->responsive("src/UI/examples/Demo/mockup.png");
 	    return $renderer->render($mockup);
    }
    ```

7. Next you should create the necessary tests for the new component. Since this is a very important step, it deserved
   its own [chapter below](#How-to-write-unit-tests-for-a-Component). **Make sure you write at at least tests for all
   interface methods and one full rendering test.**

8. Currently you will only get the NotImplementedException you threw previously. That needs to be changed.
  First, add an implementation for the new interface (add it at src/UI/Implementation/Component/Demo/Demo.php):
    ```php
    <?php declare(strict_types=1)
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
9. Next, make the factory return the new component (change demo() of src/UI/Implementation/Factory.php) and
   add the return **concrete** type declaration. Please make sure you have read about the
   [interface segregation and covariance](./docs/segregation-and-covariance.md) and understand it:

    ```php
    public function demo($content): Component\Demo\Demo
    {
        return new Component\Demo\Demo($content);
    }
    ```

10. Then, implement the renderer at src/UI/Implementation/Component/Demo/Demo.php:
    ```php
    <?php declare(strict_types=1)

    /* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

    namespace ILIAS\UI\Implementation\Component\Demo;

    use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
    use ILIAS\UI\Renderer as RendererInterface;
    use ILIAS\UI\Component;

    class Renderer extends AbstractComponentRenderer {
        /**
         * @inheritdocs
         */
        public function render(Component\Component $component, RendererInterface $default_renderer): string 
        {
            // if this is not our component, call cannotHandleComponent($component)
            // to throw a unified exception. 
            if (!$component instanceof Component\Demo\Demo) {
                $this->cannotHandleComponent($component);
            }
    
            $tpl = $this->getTemplate("tpl.demo.html", true, true);
            $tpl->setVariable("CONTENT",$component->getContent());
            return $tpl->get();
        }
    }
    ```
11. Finally you need the template used to render your component. Create it at src/UI/templates/default/Demo/tpl.demo.html:
     ```html
    <h1 class="il-demo">{CONTENT}</h1>
     ```
12. Execute the UI tests again. At this point, everything should pass. Thanks, you just made ILIAS more powerful!
13. Remember to add examples demonstrating the usage of your new component.
    Those examples should showcase the key features of the new component.
    Note that they will be used as basis for the test cases in testrail (see
    next point). The example for the demo looks as follows (located at
    src/UI/examples/Demo/render.php):
    ```php
      <?php declare(strict_types=1)
      function render() {
          //Init Factory and Renderer
          global $DIC;
          $f = $DIC->ui()->factory();
          $renderer = $DIC->ui()->renderer();

          $demo = $f->demo("Demo rendered by template!");


          return $renderer->render($demo);
      }
    ```
14. Remember to adapt/add the Test Cases in 
    [Testrail section UI Components](https://testrail.ilias.de/index.php?/suites/view/390) 
    so that a tester with no technical expertise can confirm that all examples
    work as intended. They must be available and linked to the PR before the PR will be merged.
  
15. Optional: You might need to add some scss, to make your new component look nice. However, only do that
    if this is really required. (see: [SCSS Guidelines](../../templates/Guidelines_SCSS-Coding.md) ).
16. Formulate at least one test-case for your new component on testrail.ilias.de](https://testrail.ilias.de).
    Best, try to formulate a testcase for each relevant client-side interaction. E.g. if 
    your component contains a button that triggers a modal on-click, write a test-case for this
    interaction. Post the the link to this test-case in a comment/the description of your PR.
17. Optional: If your component introduces a new factory, do not forget to wire it up in the according
    location of the initialisation. Have a look into `ilInitialisation::initUIFramework` in
    `components/ILIAS/Init/class/class.ilInitialisation.php`.

### How to write unit tests for a Component?

When creating a new component, please make sure you provide at least tests for all interface methods and one full
rendering test. For the demo component we have created above, this looks as follows (located at
UI/tests/Component/Demo/DemoTest.php). Please make sure your unit test extends from the `ILIAS_UI_TestBase`, so you can
use functionalities like getting the test renderer for rendering tests.

```php
    <?php declare(strict_types=1)

    require_once(__DIR__."/../../../../vendor/composer/vendor/autoload.php");
    require_once(__DIR__."/../../Base.php");

    use \ILIAS\UI\Component as C;

    /**
     * Test on demo implementation.
     */
    class DemoTest extends ILIAS_UI_TestBase {
        public function testImplementsFactoryInterface() {
            $f = new \ILIAS\UI\Implementation\Factory();

            $this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
            $demo = $f->demo("Demo Implementation!");
            $this->assertInstanceOf( "ILIAS\\UI\\Component\\Demo\\Demo", $demo);
        }

        public function testGetContent() {
            $f = new \ILIAS\UI\Implementation\Factory();
            $demo = $f->demo("Demo Implementation!");

            $this->assertEquals("Demo Implementation!", $demo->getContent());
        }

        public function testRenderContent() {
            $r = $this->getDefaultRenderer();
            $f = new \ILIAS\UI\Implementation\Factory();
            $demo = $f->demo("Demo Implementation!");


            $html = $r->render($demo);

            $expected_html = '<h1 class="il-demo">Demo Implementation!</h1>';

            $this->assertHTMLEquals($expected_html, $html);
        }
    }
```

If you are implementing or adjusting the unit tests for a more complex component, you need to be careful when writing
rendering tests. If your component features further components from the framework, either by composable aspects like
e.g. providing a roundtrip modals action buttons (`ILIAS\UI\Component\Modal\Roundtrip::withActionButtons()`), or
by rendering further components during the rendering process, it is implicitly coupled to the HTML of other components.

If thats the case, you MUST implement your rendering tests using "component stubs", to fully decouple the unit tests
from other components. This means, instead of rendering actual components in your unit test, you provide mocked
instances of the component interfaces used within your component or unit test, so we have full control over the HTML
being rendered for each component. An implementation for the first scenario (externally provided components) would look
like this for our demo component:

```php
// ...

class DemoTest extends ILIAS_UI_TestBase
{
    // ...
    
    /**
     * Tests if the action button which is provided is catually rendered.
     */
    public function testWithActionButtonRendering(): void
    {
        $f = new \ILIAS\UI\Implementation\Factory();
        $demo = $f->demo('');
        
        // create the component mock for a standard button.
        $button_stub = $this->createMock(\ILIAS\UI\Component\Button\Standard::class);
        
        // configure the mock to return our desired HTML, it is advised to make this
        // value unique, so we can check only the existense using a str_contains()
        // approach.
        $button_html = sha1(\ILIAS\UI\Component\Button\Standard::class);
        $button_stub->method('getCanonicalName')->willReturn($button_html);
        
        // make the mock known to the renderer, so it can be rendered.
        $renderer = $this->getDefaultRenderer(null, [$button_stub]);
        
        // provide the mock to the method we are testing.
        $demo = $demo->withActionButton($button_stub);
    
        $actual_html = $renderer->render($demo);
        
        // checks only the existence of the component, using str_contains() to
        // search our unique stub HTML.
        $this->assertTrue(str_contains($actual_html, $stub_html));
    
        $expected_html = <<<EOT
<div class="c-demo">
    <h1 class="il-demo"></h1>
    $button_html
</div>
EOT;
    
        // checks the exact position of the component stub, using the unique HTML
        // value embeded in the expected HTML.
        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html), 
            $this->brutallyTrimHTML($actual_html)
        );
    }
}
```

**Note it is also important to think about what you are actually testing with your rendering test.** If testing only for
the existence of something is important, you can use the `str_contains()` approach to reduce maintenance in the future.
However, it might be necessary to also test the location of a ceratin component, e.g. if an action button should be
inside a certain container, so the styling works properly. In this case, you may use the `assertEquals()` approach with
the embedded stub-HTML.

For the latter scenario (internally rendered components), you need to make the component stubs available in the UI
factory which is injected into the actual renderer. This way, we still have full control over the HTML of other
components which may be rendered and checked in your rendering tests. For our demo component this would work like this:

```php
// ...

class DemoTest extends ILIAS_UI_TestBase
{
    // ...
    protected \ILIAS\UI\Implementation\Component\Button\Factory $button_factory;
    protected \ILIAS\UI\Implementation\Component\Button\Standard $button_stub;
    protected string $button_html;
    
    /** Sets up the button stub which will be rendered internally */
    public function setUp() : void
    {
        // setup the button stub similar to the previous example.
        $this->button_stub = $this->createMock(\ILIAS\UI\Implementation\Component\Button\Standard::class);
        $this->button_html = sha1(\ILIAS\UI\Implementation\Component\Button\Standard::class);
        $this->button_stub->method('getCanonicalName')->willReturn($this->button_html);
    
        // setup the factory so it will return the button stub.
        $this->button_factory = $this->createMock(\ILIAS\UI\Implementation\Component\Button\Factory::class);
        $this->button_factory->method('standard')->willReturn($this->button_stub);
        
        // don't forget to call our parent!
        parent::setUp();
    }

    /** Overrides the factory retrieval so it uses our instance of the button factory. */
    public function getUIFactory(): NoUIFactory
    {
        return new class ($this->button_factory) extends NoUIFactory {
            public function __construct(
                protected \ILIAS\UI\Implementation\Component\Button\Factory $button_factory,
            ) {
            }
            
            public function button(): \ILIAS\UI\Implementation\Component\Button\Factory
            {
                return $this->button_factory;
            }
        };
    }

    /** Tests if the action button is rendered properly during the internal rendering process. */
    public function testInternalActionButtonRendering(): void
    {
        $f = new \ILIAS\UI\Implementation\Factory();
        $demo = $f->demo('');

        // render the component making the stub available.
        $renderer = $this->getDefaultRenderer(null, [$this->button_stub]);
        $actual_html = $renderer->render($demo);

        // check existence ... 
        $this->assertTrue(str_contains($actual_html, $this->stub_html));
        
        $expected_html = <<<EOT
<div class="c-demo">
    <h1 class="il-demo"></h1>
    $this->button_html
</div>
EOT;

        // check location ...
        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html), 
            $this->brutallyTrimHTML($actual_html)
        );
    }
}
```

### How can I make my component look different in some context?

For some use cases you might get to the point where you want to know where your
component is rendered to emit different HTML in your renderer. A general idea of
the UI framework is that components have their unique look that is recognisable
throughout the system, which is the exact reason you could not find a simple way
to get to know where your component is being rendered.

There still might be circumstances where a context dependent rendering is indeed
required. A context can be understood as a collection or stack of all other surrounding
UI components. This means, if e.g. a Page component is rendered which needs to render
a Dropdown component somewhere that features some further Shy button compoennt, the 
rendering stack or context when the button is rendered would be "Page -> Dropdown -> Shy".
The [DefaultRenderer](./src/Implementation/DefaultRenderer.php) orchestrates this process
and is responsible to remember this context at any time during the entire rendering
process. Component renderers are able to react to this context using a `RendererFactory`,
which receives the current context as an argument when loading the renderer of some
component. The [FSLoader](./src/UI/Implementation/Render/FSLoader.php) contains directions
on how to introduce new renderers for different contexts in your component.

**Before using this mechanism, please consider if you really require a different look in a
different context and, and if thats the case, whether you could achieve the same effect using
CSS or not.**

### How to Change an Existing Component?

1. Create a new branch based on the current trunk.
2. Implement your changes and create a PR on the current trunk.
3. Clearly state in the description of the PR, why you believe the change to be necessary.
If your change fixes a bug, link to the according bugfix. If you need the change for implementing a feature, link
the Feature Request. If you are changing the interface of a component, your proposal will be discussed in the next JF 
(see: [rules](./docu/rules) ).


## Abstraction of Javascript in the Framework

_Note: The concept described in this section is not yet fully finalized._

The functionality of some components relies on Javascript. As an example, a modal is shown and closed by 
clicking on some triggerer component. As a user of the framework, you should not worry about writing the Javascript
logic for this kind of interaction.

### About Triggerables, Triggerer and Signals

Before describing the concept in more detail, you should be aware of the following definitions:
* **Signal** A signal describes a Javascript action of a component which can be triggered by another component of the
framework.
* **Triggerable** A component offering some signals that can be triggered by other components.
* **Triggerer** A component triggering a signal of another component. 
* **Event** The Javascript event on which a signal is being triggered, e.g. `click`, `hover` etc.

Again, consider the example if a user opens a modal by clicking on a button:

* Signal: Show Modal
* Triggerable: Modal
* Triggerer: Button
* Event: Click 

### How to trigger Signals of Components

This code snippet shows how to open a modal by clicking on a button:
```php
global $DIC;
$factory = $DIC->ui()->factory();
$modal = $factory->modal()->roundtrip('Title', $factory->legacy()->content('Hello World'));
$button = $factory->button()->standard('Open Modal', '#')
  ->withOnClick($modal->getShowSignal());
```
The button is a triggerer component. As such, it offers the method `withOnClick` which takes any `Signal` 
offered by a triggerable. This is how the framework connects triggerer components with signals of triggerable components.
Similar to the click event, there exist methods `withOnHover` and `withOnLoad` to abstract the Javascript events on
which a signal is being triggered.

#### Attention: Immutable Objects and Signals
Each triggerer component stores the signals it triggers. By cloning a component, these signals are cloned as well.
This means that a cloned component may trigger the same signals as the original. Consider the following example:
 ```php
global $DIC;
$factory = $DIC->ui()->factory();
$modal = $factory->modal()->roundtrip('Title', $factory->legacy()->content('Hello World'));
$button1 = $factory->button()->standard('Open Modal', '#')
  ->withOnClick($modal->getShowSignal());
$button2 = $button1->withLabel('Open the same Modal');   
 ```
In the example above, `$button2` will open the same modal as `$button1`. In order to reset any triggered signals, use
 the method `$button2->withResetTriggeredSignals()`.

### Implementing a Triggerer Component

Any component acting as triggerer must implement the `Triggerer` interface. This interface is further extended by 
interfaces describing the Javascript event on which a signal is being triggered. Currently, there exist the `Clickable`,
 `Hoverable` and `Onloadable` interfaces. Please check out the button component for an example implementation.

### Implementing a Triggerable Component

Any component acting as triggerable must implement the `Triggerable` interface. In addition, it must offer at least
one signal that can be triggered by other components. The renderer of the triggerable component is also responsible
for executing the Javascript logic if any signal is getting triggered. Please check out the modal component for an example
implementation. The next section explains how the concept of signals/triggerer/triggerable is abstracted in Javascript.

### Technical Details

The magic how everything is glued together on the Javascript side happens in the renderers of the triggerer and 
triggerable components:
* Triggerer: The renderer of the triggerer component knows which signals are triggered on which events. It registers
a new event handler on the component (e.g. on click/hover) which will trigger the signal as a custom Javascript event.
* Triggerable: The renderer of the triggerable component knows the signals and the Javascript logic which must be
executed if any of the signals is getting triggered.

Each signal has a unique alphanumeric ID. The triggerer uses this ID to trigger a custom Javascript event which
 will be handled by some event handler from the triggerable. In order to understand this concept, take a look at the 
 Javascript code that is getting generated by the renderers if a button opens a modal on click:
 
**Renderer of button**  
The renderer of the button generates the HTML for the button AND registers the event handler for the button click.
This event handler triggers a custom Javascript event with the same name as the ID of show signal of the modal:

 ```html
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
The event handler calls some Javascript logic to show the modal.

```html
<div class="modal" id="modal1"> ... </div>
<script>
$(document).on('id_of_signal_to_open_the_modal', function(event, signalData) { 
  il.UI.modal.showModal('modal1', signalData);
});
</script>
```
Note: `signalData` contains the event data passed by the triggerer, e.g. `signalData.triggerer` holds the JQuery
object of the button.

For more information on events in Javascript in the context of JQuery: http://api.jquery.com/category/events/


## Code Style
We are currently not enforcing code style, **but eventually will**.

### PHP

Use [PHPStan](../../../scripts/PHPStan/README.md) to check your files:
```
./scripts/PHPStan/run_check.sh src/UI/...
```
There are different [levels of checks](https://phpstan.org/user-guide/rule-levels),
you can e.g. run 
```
./vendor/composer/vendor/bin/phpstan analyse --level 8 src/UI/...
```
to override `./scripts/PHPStan/phpstan.neon`, however, level 9/max is the desired goal.

### Java Script

In order to validate your JS-files, run 

```
./node_modules/.bin/eslint --parser-options ecmaVersion:13 src/UI/templates/js/...
```
or change/add `.eslintrc.json` in ILIAS' root directory:
```
{
  "parserOptions": {
    "ecmaVersion": 13
  },
  "extends": "airbnb-base"
}
```
To install the linter (and its config), run

```
npm i -D "eslint" "eslint-config-airbnb-base" "eslint-plugin-import"

```

## FAQ

### There are so many rules, is that really necessary?

The current state of the art in ILIAS GUI creation was dubbed "The GUI Anarchy"
by some smart person. The introduction of the ILIAS UI framework aims at bringing
more structure in the GUIs of ILIAS. As one (or two) maintainers for all things
GUI of ILIAS is no option for several reasons and the current state (without rules)
is anarchy, rules seem to be the only sensible option to get some structure. All
existing rules have a purpose, but there might be a more terse way to explain
them. If you have found it, we'll be glad to accept your PR.

### I don't understand that stuff, is there anyone who can explain it to me?

Yes. Ask Richard Klees <richard.klees@concepts-and-training.de>, Timon Amstutz
 <timon.amstutz@ilub.unibe.ch> or Thibeau Fuhrer <thibeau@sr.solutions>.
