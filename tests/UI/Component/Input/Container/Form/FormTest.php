<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
require_once(__DIR__ . "/../../../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\UI\Implementation\Component\Input\Field\FormInputInternal;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\Container\Form\Form;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Data;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\Refinery\Custom\Transformation;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\MockObject\MockObject;

class FixedNameSource implements NameSource
{
    public string $name = "name";

    public function getNewName() : string
    {
        return $this->name;
    }
}

class ConcreteForm extends Form
{
    public ?Input\InputData $input_data = null;
    protected Input\Field\Factory $input_factory;
    protected Group $input_group;
    protected array $inputs;

    public function __construct(Input\Field\Factory $field_factory, NameSource $name_source, array $inputs)
    {
        $this->input_factory = $field_factory;
        parent::__construct($field_factory, $name_source, $inputs);
    }

    public function _extractPostData(ServerRequestInterface $request) : Input\InputData
    {
        return $this->extractPostData($request);
    }

    public function extractPostData(ServerRequestInterface $request) : Input\InputData
    {
        if ($this->input_data !== null) {
            return $this->input_data;
        }

        return parent::extractPostData($request);
    }


    public function setInputs(array $inputs) : void
    {
        $this->input_group = $this->input_factory->group($inputs);
        $this->inputs = $inputs;
    }
}

/**
 * Test on form implementation.
 */
class FormTest extends ILIAS_UI_TestBase
{
    /**
     * @var ilLanguage|mixed|MockObject
     */
    protected $language;
    protected array $inputs;

    protected function buildFactory() : Input\Container\Form\Factory
    {
        return new Input\Container\Form\Factory($this->buildInputFactory(), new DefNamesource());
    }

    protected function buildInputFactory() : Input\Field\Factory
    {
        $df = new Data\Factory();
        $this->language = $this->createMock(ilLanguage::class);
        return new Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new Refinery($df, $this->language),
            $this->language
        );
    }

    protected function buildButtonFactory() : I\Button\Factory
    {
        return new I\Button\Factory();
    }

    protected function buildTransformation(Closure $trafo) : Transformation
    {
        $dataFactory = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        $refinery = new Refinery($dataFactory, $language);

        return $refinery->custom()->transformation($trafo);
    }

    public function getUIFactory() : NoUIFactory
    {
        return new WithButtonNoUIFactory($this->buildButtonFactory());
    }

    public function buildDataFactory() : Data\Factory
    {
        return new Data\Factory;
    }

    public function test_getInputs() : void
    {
        $this->buildFactory();
        $if = $this->buildInputFactory();
        $name_source = new FixedNameSource();

        $inputs = [$if->text(""), $if->text("")];
        $form = new ConcreteForm($this->buildInputFactory(), new DefNamesource(), $inputs);

        $seen_names = [];
        $form_inputs = $form->getInputs();
        $this->assertSameSize($inputs, $form_inputs);

        foreach ($form_inputs as $input) {
            $name = $input->getName();
            $name_source->name = $name;

            // name is a string
            $this->assertIsString($name);

            // only name is attached
            $input = array_shift($form_inputs);
            $this->assertEquals($input->withNameFrom($name_source), $input);

            // every name can only be contained once.
            $this->assertNotContains($name, $seen_names);
            $seen_names[] = $name;
        }
    }

    public function test_extractPostData() : void
    {
        $form = new ConcreteForm($this->buildInputFactory(), new DefNamesource(), []);
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([]);
        $input_data = $form->_extractPostData($request);
        $this->assertInstanceOf(InputData::class, $input_data);
    }

    public function test_withRequest() : void
    {
        $df = $this->buildDataFactory();
        $request = $this->createMock(ServerRequestInterface::class);
        $input_data = $this->createMock(InputData::class);

        $input_1 = $this->inputMock();
        $input_1
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($input_1);
        $input_1
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($df->ok(0));

        $input_2 = $this->inputMock();
        $input_2
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($input_2);
        $input_2
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($df->ok(0));

        $form = new ConcreteForm($this->buildInputFactory(), new DefNamesource(), []);
        $form->setInputs([$input_1, $input_2]);
        $form->input_data = $input_data;

        $form2 = $form->withRequest($request);

        $this->assertNotSame($form2, $form);
        $this->assertInstanceOf(Form::class, $form2);
        $this->assertEquals([$input_1, $input_2], $form2->getInputs());
    }

    public function test_withRequest_respects_keys() : void
    {
        $df = $this->buildDataFactory();
        $request = $this->createMock(ServerRequestInterface::class);
        $input_data = $this->createMock(InputData::class);

        $input_1 = $this->inputMock();
        $input_1
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($input_1);
        $input_1
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($df->ok(0));

        $input_2 = $this->inputMock();
        $input_2
            ->expects($this->once())
            ->method("withInput")
            ->with($input_data)
            ->willReturn($input_2);
        $input_2
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($df->ok(0));

        $form = new ConcreteForm($this->buildInputFactory(), new DefNamesource(), []);
        $form->setInputs(["foo" => $input_1, "bar" => $input_2]);
        $form->input_data = $input_data;

        $form2 = $form->withRequest($request);

        $this->assertNotSame($form2, $form);
        $this->assertInstanceOf(Form::class, $form2);
        $this->assertEquals(["foo" => $input_1, "bar" => $input_2], $form2->getInputs());
    }

    public function test_getData() : void
    {
        $df = $this->buildDataFactory();
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([]);

        $input_1 = $this->inputMock();
        $input_1
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($df->ok(1));
        $input_1
            ->expects($this->once())
            ->method("withInput")
            ->willReturn($input_1);

        $input_2 = $this->inputMock();
        $input_2
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($df->ok(2));
        $input_2
            ->expects($this->once())
            ->method("withInput")
            ->willReturn($input_2);

        $form = new ConcreteForm($this->buildInputFactory(), new DefNamesource(), []);
        $form->setInputs([$input_1, $input_2]);
        $form = $form->withRequest($request);
        $this->assertEquals([1, 2], $form->getData());
    }

    public function test_getData_respects_keys() : void
    {
        $df = $this->buildDataFactory();
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([]);

        $input_1 = $this->inputMock();
        $input_1
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($df->ok(1));
        $input_1
            ->expects($this->once())
            ->method("withInput")
            ->willReturn($input_1);

        $input_2 = $this->inputMock();
        $input_2
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($df->ok(2));
        $input_2
            ->expects($this->once())
            ->method("withInput")
            ->willReturn($input_2);

        $form = new ConcreteForm($this->buildInputFactory(), new DefNamesource(), []);
        $form->setInputs(["foo" => $input_1, "bar" => $input_2]);
        $form = $form->withRequest($request);
        $this->assertEquals(["foo" => 1, "bar" => 2], $form->getData());
    }

    public function test_getData_faulty() : void
    {
        $df = $this->buildDataFactory();
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([]);

        $input_1 = $this->inputMock();
        $input_1
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($df->error("error"));
        $input_1
            ->expects($this->once())
            ->method("withInput")
            ->willReturn($input_1);

        $input_2 = $this->inputMock();
        $input_2
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($df->ok(2));
        $input_2
            ->expects($this->once())
            ->method("withInput")
            ->willReturn($input_2);

        $form = new ConcreteForm($this->buildInputFactory(), new DefNamesource(), []);
        $form->setInputs(["foo" => $input_1, "bar" => $input_2]);

        $i18n = "THERE IS SOME ERROR IN THIS GROUP";
        $this->language
            ->expects($this->once())
            ->method("txt")
            ->with("ui_error_in_group")
            ->willReturn($i18n);

        //Todo: This is not good, this should throw an error or similar.
        $form = $form->withRequest($request);
        $this->assertEquals(null, null);
    }

    public function test_withAdditionalTransformation() : void
    {
        $df = $this->buildDataFactory();
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([]);

        $input_1 = $this->inputMock();
        $input_1
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($df->ok(1));
        $input_1
            ->expects($this->once())
            ->method("withInput")
            ->willReturn($input_1);

        $input_2 = $this->inputMock();
        $input_2
            ->expects($this->once())
            ->method("getContent")
            ->willReturn($df->ok(2));
        $input_2
            ->expects($this->once())
            ->method("withInput")
            ->willReturn($input_2);

        $form = new ConcreteForm($this->buildInputFactory(), new DefNamesource(), []);
        $form->setInputs([$input_1, $input_2]);

        $form2 = $form->withAdditionalTransformation($this->buildTransformation(function () {
            return "transformed";
        }));

        $this->assertNotSame($form2, $form);
        $form2 = $form2->withRequest($request);

        $this->assertEquals("transformed", $form2->getData());
    }

    public function test_nameInputs_respects_keys() : void
    {
        $if = $this->buildInputFactory();
        $inputs = [
            2 => $if->text(""),
            "foo" => $if->text(""),
            1 => $if->text(""),
            $if->text(""),
        ];
        $form = new ConcreteForm($this->buildInputFactory(), new DefNamesource(), []);
        $form->setInputs($inputs);
        $named_inputs = $form->getInputs();
        $this->assertEquals(array_keys($inputs), array_keys($named_inputs));
    }

    /**
     * @return FormInputInternal|mixed|MockObject
     */
    protected function inputMock()
    {
        static $no = 1000;
        return $this
            ->getMockBuilder(FormInputInternal::class)
            ->onlyMethods([
                "getName",
                "withNameFrom",
                "withInput",
                "getContent",
                "getLabel",
                "withLabel",
                "getByline",
                "withByline",
                "isRequired",
                "withRequired",
                "isDisabled",
                "withDisabled",
                "getValue",
                "withValue",
                "getError",
                "withError",
                "withAdditionalTransformation",
                "getUpdateOnLoadCode",
                "getCanonicalName",
                "withOnLoadCode",
                "withAdditionalOnLoadCode",
                "getOnLoadCode",
                "withOnUpdate",
                "appendOnUpdate",
                "withResetTriggeredSignals",
                "getTriggeredSignals"
            ])
            ->setMockClassName("Mock_InputNo" . ($no++))
            ->getMock();
    }
}
