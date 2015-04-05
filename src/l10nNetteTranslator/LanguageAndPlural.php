<?php
namespace l10nNetteTranslator;

use l10n\Language\ILanguage;
use l10n\Plural\IPlural;

class LanguageAndPlural {
	/** @var ILanguage */
	private $language;

	/** @var IPlural */
	private $plural;

	/**
	 * @return ILanguage
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * @param ILanguage $language
	 */
	public function setLanguage(ILanguage $language) {
		$this->language = $language;
	}

	/**
	 * @return IPlural
	 */
	public function getPlural() {
		return $this->plural;
	}

	/**
	 * @param IPlural $plural
	 */
	public function setPlural(IPlural $plural) {
		$this->plural = $plural;
	}
}
