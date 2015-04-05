<?php
use l10nNetteTranslator\Storage\SimpleNetteStorage;

class Storage_SimpleNetteStorageTest extends BaseTest {
	private $untranslated = [];
	private $translated = [];

	public function setUp() {
		$this->untranslated = [];
		$this->translated = [];
	}

	public function createStorageMock() {
		/** @var \Nette\Caching\IStorage|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('Nette\Caching\IStorage');

		return $mock;
	}

	public function testConstruct() {
		$storage = $this->createStorageMock();
		$nette_storage = new SimpleNetteStorage($storage);

		$storage_property = $this->getProperty($nette_storage, 'storage');

		$this->assertSame($storage, $storage_property);
	}

	public function createTranslatorMock() {
		/** @var \l10nNetteTranslator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10nNetteTranslator\Translator');

		return $mock;
	}

	public function testSetAndGetTranslator() {
		$storage = $this->createStorageMock();
		$nette_storage = new SimpleNetteStorage($storage);

		$translator = $this->createTranslatorMock();
		$nette_storage->setTranslator($translator);

		$this->assertSame($translator, $nette_storage->getTranslator());
	}

	public function testGetTranslatorWithException() {
		$storage = $this->createStorageMock();
		$nette_storage = new SimpleNetteStorage($storage);

		$this->setExpectedException('Nette\InvalidStateException', 'Translator is not set');
		$nette_storage->getTranslator();
	}

	public function createTranslatorMockForGetLanguage($return) {
		/** @var \l10nNetteTranslator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10nNetteTranslator\Translator');

		$mock->expects($this->once())
			->method('getActiveLanguageAndPlural')
			->willReturn($this->createLanguageAndPluralMockForGetLanguage($return));

		return $mock;
	}

	public function createLanguageAndPluralMockForGetLanguage($return) {
		/** @var \l10nNetteTranslator\LanguageAndPlural|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10nNetteTranslator\LanguageAndPlural');

		$mock->expects($this->once())
			->method('getLanguage')
			->willReturn($return);

		return $mock;
	}

	public function testGetLanguage() {
		/** @var \l10nNetteTranslator\Storage\SimpleNetteStorage|PHPUnit_Framework_MockObject_MockObject $nette_storage */
		$nette_storage = $this->getMockBuilder('l10nNetteTranslator\Storage\SimpleNetteStorage')
			->disableOriginalConstructor()
			->setMethods(['getTranslator'])
			->getMock();

		$return = 'something';

		$nette_storage->expects($this->once())
			->method('getTranslator')
			->willReturn($this->createTranslatorMockForGetLanguage($return));

		$method_return = $this->callMethod($nette_storage, 'getLanguage');

		$this->assertSame($return, $method_return);
	}

	public function createStorageMockForLoad($code, $return) {
		/** @var \Nette\Caching\IStorage|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('Nette\Caching\IStorage');

		$mock->expects($this->once())
			->method('read')
			->with($this->equalTo($code))
			->willReturn($return);

		return $mock;
	}

	public function createLanguageMock($code) {
		/** @var \l10n\Language\ILanguage|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10n\Language\ILanguage');

		$mock->expects($this->once())
			->method('getIso639_1')
			->willReturn($code);

		return $mock;
	}

	public function createTranslatorMockForLoad() {
		/** @var \l10n\Translator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMockBuilder('l10n\Translator\Translator')
			->disableOriginalConstructor()
			->setMethods(['setUntranslated', 'setText'])
			->getMock();

		$mock->expects($this->any())
			->method('setUntranslated')
			->with($this->isType('string'), $this->isType('int'))
			->willReturnCallback(
				function ($key, $plural) {
					$this->untranslated[$key][$plural] = true;
				}
			);

		$mock->expects($this->any())
			->method('setText')
			->with($this->isType('string'), $this->isType('string'), $this->isType('int'))
			->willReturnCallback(
				function ($key, $text, $plural) {
					$this->translated[$key][$plural] = $text;
				}
			);

		return $mock;
	}

	public function testLoad() {
		$code = 'cs';
		$return = [
			'untranslated' => [
				'foo' => ['bar', 'baz']
			],
			'translated'   => [
				'foo'  => ['bar', 'baz'],
				'foo2' => ['bar2', 'baz2']
			]
		];

		$storage = $this->createStorageMockForLoad($code, $return);

		/** @var \l10nNetteTranslator\Storage\SimpleNetteStorage|PHPUnit_Framework_MockObject_MockObject $nette_storage */
		$nette_storage = $this->getMockBuilder('l10nNetteTranslator\Storage\SimpleNetteStorage')
			->setConstructorArgs([$storage])
			->setMethods(['getLanguage'])
			->getMock();

		$nette_storage->expects($this->once())
			->method('getLanguage')
			->willReturn($this->createLanguageMock($code));

		$nette_storage->load($this->createTranslatorMockForLoad());

		foreach ($return['untranslated'] as $key => $texts) {
			foreach ($texts as $plural => $_) {
				$this->assertArrayHasKey($key, $this->untranslated);
				$this->assertArrayHasKey($plural, $this->untranslated[$key]);
				$this->assertTrue($this->untranslated[$key][$plural]);
			}
		}

		foreach ($return['translated'] as $key => $texts) {
			foreach ($texts as $plural => $text) {
				$this->assertArrayHasKey($key, $this->translated);
				$this->assertArrayHasKey($plural, $this->translated[$key]);
				$this->assertSame($text, $this->translated[$key][$plural]);
			}
		}
	}

	public function createStorageMockForSave($code, $return) {
		/** @var \Nette\Caching\IStorage|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('Nette\Caching\IStorage');

		$mock->expects($this->once())
			->method('write')
			->with($this->equalTo($code), $this->equalTo($return));

		return $mock;
	}

	public function createTranslatorMockForSave($translated, $untranslated) {
		/** @var \l10n\Translator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMockBuilder('l10n\Translator\Translator')
			->disableOriginalConstructor()
			->setMethods(['getTranslated', 'getUntranslated'])
			->getMock();

		$mock->expects($this->any())
			->method('getTranslated')
			->willReturn($translated);

		$mock->expects($this->any())
			->method('getUntranslated')
			->willReturn($untranslated);

		return $mock;
	}

	public function testSave() {
		$code = 'cs';
		$return = [
			'untranslated' => [
				'foo' => ['bar' => true, 'baz' => true]
			],
			'translated'   => [
				'foo'  => ['bar', 'baz'],
				'foo2' => ['bar2', 'baz2']
			]
		];

		$storage = $this->createStorageMockForSave($code, $return);

		/** @var \l10nNetteTranslator\Storage\SimpleNetteStorage|PHPUnit_Framework_MockObject_MockObject $nette_storage */
		$nette_storage = $this->getMockBuilder('l10nNetteTranslator\Storage\SimpleNetteStorage')
			->setConstructorArgs([$storage])
			->setMethods(['getLanguage'])
			->getMock();

		$nette_storage->expects($this->once())
			->method('getLanguage')
			->willReturn($this->createLanguageMock($code));

		$nette_storage->save($this->createTranslatorMockForSave($return['translated'], $return['untranslated']));
	}
}
