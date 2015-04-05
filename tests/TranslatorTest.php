<?php
use l10n\Language\ILanguage;
use l10n\Plural\IPlural;
use l10nNetteTranslator\LanguageAndPlural;
use l10nNetteTranslator\Translator;

interface ILanguageWithPlural extends ILanguage, IPlural {
}

class TranslatorTest extends BaseTest {
	public function createLanguageMock($code) {
		/** @var \l10n\Language\ILanguage|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10n\Language\ILanguage');

		$mock->expects($this->once())
			->method("getIso639_1")
			->willReturn($code);

		return $mock;
	}

	public function createLanguageWithPluralMock($code) {
		/** @var ILanguageWithPlural|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('ILanguageWithPlural');

		$mock->expects($this->once())
			->method("getIso639_1")
			->willReturn($code);

		return $mock;
	}

	public function createStorageMock($add_methods = false) {
		/** @var \l10nNetteTranslator\Storage\IStorage|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10nNetteTranslator\Storage\IStorage');

		if ($add_methods) {
			$mock->expects($this->once())
				->method("load")
				->with($this->isInstanceOf('l10n\Translator\Translator'));

			$mock->expects($this->once())
				->method("save")
				->with($this->isInstanceOf('l10n\Translator\Translator'));

			$mock->expects($this->once())
				->method("setTranslator")
				->with($this->isInstanceOf('l10nNetteTranslator\Translator'));
		}

		return $mock;
	}

	public function createPluralMock() {
		/** @var \l10n\Plural\IPlural|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10n\Plural\IPlural');

		return $mock;
	}

	public function testTranslatorIsInstanceOfNetteLocalizationITranslator() {
		$this->assertInstanceOf('Nette\Localization\ITranslator', new Translator());
	}

	public function testTestLanguageCodeWithException() {
		$translator = new Translator();

		$this->setExpectedException('Nette\InvalidStateException');
		$this->callMethod($translator, 'testLanguageCode', ['foo']);
	}

	public function testTestLanguageCodeIsOk() {
		$translator = new Translator();

		$this->setProperty($translator, 'languages_and_plurals', ['foo' => true]);
		$this->callMethod($translator, 'testLanguageCode', ['foo']);
	}

	private function helperForTestAddLanguageAndPlural(Translator $translator, $language_code, $default) {
		$language = $this->createLanguageMock($language_code);
		$plural = $this->createPluralMock();

		$translator->addLanguageAndPlural($language, $plural, $default);

		$languages = $this->getProperty($translator, 'languages_and_plurals');

		$this->assertArrayHasKey($language_code, $languages);
		$this->assertInstanceOf('l10nNetteTranslator\LanguageAndPlural', $languages[$language_code]);
	}

	public function testAddLanguageAndPlural() {
		$translator = new Translator();

		$this->helperForTestAddLanguageAndPlural($translator, 'cs', false);
		$this->assertSame('cs', $this->getProperty($translator, 'active_language_code'));

		$this->helperForTestAddLanguageAndPlural($translator, 'sk', false);
		$this->assertSame('cs', $this->getProperty($translator, 'active_language_code'));

		$this->helperForTestAddLanguageAndPlural($translator, 'en', true);
		$this->assertSame('en', $this->getProperty($translator, 'active_language_code'));

		$this->helperForTestAddLanguageAndPlural($translator, 'xx', false);
		$this->assertSame('en', $this->getProperty($translator, 'active_language_code'));
	}

	public function testSetActiveLanguageCodeWithException() {
		$translator = new Translator();

		$this->setExpectedException('Nette\InvalidStateException');
		$translator->setActiveLanguageCode('cs');
	}

	public function testSetActiveLanguageCode() {
		$translator = new Translator();
		$this->setProperty($translator, 'languages_and_plurals', ['cs' => true, 'en' => true]);

		$translator->setActiveLanguageCode('cs');
		$this->assertSame('cs', $this->getProperty($translator, 'active_language_code'));

		$translator->setActiveLanguageCode('en');
		$this->assertSame('en', $this->getProperty($translator, 'active_language_code'));
	}

	public function testGetLanguageByCodeWithException() {
		$translator = new Translator();

		$this->setExpectedException('Nette\InvalidStateException');
		$translator->getLanguageAndPluralByCode('cs');
	}

	public function testGetLanguageAndPluralByCode() {
		$translator = new Translator();
		$this->setProperty($translator, 'languages_and_plurals', ['cs' => true, 'en' => true]);

		$this->assertSame(true, $translator->getLanguageAndPluralByCode('cs'));
		$this->assertSame(true, $translator->getLanguageAndPluralByCode('en'));
	}

	public function testGetActiveLanguageAndPluralWithException() {
		$translator = new Translator();

		$this->setExpectedException('Nette\InvalidStateException');
		$translator->getActiveLanguageAndPlural();
	}

	public function testGetActiveAndPluralLanguage() {
		$translator = new Translator();
		$this->setProperty($translator, 'languages_and_plurals', ['cs' => true, 'en' => true]);

		$this->setProperty($translator, 'active_language_code', 'cs');
		$this->assertSame(true, $translator->getActiveLanguageAndPlural());

		$this->setProperty($translator, 'active_language_code', 'en');
		$this->assertSame(true, $translator->getActiveLanguageAndPlural());
	}

	public function testLanguagesAndPlurals() {
		$translator = new Translator();

		$languages = ['cs' => true, 'en' => true];
		$this->setProperty($translator, 'languages_and_plurals', $languages);

		$this->assertSame($languages, $translator->getLanguagesAndPlurals());
	}

	public function testSetAndGetStorage() {
		$translator = new Translator();

		$this->assertNull($translator->getStorage());

		$storage = $this->createStorageMock();
		$translator->setStorage($storage);
		$this->assertSame($storage, $translator->getStorage());
	}

	public function testGetTranslator() {
		$translator = new Translator();

		$plural = $this->createPluralMock();

		$language_and_plural = new LanguageAndPlural();
		$language_and_plural->setPlural($plural);

		$this->setProperty($translator, 'languages_and_plurals', ['cs' => $language_and_plural]);
		$this->setProperty($translator, 'active_language_code', 'cs');

		$translator_translator = $translator->getTranslator();
		$this->assertSame($plural, $translator_translator->getPlural());

		$storage = $this->createStorageMock(true);
		$translator->setStorage($storage);

		$translator_translator = $translator->getTranslator();
		$this->assertSame($plural, $translator_translator->getPlural());
		$translator_translator->__destruct();
	}

	public function testTranslate() {
		$translator = new Translator();

		$plural = $this->createPluralMock();

		$language_and_plural = new LanguageAndPlural();
		$language_and_plural->setPlural($plural);

		$this->setProperty($translator, 'languages_and_plurals', ['cs' => $language_and_plural]);
		$this->setProperty($translator, 'active_language_code', 'cs');

		$this->assertSame('foo', $translator->translate('foo'));
		$this->assertSame('foo', $translator->translate('foo', 0));
		$this->assertSame('foo', $translator->translate('foo', 0, []));
	}
}
