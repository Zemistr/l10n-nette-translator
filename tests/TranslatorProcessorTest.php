<?php

use l10nNetteTranslator\TranslatorProcessor;
use Nette\InvalidStateException;

class TranslatorProcessorTest extends BaseTest {
	private $texts;

	public function setUp() {
		$this->texts = [];
	}

	public function testCreateHash() {
		/** @var \l10nNetteTranslator\TranslatorProcessor|PHPUnit_Framework_MockObject_MockObject $processor */
		$processor = $this->getMockBuilder('l10nNetteTranslator\TranslatorProcessor')
			->disableOriginalConstructor()
			->getMock();

		$hash = $this->callMethod($processor, 'createHash', ['foo']);

		$this->assertSame('c77a6e90', $hash);
	}

	protected function createTranslatorMock() {
		/** @var \l10nNetteTranslator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10nNetteTranslator\Translator');

		return $mock;
	}

	public function createRequestMockWithGetPostMethod(array $request_data) {
		/** @var \Nette\Http\IRequest|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('Nette\Http\IRequest');

		$mock->expects($this->once())
			->method("getPost")
			->with($this->equalTo(TranslatorProcessor::PARAMETER))
			->willReturn($request_data);

		return $mock;
	}

	public function testGetRequestData() {
		$translator = $this->createTranslatorMock();
		$request = $this->createRequestMockWithGetPostMethod([]);

		$processor = new TranslatorProcessor($translator, $request);

		$request_data = $this->callMethod($processor, 'getRequestData');

		$this->assertArrayHasKey('key', $request_data);
		$this->assertArrayNotHasKey('id', $request_data);

		$this->assertNull($request_data['key']);

		/////////////

		$request = $this->createRequestMockWithGetPostMethod(['key' => 'foo', 'id' => 'bar']);
		$processor = new TranslatorProcessor($translator, $request);
		$request_data = $this->callMethod($processor, 'getRequestData');

		$this->assertArrayHasKey('key', $request_data);
		$this->assertArrayHasKey('id', $request_data);

		$this->assertSame('foo', $request_data['key']);
		$this->assertSame('bar', $request_data['id']);

		/////////////

		$request = $this->createRequestMockWithGetPostMethod(['key' => 'foo']);
		$processor = new TranslatorProcessor($translator, $request);
		$request_data = $this->callMethod($processor, 'getRequestData');

		$this->assertArrayHasKey('key', $request_data);
		$this->assertArrayHasKey('id', $request_data);

		$this->assertSame('foo', $request_data['key']);
		$this->assertSame('c77a6e90', $request_data['id']);
	}

	public function createRequestMock() {
		/** @var \Nette\Http\IRequest|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('Nette\Http\IRequest');

		return $mock;
	}

	public function testGetPayload() {
		$translator = $this->createTranslatorMock();
		$request = $this->createRequestMock();

		$processor = new TranslatorProcessor($translator, $request);

		$payload_property = $this->getProperty($processor, 'payload');
		$payload = $this->callMethod($processor, 'getPayload');

		$this->assertSame($payload_property, $payload);
	}

	public function testInitAction() {
		/** @var \l10nNetteTranslator\TranslatorProcessor|PHPUnit_Framework_MockObject_MockObject $processor */
		$processor = $this->getMockBuilder('l10nNetteTranslator\TranslatorProcessor')
			->disableOriginalConstructor()
			->setMethods(['loadLanguagesAction', 'loadListAction'])
			->getMock();

		$processor->expects($this->once())
			->method("loadLanguagesAction");

		$processor->expects($this->once())
			->method("loadListAction");

		$this->callMethod($processor, 'initAction');
	}

