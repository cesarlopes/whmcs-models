<?php

namespace CesarLopes\WHMCS\Models\User;

use CesarLopes\WHMCS\Models\Billing\Invoice;
use CesarLopes\WHMCS\Models\Domain\Domain;
use CesarLopes\WHMCS\Models\User\Client\Affiliate;
use CesarLopes\WHMCS\Models\User\Client\Contact;

class Client extends \WHMCS\User\Client
{
	public function affiliate()
	{
		return $this->hasOne(Affiliate::class, 'clientid');
	}

	public function contacts()
	{
		return $this->hasMany(Contact::class, 'userid');
	}

	public function invoices()
	{
		return $this->hasMany(Invoice::class, 'userid');
	}

	public function domains()
	{
		return $this->hasMany(Domain::class, 'userid');
	}
}
