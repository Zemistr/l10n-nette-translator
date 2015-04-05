<?php
namespace l10nNetteTranslator\Storage;

use l10nNetteTranslator\Translator;

interface IStorage extends \l10n\Translator\IStorage {
	/**
	 * @param \l10nNetteTranslator\Translator $translator
	 * @return void
	 */
	public function setTranslator(Translator $translator);

	/**
	 * @return \l10nNetteTranslator\Translator
	 */
	public function getTranslator();
}
