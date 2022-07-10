<?php

namespace CesarLopes\WHMCS\Models\Module\Addon;

use \WHMCS\Module\Addon\Setting;

class Settings
{
	public static function addonModule($addon)
	{

		$arraySettings = Setting::module($addon)->get();
		$settings      = new \stdClass();

		foreach ($arraySettings as $setting) {
			$settings->{$setting->setting} = $setting->value;
		}
		return $settings;
	}
}
