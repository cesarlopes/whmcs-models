<?php

namespace CesarLopes\WHMCS\Models;

use CesarLopes\WHMCS\Models\CustomField\CustomFieldValue;

class CustomField extends \WHMCS\CustomField
{
	public function customFieldValues()
	{
		return $this->hasMany(CustomFieldValue::class, "fieldid");
	}
}
