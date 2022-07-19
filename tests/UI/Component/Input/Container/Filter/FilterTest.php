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
use ILIAS\UI\Implementation\Component\Input\Container\Filter\Filter;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Data;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\UI\Component\Input\Field\Group;

class FixedNameSourceFilter implements NameSource
{
    public string $name = "name";

    public function getNewName() : string
    {
        return $this->name;
    }
}

class ConcreteFilter extends Filter
{
    public array $inputs;
    public ?Input\InputData $input_data = null;
    protected Input\Field\Factory $input_factory;
    protected Group $input_group;

    public function __construct(
        SignalGenerator $signal_generator,
        Input\Field\Factory $field_factory,
        $toggle_action_on,
        $toggle_action_off,
        $expand_action,
        $collapse_action,
        $apply_action,
        $reset_action,
        array $inputs,
        array $is_input_rendered,
        bool $is_activated,
        bool $is_expanded
    ) {
        $this->input_factory = $field_factory;
        parent::__construct(
            $signal_generator,
            $field_factory,
            $toggle_action_on,
            $toggle_action_off,
            $expand_action,
            $collapse_action,
            $apply_action,
            $reset_action,
            $inputs,
            $is_input_rendered,
            $is_activated,
            $is_expanded
        );
    }


    public function _extractParamData(ServerRequestInterface $request) : Input\InputData
    {
        return $this->extractParamData($request);
    }


    public function extractParamData(ServerRequestInterface $request) : Input\InputData
    {
        if ($this->input_data !== null) {
            return $this->input_data;
        }

        return parent::extractParamData($request);
    }


    public function setInputs(array $inputs) : void
    {
        $this->input_group = $this->input_factory->group($inputs);
        $this->inputs = $inputs;
    }

    // TODO: DW perhaps, this can be removed
//    public function _getInput(ServerRequestInterface $request)
//    {
//        return $this->getInput($request);
//    }
}

/**
 * Test on filter implementation.
 */
class FilterTest extends ILIAS_UI_TestBase
{
    protected function buildFactory() : Input\Container\Filter\Factory
    {
        return new ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory(
            new SignalGenerator(),
            $this->buildInputFactory()
        );
    }

    protected function buildInputFactory() : Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new SignalGenerator(),
            $df,
            new ILIAS\Refinery\Factory($df, $language),
            $language
        );
    }

    protected function buildButtonFactory() : I\Button\Factory
    {
        return new ILIAS\UI\Implementation\Component\Button\Factory;
    }

    protected function buildGlyphFactory() : I\Symbol\Factory
    {
        return new ILIAS\UI\Implementation\Component\Symbol\Factory();
    }

    protected function buildPopoverFactory() : I\Popover\Factory
    {
        return new ILIAS\UI\Implementation\Component\Popover\Factory(new SignalGenerator());
    }

    protected function buildLegacyFactory() : I\Legacy\Factory
    {
        return new ILIAS\UI\Implementation\Component\Legacy\Factory();
    }

    protected function buildListingFactory() : I\Listing\Factory
    {
        return new ILIAS\UI\Implementation\Component\Listing\Factory;
    }

    public function getUIFactory() : NoUIFactory
    {
        return new WithNoUIFactories(
            $this->buildButtonFactory(),
            $this->buildGlyphFactory(),
            $this->buildPopoverFactory(),
            $this->buildLegacyFactory(),
            $this->buildListingFactory()
        );
    }

    public function buildDataFactory() : Data\Factory
    {
        return new Data\Factory;
    }

    public function test_getInputs() : void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $name_source = new FixedNameSourceFilter();

        $inputs = [$if->text(""), $if->select("", [])];
        $inputs_rendered = [true, true];
        $filter = $f->standard(
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            $inputs,
            $inputs_rendered,
            false,
            false
        );

        $seen_names = [];
        $inputs = $filter->getInputs();

        foreach ($inputs as $input) {
            $name = $input->getName();
            $name_source->name = $name;

            // name is a string
            $this->assertIsString($name);

            // only name is attached
            $input = array_shift($inputs);
            $this->assertEquals($input->withNameFrom($name_source), $input);

            // every name can only be contained once.
            $this->assertNotContains($name, $seen_names);
            $seen_names[] = $name;
        }
    }

    public function test_extractParamData() : void
    {
        $filter = new ConcreteFilter(
            new SignalGenerator(),
            $this->buildInputFactory(),
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            [],
            [],
            false,
            false
        );
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getQueryParams")
            ->willReturn([]);
        $input_data = $filter->_extractParamData($request);
        $this->assertInstanceOf(InputData::class, $input_data);
    }

    public function test_withRequest() : void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $input_data = $this->createMock(InputData::class);

        $df = $this->buildDataFactory();

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

        $filter = new ConcreteFilter(
            new SignalGenerator(),
            $this->buildInputFactory(),
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            [],
            [],
            false,
            false
        );
        $filter->setInputs([$input_1, $input_2]);
        $filter->input_data = $input_data;

        $filter2 = $filter->withRequest($request);

        $this->assertNotSame($filter2, $filter);
        $this->assertInstanceOf(Filter::class, $filter2);
        $this->assertEquals([$input_1, $input_2], $filter2->getInputs());
    }

    public function test_getData() : void
    {
        $df = $this->buildDataFactory();
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method("getQueryParams")
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

        $filter = new ConcreteFilter(
            new SignalGenerator(),
            $this->buildInputFactory(),
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            [],
            [],
            false,
            false
        );
        $filter->setInputs([$input_1, $input_2]);
        $filter = $filter->withRequest($request);
        $this->assertEquals([1, 2], $filter->getData());
    }

    public function test_with_activated() : void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $inputs = [$if->text(""), $if->text("")];
        $inputs_rendered = [true, true];

        $filter = $f->standard(
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            $inputs,
            $inputs_rendered,
            false,
            false
        );
        $filter1 = $filter->withActivated();

        $this->assertFalse($filter->isActivated());
        $this->assertTrue($filter1->isActivated());
    }

    public function test_with_deactivated() : void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $inputs = [$if->text(""), $if->text("")];
        $inputs_rendered = [true, true];

        $filter = $f->standard(
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            $inputs,
            $inputs_rendered,
            true,
            false
        );
        $filter1 = $filter->withDeactivated();

        $this->assertTrue($filter->isActivated());
        $this->assertFalse($filter1->isActivated());
    }

    public function test_with_expanded() : void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $inputs = [$if->text(""), $if->text("")];
        $inputs_rendered = [true, true];

        $filter = $f->standard(
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            $inputs,
            $inputs_rendered,
            false,
            false
        );
        $filter1 = $filter->withExpanded();

        $this->assertFalse($filter->isExpanded());
        $this->assertTrue($filter1->isExpanded());
    }

    public function test_with_collapsed() : void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $inputs = [$if->text(""), $if->text("")];
        $inputs_rendered = [true, true];

        $filter = $f->standard(
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            $inputs,
            $inputs_rendered,
            false,
            true
        );
        $filter1 = $filter->withCollapsed();

        $this->assertTrue($filter->isExpanded());
        $this->assertFalse($filter1->isExpanded());
    }

    /**
     * @return FormInputInternal|mixed|MockObject
     */
    protected function inputMock()
    {
        static $no = 2000;
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
