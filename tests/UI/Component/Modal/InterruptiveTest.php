<?php

declare(strict_types=1);

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

require_once(__DIR__ . '/ModalBase.php');

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Tests on implementation for the interruptive modal
 */
class InterruptiveTest extends ModalBase
{
    public function test_get_title(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
        $this->assertEquals('myTitle', $interruptive->getTitle());
    }

    public function test_get_message(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
        $this->assertEquals('myMessage', $interruptive->getMessage());
    }

    public function test_get_form_action(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
        $this->assertEquals('myFormAction', $interruptive->getFormAction());
    }

    public function test_get_affected_items(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
        $items = [$this->getInterruptiveItem(), $this->getInterruptiveItem()];
        $interruptive = $interruptive->withAffectedItems($items);
        $this->assertEquals($items, $interruptive->getAffectedItems());
    }

    public function test_with_form_action(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
        $interruptive2 = $interruptive->withFormAction('myFormAction2');
        $this->assertEquals('myFormAction', $interruptive->getFormAction());
        $this->assertEquals('myFormAction2', $interruptive2->getFormAction());
    }

    public function test_with_affected_items(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('myTitle', 'myMessage', 'myFormAction');
        $items = [$this->getInterruptiveItem(), $this->getInterruptiveItem()];
        $interruptive2 = $interruptive->withAffectedItems($items);
        $this->assertEquals(0, count($interruptive->getAffectedItems()));
        $this->assertEquals($items, $interruptive2->getAffectedItems());
    }

    public function test_simple_rendering(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('Title', 'Message', 'myAction.php');
        $expected = $this->normalizeHTML($this->getExpectedHTML());
        $actual = $this->normalizeHTML($this->getDefaultRenderer()->render($interruptive));
        $this->assertEquals($expected, $actual);
    }

    public function test_rendering_with_items(): void
    {
        $interruptive = $this->getModalFactory()->interruptive('Title', 'Message', 'myAction.php');
        $items = [
            $this->getKeyValueInterruptiveItem('keyvalue1'),
            $this->getStandardInterruptiveItem('standard1'),
            $this->getKeyValueInterruptiveItem('keyvalue2'),
            $this->getKeyValueInterruptiveItem('keyvalue3'),
            $this->getStandardInterruptiveItem('standard2')
        ];
        $interruptive = $interruptive->withAffectedItems($items);
        $expected = $this->normalizeHTML($this->getExpectedHTML(true));
        $actual = $this->normalizeHTML($this->getDefaultRenderer(null, $items)->render($interruptive));
        $this->assertEquals($expected, $actual);
    }

    protected function getInterruptiveItem(): InterruptiveItemMock
    {
        return new InterruptiveItemMock();
    }

    protected function getStandardInterruptiveItem(string $canonical_name): StandardItemMock
    {
        return new StandardItemMock($canonical_name);
    }

    protected function getKeyValueInterruptiveItem(string $canonical_name): KeyValueItemMock
    {
        return new KeyValueItemMock($canonical_name);
    }

    protected function getExpectedHTML(bool $with_items = false): string
    {
        $expected_start = <<<EOT
<div class="modal fade c-modal--interruptive" tabindex="-1" role="dialog" id="id_1">
	<div class="modal-dialog" role="document">
		<form action="myAction.php" method="POST">
			<div class="modal-content">
				<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true"></span></button><span class="modal-title">Title</span>
				</div>
				<div class="modal-body">
					<div class="alert alert-warning c-modal--interruptive__message" role="alert">Message</div>
EOT;
        $expected_items = <<<EOT
					<div class="c-modal--interruptive__items">
						<table>
							standard1
							standard2
						</table>
					</div>
					<div class="c-modal--interruptive__items">
						<dl>
							keyvalue1
							keyvalue2
							keyvalue3
						</dl>
					</div>
EOT;
        $expected_end = <<<EOT
				</div>
				<div class="modal-footer">
					<input type="submit" class="btn btn-primary" value="delete" name="cmd[delete]">
					<button class="btn btn-default" data-dismiss="modal">cancel</button>
				</div>
			</div>
		</form>
	</div>
</div>
EOT;
        if ($with_items) {
            return $expected_start . $expected_items . $expected_end;
        }
        return $expected_start . $expected_end;
    }


    public function testLabels(): void
    {
        $action_label = 'actionlabel';
        $cancel_label = 'cancellabel';
        $interruptive = $this->getModalFactory()->interruptive('Title', 'Message', 'someaction')
            ->withActionButtonLabel($action_label)
            ->withCancelButtonLabel($cancel_label);

        $this->assertEquals(
            $action_label,
            $interruptive->getActionButtonLabel()
        );
        $this->assertEquals(
            $cancel_label,
            $interruptive->getCancelButtonLabel()
        );
    }
}

class InterruptiveItemMock implements C\Modal\InterruptiveItem\InterruptiveItem
{
    protected string $canonical_name;

    public function __construct(string $canonical_name = '')
    {
        $this->canonical_name = $canonical_name;
    }

    public function getId(): string
    {
        return '1';
    }

    public function getCanonicalName(): string
    {
        return $this->canonical_name ?: 'InterruptiveItem';
    }
}

class StandardItemMock extends InterruptiveItemMock implements C\Modal\InterruptiveItem\Standard
{
    public function getTitle(): string
    {
        return 'title';
    }

    public function getDescription(): string
    {
        return 'description';
    }

    public function getIcon(): C\Image\Image
    {
        return new I\Component\Image\Image(C\Image\Image::STANDARD, '', '');
    }
}

class KeyValueItemMock extends InterruptiveItemMock implements C\Modal\InterruptiveItem\KeyValue
{
    public function getKey(): string
    {
        return 'key';
    }

    public function getValue(): string
    {
        return 'value';
    }
}
