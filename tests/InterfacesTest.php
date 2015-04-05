<?php
class InterfacesTest extends PHPUnit_Framework_TestCase {
	/**
	 * @covers \l10nNetteTranslator\Storage\IStorage
	 */
	public function testIStorage() {
		$mock = $this->getMock('l10nNetteTranslator\Storage\IStorage');

		$this->assertInstanceOf('l10nNetteTranslator\Storage\IStorage', $mock);
		$this->assertInstanceOf('l10n\Translator\IStorage', $mock);
		$this->assertTrue(method_exists($mock, 'load'), 'Method "load" must exists');
		$this->assertTrue(method_exists($mock, 'save'), 'Method "save" must exists');
		$this->assertTrue(method_exists($mock, 'setTranslator'), 'Method "setTranslator" must exists');
		$this->assertTrue(method_exists($mock, 'getTranslator'), 'Method "getTranslator" must exists');
	}
}
