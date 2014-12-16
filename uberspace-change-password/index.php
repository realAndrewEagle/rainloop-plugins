<?php

class UberspaceChangePasswordPlugin extends \RainLoop\Plugins\AbstractPlugin
{
	public function Init()
	{
		$this->addHook('main.fabrica', 'MainFabrica');
	}

	/**
	 * @param string $name
	 * @param mixed $provider
	 */
	public function MainFabrica($name, &$provider)
	{
		switch ($name)
		{
			case 'change-password':

				include_once __DIR__.'/UberspaceChangePasswordDriver.php';

				$provider = new UberspaceChangePasswordDriver();

				break;
		}
	}
}
