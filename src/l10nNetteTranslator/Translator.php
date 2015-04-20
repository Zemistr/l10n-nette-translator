<?php
namespace l10nNetteTranslator;

use l10n\Language\ILanguage;
use l10n\Plural\IPlural;
use l10nNetteTranslator\Storage\IStorage;
use Nette\InvalidStateException;
use Nette\Localization\ITranslator;
use Nette\Object;

class Translator extends Object implements ITranslator {
	/** @var \l10nNetteTranslator\LanguageAndPlural[] */
	private $languages_and_plurals;

	/** @var \l10nNetteTranslator\Storage\IStorage */
	private $storage;

	/** @var \l10n\Translator\Translator */
	private $translator;

	/** @var string */
	private $active_language_code;

	/**
	 * @param string $code
	 */
	protected function testLanguageCode($code) {
		if (empty($this->languages_and_plurals[$code])) {
			throw new InvalidStateException(sprintf('Language with code "%s" is not set', $code));
		}
	}

	/**
	 * @param \l10n\Language\ILanguage $language
	 * @param \l10n\Plural\IPlural     $plural
	 * @param bool                     $default
	 */
	public function addLanguageAndPlural(ILanguage $language, IPlural $plural, $default = false) {
		$language_and_plural = new LanguageAndPlural();
		$language_and_plural->setLanguage($language);
		$language_and_plural->setPlural($plural);
		$code = $language->getIso639_1();

		$this->languages_and_plurals[$code] = $language_and_plural;

		if ($default || !$this->active_language_code) {
			$this->setActiveLanguageCode($code);
		}
	}

	/**
	 * @param string $code
	 * @throws InvalidStateException
	 */
	public function setActiveLanguageCode($code) {
		$this->testLanguageCode($code);
		$this->active_language_code = $code;
		$this->translator = null;
	}

	/**
	 * @param string $code
	 * @return \l10nNetteTranslator\LanguageAndPlural
	 * @throws InvalidStateException
	 */
	public function getLanguageAndPluralByCode($code) {
		$this->testLanguageCode($code);

		return $this->languages_and_plurals[$code];
	}

	/**
	 * @param string $code
	 * @return bool
	 */
	public function hasLanguageAndPluralByCode($code) {
		return isset($this->languages_and_plurals[$code]);
	}

	/**
	 * @return \l10nNetteTranslator\LanguageAndPlural
	 * @throws InvalidStateException
	 */
	public function getActiveLanguageAndPlural() {
		return $this->getLanguageAndPluralByCode($this->active_language_code);
	}

	/**
	 * @return \l10nNetteTranslator\LanguageAndPlural[]
	 */
	public function getLanguagesAndPlurals() {
		return $this->languages_and_plurals;
	}

	/**
	 * @return \l10nNetteTranslator\Storage\IStorage
	 */
	public function getStorage() {
		return $this->storage;
	}

	/**
	 * @param \l10nNetteTranslator\Storage\IStorage $storage
	 */
	public function setStorage(IStorage $storage) {
		$this->storage = $storage;
		$this->translator = null;
	}

	/**
	 * @return \l10n\Translator\Translator
	 */
	public function getTranslator() {
		if (!($this->translator instanceof \l10n\Translator\Translator)) {
			$plural = $this->getActiveLanguageAndPlural()->getPlural();
			$storage = $this->getStorage();

			if ($storage) {
				$storage->setTranslator($this);
			}

			$this->translator = new \l10n\Translator\Translator($plural, $storage);
		}

		return $this->translator;
	}

	/**
	 * @param string         $key
	 * @param int|array|null $n When $n is null, than singular will be selected. When $n is an array, it's used as $parameters.
	 * @param array          $parameters
	 * @return string
	 */
	public function translate($key, $n = null, array $parameters = []) {
		return $this->getTranslator()->translate($key, $n, $parameters);
	}
}
