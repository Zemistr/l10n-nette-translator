<?php
namespace l10nNetteTranslator\ApplicationDI;

use Nette\DI\CompilerExtension;
use Nette\InvalidStateException;
use Nette\PhpGenerator\ClassType;

class Extension extends CompilerExtension {
	public function loadConfiguration() {
		$config = $this->getConfig();

		if (empty($config['languages']) || !is_array($config['languages'])) {
			throw new InvalidStateException("Languages must be set, must be array and can't be empty");
		}

		/** @var \Nette\DI\ContainerBuilder $builder */
		$builder = $this->getContainerBuilder();

		$translator = $builder->addDefinition('l10n_nette_translator.translator');
		$translator->setClass('l10nNetteTranslator\Translator');

		foreach ($config['languages'] as $language) {
			if (empty($language['lang'])) {
				throw new InvalidStateException('Key "lang" must be set');
			}

			$lang = $builder->literal("new $language[lang]");
			$plural = isset($language['plural']) ? $builder->literal("new $language[plural]") : $lang;
			$default = !empty($language['default']);

			$translator->addSetup('addLanguageAndPlural', [$lang, $plural, $default]);
		}

		if (!empty($config['storage'])) {
			$translator->addSetup('setStorage', [$config['storage']]);
		}

		$panel = $builder->addDefinition('l10n_nette_translator.panel');
		$panel->setClass('l10nNetteTranslator\Panel');

		$processor = $builder->addDefinition('l10n_nette_translator.processor');
		$processor->setClass('l10nNetteTranslator\TranslatorProcessor');
	}

	public function afterCompile(ClassType $class) {
		$initialize = $class->getMethod('initialize');
		$initialize->addBody('$this->getService("tracy.bar")->addPanel($this->getService("l10n_nette_translator.panel"));');
		$initialize->addBody('$response = $this->getService("l10n_nette_translator.processor")->run();');
		$initialize->addBody('if($response instanceof Nette\Application\IResponse) {');
		$initialize->addBody(' $response->send($this->getByType("Nette\Http\IRequest"), $this->getByType("Nette\Http\IResponse"));');
		$initialize->addBody(' exit();');
		$initialize->addBody('}');
	}
}
