<?php
use l10nNetteTranslator\Panel;

class PanelTest extends PHPUnit_Framework_TestCase {
	protected function createTranslatorMock() {
		/** @var l10nNetteTranslator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10nNetteTranslator\Translator');
		$mock->expects($this->once())
			->method('getActiveLanguageAndPlural')
			->willReturnCallback(
				function () {
					$mock = $this->getMock('l10nNetteTranslator\LanguageAndPlural');
					$mock->expects($this->once())
						->method('getLanguage')
						->willReturnCallback(
							function () {
								$mock = $this->getMock('l10n\Language\ILanguage');
								$mock->expects($this->once())
									->method('getIso639_1');

								return $mock;
							}
						);

					return $mock;
				}
			);

		return $mock;
	}

	public function testPanel() {
		$translator = $this->createTranslatorMock();
		$panel = new Panel($translator);

		$this->assertInstanceOf('Tracy\IBarPanel', $panel);
		$this->assertSame('l10nNetteTranslator\Panel', $panel->getId());
		$this->assertInternalType('string', $panel->getPanel());
		$this->assertInternalType('string', $panel->getTab());
	}
}
