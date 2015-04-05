<?php

class AutoloaderTest extends PHPUnit_Framework_TestCase {
	public function filesProvider() {
		$data = [];
		$path = __DIR__ . '/../src/l10nNetteTranslator/';
		$files = @glob($path . '{*/*,*}.php', GLOB_BRACE) ?: [];

		foreach ($files as $file) {
			$file = str_replace($path, '', $file);
			$file = str_replace('/', '\\', $file);
			$file = substr($file, 0, -4);

			$data[] = [
				"l10nNetteTranslator\\$file",
				strpos($file, '\\I') !== false
			];
		}

		return $data;
	}

	/**
	 * @dataProvider filesProvider
	 * @param $class
	 * @param $is_interface
	 */
	public function testLoad($class, $is_interface) {
		if ($is_interface) {
			$this->assertTrue(interface_exists($class), "Interface '$class' not found");
		}
		else {
			$this->assertTrue(class_exists($class), "Class '$class' not found");
		}
	}
}
