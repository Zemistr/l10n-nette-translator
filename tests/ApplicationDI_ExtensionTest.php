<?php
use l10nNetteTranslator\ApplicationDI\Extension;

class ApplicationDI_ExtensionTest extends BaseTest {
	private $definitions = [];
	private $code = '';

	public function setUp() {
		$this->definitions = [];
		$this->code = '';
	}

	public function createCompilerMock() {
		/** @var \Nette\DI\Compiler|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMockBuilder('Nette\DI\Compiler')
			->setMethods(['getContainerBuilder'])
			->getMock();

		$mock->expects($this->once())
			->method('getContainerBuilder')
			->willReturnCallback([$this, 'createContainerBuilderMock']);

		return $mock;
	}

	public function createContainerBuilderMock() {
		/** @var \Nette\DI\ContainerBuilder|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMockBuilder('Nette\DI\ContainerBuilder')
			->setMethods(['addDefinition'])
			->getMock();

		$mock->expects($this->any())
			->method('addDefinition')
			->willReturnCallback([$this, 'createServiceDefinitionMock']
			);

		return $mock;
	}

	public function createServiceDefinitionMock() {
		/** @var \Nette\DI\ServiceDefinition|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMockBuilder('Nette\DI\ServiceDefinition')
			->setMethods(['setClass', 'addSetup'])
			->getMock();

		$mock->expects($this->once())
			->method('setClass');

		$mock->expects($this->any())
			->method('addSetup')
			->willReturnCallback(
				function ($method, $arguments) {
					$this->definitions[$method][] = $arguments;
				}
			);

		return $mock;
	}

	public function providerForTestLoadConfigurationLanguagesException() {
		$data = [];
		$data[] = ['---'];
		$data[] = [[]];
		$data[] = [0];
		$data[] = [1];
		$data[] = [true];
		$data[] = [false];
		$data[] = [''];
		$data[] = ['foo'];
		$data[] = [new stdClass()];

		return $data;
	}

	/**
	 * @dataProvider providerForTestLoadConfigurationLanguagesException
	 * @param $data
	 */
	public function testLoadConfigurationLanguagesException($data) {
		$this->setExpectedException('Nette\InvalidStateException', 'Languages must be set, must be array and can\'t be empty');

		$extension = new Extension();

		if ($data != '---') {
			$extension->setConfig(['languages' => $data]);
		}

		$extension->loadConfiguration();
	}

	public function testLoadConfiguration() {
		$config = [
			'languages' => [
				[
					'lang'   => 'l10n\Language\CzechLanguage',
					'plural' => 'l10n\Plural\PluralRule8',
				],
				[
					'lang' => 'l10n\Language\EnglishLanguage'
				]
			]
		];

		$extension = new Extension();
		$extension->setCompiler($this->createCompilerMock(), null);
		$extension->setConfig($config);
		$extension->loadConfiguration();

		$this->assertArrayHasKey('addLanguageAndPlural', $this->definitions);
		$this->assertCount(2, $this->definitions['addLanguageAndPlural']);

		foreach ($this->definitions['addLanguageAndPlural'] as $i => $definition_arguments) {
			$this->assertCount(3, $definition_arguments);

			$config_section = $config['languages'][$i];

			$this->assertInstanceOf('Nette\PhpGenerator\PhpLiteral', $definition_arguments[0]);
			$this->assertSame('new ' . $config_section['lang'], "$definition_arguments[0]");

			$this->assertInstanceOf('Nette\PhpGenerator\PhpLiteral', $definition_arguments[1]);

			if (isset($config_section['plural'])) {
				$this->assertSame('new ' . $config_section['plural'], "$definition_arguments[1]");
			}
			else {
				$this->assertSame('new ' . $config_section['lang'], "$definition_arguments[1]");
			}

			$this->assertInternalType('bool', $definition_arguments[2]);
			$this->assertFalse($definition_arguments[2]);
		}
	}

	public function testLoadConfigurationWithLangExeption() {
		$config = ['languages' => [[]]];

		$extension = new Extension();
		$extension->setCompiler($this->createCompilerMock(), null);
		$extension->setConfig($config);

		$this->setExpectedException('Nette\InvalidStateException');
		$extension->loadConfiguration();
	}

	public function testLoadConfigurationWithStorage() {
		$config = [
			'languages' => [
				[
					'lang' => 'l10n\Language\CzechLanguage',
				]
			],
			'storage'   => 'foo'
		];

		$extension = new Extension();
		$extension->setCompiler($this->createCompilerMock(), null);
		$extension->setConfig($config);
		$extension->loadConfiguration();

		$this->assertArrayHasKey('setStorage', $this->definitions);
		$this->assertCount(1, $this->definitions['setStorage']);
		$this->assertArrayHasKey(0, $this->definitions['setStorage']);
		$this->assertArrayHasKey(0, $this->definitions['setStorage'][0]);
		$this->assertSame($config['storage'], $this->definitions['setStorage'][0][0]);
	}

	public function createClassTypeMock() {
		/** @var \Nette\PhpGenerator\ClassType|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMockBuilder('Nette\PhpGenerator\ClassType')
			->setMethods(['getMethod'])
			->getMock();

		$mock->expects($this->once())
			->method('getMethod')
			->willReturnCallback([$this, 'createMethodMock']);

		return $mock;
	}

	public function createMethodMock() {
		/** @var \Nette\PhpGenerator\Method|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMockBuilder('Nette\PhpGenerator\Method')
			->setMethods(['addBody'])
			->getMock();

		$mock->expects($this->exactly(6))
			->method('addBody')
			->willReturnCallback(
				function ($argument) {
					$this->code .= "$argument\n";
				}
			);

		return $mock;
	}

	public function testAfterCompile() {
		$extension = new Extension();
		$extension->afterCompile($this->createClassTypeMock());

		$this->assertContains('tracy.bar', $this->code);
		$this->assertContains('addPanel', $this->code);
		$this->assertContains('translator.panel', $this->code);
		$this->assertContains('->run()', $this->code);
	}
}
