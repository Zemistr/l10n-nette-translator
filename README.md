[![Build Status](https://travis-ci.org/Zemistr/l10n-nette-translator.svg?branch=master)](https://travis-ci.org/Zemistr/l10n-nette-translator)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Zemistr/l10n-nette-translator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Zemistr/l10n-nette-translator/?branch=master)
[![Scrutinizer Code Coverage](https://scrutinizer-ci.com/g/Zemistr/l10n-nette-translator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Zemistr/l10n-nette-translator/?branch=master)
[![Packagist Total Downloads](https://img.shields.io/packagist/dt/zemistr/l10n-nette-translator.svg)](https://packagist.org/packages/zemistr/l10n-nette-translator)
[![License](https://img.shields.io/packagist/l/zemistr/l10n-nette-translator.svg)](http://opensource.org/licenses/mit-license.php)

# l10nNetteTranslator
l10n translator for [Nette](http://nette.org/) with simple panel for [Tracy](http://tracy.nette.org/en/)

![Tracy-Panel](http://zemistr.github.io/l10n-nette-translator/images/tracy-panel.png)

## Packagist
l10nNetteTranslator is available on [Packagist.org](https://packagist.org/packages/zemistr/l10n-nette-translator),
just add the dependency to your composer.json.

```javascript
{
  "require" : {
    "zemistr/l10n-nette-translator": "1.*"
  }
}
```

or run Composer command:
```php
php composer.phar require zemistr/l10n-nette-translator
```

## Usage without composer

```php
<?php
require('src/l10nNetteTranslator.php');
```

## Example usage (standard usage with file storage)
Just add following lines into your config:

```yaml
extensions:
    translator: l10nNetteTranslator\ApplicationDI\Extension

translator:
    # languages are required
    languages:
        -
            lang: l10n\Language\CzechLanguage # must implements l10n\Language\ILanguage
            plural: l10n\Plural\PluralRule8 # must implements l10n\Plural\IPlural

        -
            lang: l10n\Language\SlovakLanguage # if language implements l10n\Plural\IPlural, you can ignore plural section

        -
            lang: l10n\Language\EnglishLanguage
            default: true # if is not set, the first language will be set as default

    # storage is optional
    storage: @translator_storage_adapter # must implements l10nNetteTranslator\Storage\IStorage

services:
    # You can use any storage implements Nette\Caching\IStorage
    translator_nette_storage:
        class: Nette\Caching\Storages\FileStorage(%appDir%/Texts) # Texts will be saved in %appDir%/Texts as file named by ISO 639-1
        autowired: false

    translator_simple_nette_storage: l10nNetteTranslator\Storage\SimpleNetteStorage(@translator_storage)

```


-----

(c) Martin Zeman (Zemistr), 2015 (http://zemistr.eu)