	protected function createTranslatorMockForLoadLanguagesAction($languages_and_plurals, $language_and_plural) {
		/** @var \l10nNetteTranslator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10nNetteTranslator\Translator');

		$mock->expects($this->once())
			->method("getLanguagesAndPlurals")
			->willReturnCallback(
				function () use ($languages_and_plurals) {
					$data = [];

					foreach ($languages_and_plurals as $language_and_plural) {
						list($code, $original_name, $english_name) = $language_and_plural;
						$data[] = $this->createLanguageAndPluralMock($code, $original_name, $english_name);
					}

					return $data;
				}
			);

		$mock->expects($this->once())
			->method("getActiveLanguageAndPlural")
			->willReturnCallback(
				function () use ($language_and_plural) {
					list($code, $plurals_count) = $language_and_plural;

					return $this->createActiveLanguageAndPluralMock($code, $plurals_count);
				}
			);

		return $mock;
	}

	protected function createLanguageAndPluralMock($code, $original_name, $english_name) {
		/** @var \l10n\Language\ILanguage|PHPUnit_Framework_MockObject_MockObject $language */
		$language = $this->getMock('l10n\Language\ILanguage');
		$language->expects($this->once())
			->method("getIso639_1")
			->willReturn($code);

		$language->expects($this->once())
			->method("getOriginalName")
			->willReturn($original_name);

		$language->expects($this->once())
			->method("getEnglishName")
			->willReturn($english_name);

		/** @var \l10nNetteTranslator\LanguageAndPlural|PHPUnit_Framework_MockObject_MockObject $language_and_plural */
		$language_and_plural = $this->getMock('l10nNetteTranslator\LanguageAndPlural');
		$language_and_plural->expects($this->once())
			->method("getLanguage")
			->willReturn($language);

		return $language_and_plural;
	}

	protected function createActiveLanguageAndPluralMock($code, $plurals_count) {
		/** @var \l10n\Language\ILanguage|PHPUnit_Framework_MockObject_MockObject $language */
		$language = $this->getMock('l10n\Language\ILanguage');
		$language->expects($this->once())
			->method("getIso639_1")
			->willReturn($code);

		/** @var \l10n\Plural\IPlural|PHPUnit_Framework_MockObject_MockObject $plural */
		$plural = $this->getMock('l10n\Plural\IPlural');
		$plural->expects($this->once())
			->method("getPluralsCount")
			->willReturn($plurals_count);

		/** @var \l10nNetteTranslator\LanguageAndPlural|PHPUnit_Framework_MockObject_MockObject $language_and_plural */
		$language_and_plural = $this->getMock('l10nNetteTranslator\LanguageAndPlural');
		$language_and_plural->expects($this->once())
			->method("getLanguage")
			->willReturn($language);

		$language_and_plural->expects($this->once())
			->method("getPlural")
			->willReturn($plural);

		return $language_and_plural;
	}

	public function testLoadLanguagesAction() {
		$languages_and_plurals = [
			['fo', 'bdffgar', 'bnamz'],
			['oo', 'rdfgagb', 'znwab']
		];

		$language_and_plural = ['aa', 3];

		$translator = $this->createTranslatorMockForLoadLanguagesAction($languages_and_plurals, $language_and_plural);
		$request = $this->createRequestMock();

		$processor = new TranslatorProcessor($translator, $request);

		$this->callMethod($processor, 'loadLanguagesAction');

		$payload_property = $this->getProperty($processor, 'payload');

		$this->assertArrayHasKey('languages', $payload_property);

		foreach ($languages_and_plurals as $language_and_plural_item) {
			list($code, $original_name, $english_name) = $language_and_plural_item;
			$this->assertArrayHasKey($code, $payload_property['languages']);

			$this->assertArrayHasKey('code', $payload_property['languages'][$code]);
			$this->assertSame($code, $payload_property['languages'][$code]['code']);

			$this->assertArrayHasKey('original_name', $payload_property['languages'][$code]);
			$this->assertSame($original_name, $payload_property['languages'][$code]['original_name']);

			$this->assertArrayHasKey('english_name', $payload_property['languages'][$code]);
			$this->assertSame($english_name, $payload_property['languages'][$code]['english_name']);
		}

		$this->assertArrayHasKey('language', $payload_property);
		$this->assertSame($language_and_plural[0], $payload_property['language']);

		$this->assertArrayHasKey('plurals_count', $payload_property);
		$this->assertSame($language_and_plural[1], $payload_property['plurals_count']);

		$this->assertArrayHasKey('actions', $payload_property);
		$this->assertSame(3, count($payload_property['actions']));

		$this->assertArrayHasKey('buildLanguages', array_flip($payload_property['actions']));
		$this->assertArrayHasKey('setLanguage', array_flip($payload_property['actions']));
		$this->assertArrayHasKey('buildPluralsForm', array_flip($payload_property['actions']));
	}

