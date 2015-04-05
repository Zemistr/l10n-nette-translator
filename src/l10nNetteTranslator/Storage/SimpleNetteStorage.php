<?php
namespace l10nNetteTranslator\Storage;

use l10n\Translator\Translator;
use Nette\InvalidStateException;

class SimpleNetteStorage implements IStorage {
	/** @var \Nette\Caching\IStorage */
	protected $storage;

	/** @var \l10nNetteTranslator\Translator */
	protected $translator;

	/**
	 * @param \Nette\Caching\IStorage $storage
	 */
	public function __construct(\Nette\Caching\IStorage $storage) {
		$this->storage = $storage;
	}

	/**
	 * @param \l10nNetteTranslator\Translator $translator
	 */
	public function setTranslator(\l10nNetteTranslator\Translator $translator) {
		$this->translator = $translator;
	}

	/**
	 * @return \l10nNetteTranslator\Translator
	 * @throws InvalidStateException
	 */
	public function getTranslator() {
		if (!($this->translator instanceof \l10nNetteTranslator\Translator)) {
			throw new InvalidStateException('l10nNetteTranslator\Translator is not set');
		}

		return $this->translator;
	}

	/**
	 * @return \l10n\Language\ILanguage
	 */
	protected function getLanguage() {
		$translator = $this->getTranslator();
		$language_and_plural = $translator->getActiveLanguageAndPlural();
		$language = $language_and_plural->getLanguage();

		return $language;
	}

	/**
	 * @param \l10n\Translator\Translator $translator
	 */
	public function load(Translator $translator) {
		$language = $this->getLanguage();

		$data = $this->storage->read($language->getIso639_1()) ?: [];

		if ($data) {
			foreach ($data['untranslated'] ?: [] as $key => $texts) {
				foreach ($texts as $plural => $_) {
					$translator->setUntranslated($key, $plural);
				}
			}

			foreach ($data['translated'] ?: [] as $key => $texts) {
				foreach ($texts as $plural => $text) {
					$translator->setText($key, $text, $plural);
				}
			}
		}
	}

	/**
	 * @param \l10n\Translator\Translator $translator
	 */
	public function save(Translator $translator) {
		$language = $this->getLanguage();

		$this->storage->write(
			$language->getIso639_1(),
			[
				'translated'   => $translator->getTranslated(),
				'untranslated' => $translator->getUntranslated()
			],
			[]
		);
	}
}
