<?php

class BaseTest extends PHPUnit_Framework_TestCase {
	protected function callMethod($class, $method, array $arguments = []) {
		$reflection = new ReflectionMethod(get_class($class), $method);
		$reflection->setAccessible(true);

		return $reflection->invokeArgs($class, $arguments);
	}

	protected function getProperty($class, $property) {
		$reflection = new ReflectionProperty(get_class($class), $property);
		$reflection->setAccessible(true);

		return $reflection->getValue($class);
	}

	protected function setProperty($class, $property, $value) {
		$reflection = new ReflectionProperty(get_class($class), $property);
		$reflection->setAccessible(true);
		$reflection->setValue($class, $value);
	}
}