	protected function createTranslatorMockForLoadListAction($translated, $untranslated) {
		/** @var \l10nNetteTranslator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10nNetteTranslator\Translator');

		$mock->expects($this->once())
			->method("getTranslator")
			->willReturnCallback(
				function () use ($translated, $untranslated) {
					/** @var \l10n\Translator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
					$mock = $this->getMockBuilder('l10n\Translator\Translator')
						->disableOriginalConstructor()
						->getMock();

					$mock->expects($this->once())
						->method("getTranslated")
						->willReturn($translated);

					$mock->expects($this->once())
						->method("getUntranslated")
						->willReturn($untranslated);

					return $mock;
				}
			);

		return $mock;
	}

	public function testLoadListAction() {
		$translated = [
			'foo' => ['foo0', 'foo1', 'foo2'],
			'bar' => ['bar0', 'bar1', 'bar2'],
		];
		$untranslated = [
			'baz' => []
		];

		$translator = $this->createTranslatorMockForLoadListAction($translated, $untranslated);
		$request = $this->createRequestMock();

		$processor = new TranslatorProcessor($translator, $request);

		$this->callMethod($processor, 'loadListAction');

		$payload_property = $this->getProperty($processor, 'payload');

		$this->assertArrayHasKey('texts', $payload_property);

		foreach ($translated + $untranslated as $key => $texts) {
			$hash = $this->callMethod($processor, 'createHash', [$key]);

			$this->assertArrayHasKey($hash, $payload_property['texts']);

			$this->assertArrayHasKey('id', $payload_property['texts'][$hash]);
			$this->assertSame($hash, $payload_property['texts'][$hash]['id']);

			$this->assertArrayHasKey('key', $payload_property['texts'][$hash]);
			$this->assertSame($key, $payload_property['texts'][$hash]['key']);

			$this->assertArrayHasKey('status', $payload_property['texts'][$hash]);
			$this->assertSame(isset($untranslated[$key]) ? 0 : 1, $payload_property['texts'][$hash]['status']);

			$this->assertArrayHasKey('texts', $payload_property['texts'][$hash]);
			$this->assertSame($texts, $payload_property['texts'][$hash]['texts']);
		}

		$this->assertArrayHasKey('actions', $payload_property);
		$this->assertSame(1, count($payload_property['actions']));

		$this->assertArrayHasKey('buildList', array_flip($payload_property['actions']));
	}

	protected function createTranslatorMockForSaveTextAction($plurals_count, $key) {
		/** @var \l10nNetteTranslator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10nNetteTranslator\Translator');

		$mock->expects($this->once())
			->method("getTranslator")
			->willReturnCallback(
				function () use ($plurals_count, $key) {
					/** @var \l10n\Translator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
					$mock = $this->getMockBuilder('l10n\Translator\Translator')
						->disableOriginalConstructor()
						->getMock();

					$mock->expects($this->once())
						->method("removeText")
						->with($this->equalTo($key));

					$mock->expects($this->exactly($plurals_count))
						->method("setText")
						->with($this->equalTo($key))
						->willReturnCallback(
							function ($key, $text, $plural) {
								$this->texts[$plural] = $text;
							}
						);

					return $mock;
				}
			);

		$mock->expects($this->once())
			->method("getActiveLanguageAndPlural")
			->willReturnCallback(
				function () use ($plurals_count) {
					/** @var \l10n\Plural\IPlural|PHPUnit_Framework_MockObject_MockObject $plural */
					$plural = $this->getMock('l10n\Plural\IPlural');
					$plural->expects($this->once())
						->method("getPluralsCount")
						->willReturn($plurals_count);

					/** @var \l10nNetteTranslator\LanguageAndPlural|PHPUnit_Framework_MockObject_MockObject $language_and_plural */
					$language_and_plural = $this->getMock('l10nNetteTranslator\LanguageAndPlural');

					$language_and_plural->expects($this->once())
						->method("getPlural")
						->willReturn($plural);

					return $language_and_plural;
				}
			);

		return $mock;
	}

	public function testSaveTextAction() {
		$plurals_count = 3;
		$key = 'fo';

		$translator = $this->createTranslatorMockForSaveTextAction($plurals_count, $key);
		$request = $this->createRequestMock();

		/** @var \l10nNetteTranslator\TranslatorProcessor|PHPUnit_Framework_MockObject_MockObject $processor */
		$processor = $this->getMockBuilder('l10nNetteTranslator\TranslatorProcessor')
			->setConstructorArgs([$translator, $request])
			->setMethods(['loadListAction', 'createHash'])
			->getMock();

		$processor->expects($this->once())
			->method('loadListAction');

		$processor->expects($this->once())
			->method('createHash')
			->willReturnArgument(0);

		$request_data = [
			'key'   => $key,
			'texts' => [
				'singular', 'plural 1', 'plural 2', 'plural 3', 'plural 4'
			]
		];

		$this->callMethod($processor, 'saveTextAction', [$request_data]);

		$payload = $this->callMethod($processor, 'getPayload');

		$this->assertSame(array_slice($request_data['texts'], 0, 3), $this->texts);

		$this->assertArrayHasKey('select', $payload);
		$this->assertSame($key, $payload['select']);

		$this->assertArrayHasKey('actions', $payload);
		$this->assertSame(1, count($payload['actions']));

		$this->assertArrayHasKey('selectItem', array_flip($payload['actions']));
	}

	protected function createTranslatorMockForRemoveTextAction($key) {
		/** @var \l10nNetteTranslator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10nNetteTranslator\Translator');

		$mock->expects($this->once())
			->method("getTranslator")
			->willReturnCallback(
				function () use ($key) {
					/** @var \l10n\Translator\Translator|PHPUnit_Framework_MockObject_MockObject $mock */
					$mock = $this->getMockBuilder('l10n\Translator\Translator')
						->disableOriginalConstructor()
						->getMock();

					$mock->expects($this->once())
						->method("removeText")
						->with($this->equalTo($key));

					return $mock;
				}
			);

		return $mock;
	}

	public function testRemoveTextAction() {
		$key = 'fo';

		$translator = $this->createTranslatorMockForRemoveTextAction($key);
		$request = $this->createRequestMock();

		/** @var \l10nNetteTranslator\TranslatorProcessor|PHPUnit_Framework_MockObject_MockObject $processor */
		$processor = $this->getMockBuilder('l10nNetteTranslator\TranslatorProcessor')
			->setConstructorArgs([$translator, $request])
			->setMethods(['loadListAction'])
			->getMock();

		$processor->expects($this->once())
			->method('loadListAction');

		$request_data = [
			'key' => $key
		];

		$this->callMethod($processor, 'removeTextAction', [$request_data]);

		$payload = $this->callMethod($processor, 'getPayload');

		$this->assertArrayHasKey('actions', $payload);
		$this->assertSame(1, count($payload['actions']));

		$this->assertArrayHasKey('clean', array_flip($payload['actions']));
	}

	public function testCallActionByRequest() {
		/** @var \l10nNetteTranslator\TranslatorProcessor|PHPUnit_Framework_MockObject_MockObject $processor */
		$processor = $this->getMockBuilder('l10nNetteTranslator\TranslatorProcessor')
			->disableOriginalConstructor()
			->setMethods(['okAction'])
			->getMock();

		$processor->expects($this->once())
			->method('okAction');

		$request_data = ['action' => 'ok'];
		$this->callMethod($processor, 'callActionByRequest', [$request_data]);

		$request_data = ['action' => 'wrong'];
		$this->setExpectedException('Nette\InvalidStateException');
		$this->callMethod($processor, 'callActionByRequest', [$request_data]);
	}

	public function testRunWithoutAction() {
		/** @var \l10nNetteTranslator\TranslatorProcessor|PHPUnit_Framework_MockObject_MockObject $processor */
		$processor = $this->getMockBuilder('l10nNetteTranslator\TranslatorProcessor')
			->disableOriginalConstructor()
			->setMethods(['getRequestData'])
			->getMock();

		$processor->expects($this->once())
			->method('getRequestData')
			->willReturn(['action' => '']);

		$this->assertNull($processor->run());
	}

	public function testRunWithAction() {
		/** @var \l10nNetteTranslator\TranslatorProcessor|PHPUnit_Framework_MockObject_MockObject $processor */
		$processor = $this->getMockBuilder('l10nNetteTranslator\TranslatorProcessor')
			->disableOriginalConstructor()
			->setMethods(['getRequestData', 'callActionByRequest'])
			->getMock();

		$request_data = [
			'action'   => 'test',
			'language' => null
		];

		$processor->expects($this->once())
			->method('getRequestData')
			->willReturn($request_data);

		$processor->expects($this->once())
			->method('callActionByRequest')
			->with($this->equalTo($request_data));

		$this->assertInstanceOf('Nette\Application\Responses\JsonResponse', $processor->run());
	}

	public function testRunWithActionAndException() {
		/** @var \l10nNetteTranslator\TranslatorProcessor|PHPUnit_Framework_MockObject_MockObject $processor */
		$processor = $this->getMockBuilder('l10nNetteTranslator\TranslatorProcessor')
			->disableOriginalConstructor()
			->setMethods(['getRequestData', 'callActionByRequest'])
			->getMock();

		$request_data = [
			'action'   => 'test',
			'language' => null
		];

		$processor->expects($this->once())
			->method('getRequestData')
			->willReturn($request_data);

		$message = 'Test message';

		$processor->expects($this->once())
			->method('callActionByRequest')
			->willThrowException(new InvalidStateException($message));

		/** @var \Nette\Application\Responses\JsonResponse $result */
		$result = $processor->run();

		$this->assertInstanceOf('Nette\Application\Responses\JsonResponse', $result);

		$payload = $result->getPayload();

		$this->assertArrayHasKey('message', $payload);
		$this->assertSame($message, $payload['message']);
	}

	public function testRunWithLanguage() {
		$request_data = [
			'action'   => 'test',
			'language' => 'cs'
		];

		/** @var \l10nNetteTranslator\Translator|PHPUnit_Framework_MockObject_MockObject $translator */
		$translator = $this->getMockBuilder('l10nNetteTranslator\Translator')
			->setMethods(['setActiveLanguageCode'])
			->getMock();

		$translator->expects($this->once())
			->method('setActiveLanguageCode')
			->with($this->equalTo($request_data['language']));

		$request = $this->createRequestMock();

		/** @var \l10nNetteTranslator\TranslatorProcessor|PHPUnit_Framework_MockObject_MockObject $processor */
		$processor = $this->getMockBuilder('l10nNetteTranslator\TranslatorProcessor')
			->setConstructorArgs([$translator, $request])
			->setMethods(['getRequestData', 'callActionByRequest'])
			->getMock();

		$processor->expects($this->once())
			->method('getRequestData')
			->willReturn($request_data);

		$processor->expects($this->once())
			->method('callActionByRequest');

		/** @var \Nette\Application\Responses\JsonResponse $result */
		$result = $processor->run();

		$this->assertInstanceOf('Nette\Application\Responses\JsonResponse', $result);
	}

	public function testRunWithLanguageAndException() {
		$request_data = [
			'action'   => 'test',
			'language' => 'cs'
		];

		/** @var \l10nNetteTranslator\Translator|PHPUnit_Framework_MockObject_MockObject $translator */
		$translator = $this->getMockBuilder('l10nNetteTranslator\Translator')
			->setMethods(['setActiveLanguageCode'])
			->getMock();

		$message = 'Test message';

		$translator->expects($this->once())
			->method('setActiveLanguageCode')
			->willThrowException(new InvalidStateException($message));

		$request = $this->createRequestMock();

		/** @var \l10nNetteTranslator\TranslatorProcessor|PHPUnit_Framework_MockObject_MockObject $processor */
		$processor = $this->getMockBuilder('l10nNetteTranslator\TranslatorProcessor')
			->setConstructorArgs([$translator, $request])
			->setMethods(['getRequestData', 'callActionByRequest'])
			->getMock();

		$processor->expects($this->once())
			->method('getRequestData')
			->willReturn($request_data);

		/** @var \Nette\Application\Responses\JsonResponse $result */
		$result = $processor->run();

		$this->assertInstanceOf('Nette\Application\Responses\JsonResponse', $result);

		$payload = $result->getPayload();

		$this->assertArrayHasKey('message', $payload);
		$this->assertSame($message, $payload['message']);
	}
}
