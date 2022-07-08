<?php

namespace CesarLopes\WHMCS\Models\User\Client;

use CesarLopes\WHMCS\Models\User\Traits\CanBeLabelled;
use CesarLopes\WHMCS\Models\User\Client;

class Contact extends \WHMCS\User\Client\Contact
{
	public function client()
	{
		return $this->belongsTo(Client::class, 'userid');
	}

	public function log($message)
	{
		$this->client->log("[Contact ID: {$this->id}]: {$message}");
	}
}
