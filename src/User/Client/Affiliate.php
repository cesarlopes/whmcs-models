<?php

namespace CesarLopes\WHMCS\Models\User\Client;

use CesarLopes\WHMCS\Models\User\Client;

class Affiliate extends \WHMCS\User\Client\Affiliate
{
	public function client()
	{
		return $this->belongsTo(Client::class, 'clientid');
	}
}
