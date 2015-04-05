<?php
spl_autoload_register(
	function ($class_name) {
		static $class_map = [
			'l10nNetteTranslator\\ApplicationDI\\Extension'    => 'ApplicationDI/Extension.php',
			'l10nNetteTranslator\\LanguageAndPlural'           => 'LanguageAndPlural.php',
			'l10nNetteTranslator\\Panel'                       => 'Panel.php',
			'l10nNetteTranslator\\Storage\\IStorage'           => 'Storage/IStorage.php',
			'l10nNetteTranslator\\Storage\\SimpleNetteStorage' => 'Storage/SimpleNetteStorage.php',
			'l10nNetteTranslator\\Translator'                  => 'Translator.php',
			'l10nNetteTranslator\\TranslatorProcessor'         => 'TranslatorProcessor.php',
		];

		if (isset($class_map[$class_name])) {
			require __DIR__ . '/l10nNetteTranslator/' . $class_map[$class_name];
		}
	}
);
