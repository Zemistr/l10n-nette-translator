<?php

namespace l10nNetteTranslator;

use Nette\Object;
use Tracy\IBarPanel;

class Panel extends Object implements IBarPanel {
	/**
	 * Return's panel ID
	 *
	 * @return string
	 */
	public function getId() {
		return __CLASS__;
	}

	/**
	 * Returns the code for the panel tab
	 *
	 * @return string
	 */
	public function getTab() {
		ob_start();
		require __DIR__ . '/Templates/tab.phtml';

		return ob_get_clean();
	}

	/**
	 * Returns the code for the panel itself
	 *
	 * @return string
	 */
	public function getPanel() {
		ob_start();
		require __DIR__ . '/Templates/panel.phtml';

		return ob_get_clean();
	}
}
