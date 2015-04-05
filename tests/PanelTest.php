<?php
use l10nNetteTranslator\Panel;

class PanelTest extends PHPUnit_Framework_TestCase {
	public function testPanel() {
		$panel = new Panel();

		$this->assertInstanceOf('Tracy\IBarPanel', $panel);
		$this->assertSame('l10nNetteTranslator\Panel', $panel->getId());
		$this->assertInternalType('string', $panel->getPanel());
		$this->assertInternalType('string', $panel->getTab());
	}
}
