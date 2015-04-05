<?php

use l10nNetteTranslator\LanguageAndPlural;

class LanguageAndPluralTest extends BaseTest {
	public function createLanguageMock() {
		/** @var \l10n\Language\ILanguage|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10n\Language\ILanguage');

		return $mock;
	}

	public function createPluralMock() {
		/** @var \l10n\Plural\IPlural|PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMock('l10n\Plural\IPlural');

		return $mock;
	}

	public function testSettersAndGetters() {
		$language_and_plural = new LanguageAndPlural();

		$language_mock = $this->createLanguageMock();
		$language_and_plural->setLanguage($language_mock);
		$language = $language_and_plural->getLanguage();
		$this->assertSame($language_mock, $language);

		$plural_mock = $this->createPluralMock();
		$language_and_plural->setPlural($plural_mock);
		$plural = $language_and_plural->getPlural();
		$this->assertSame($plural_mock, $plural);
	}
}
