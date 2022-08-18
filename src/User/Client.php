<?php

namespace CesarLopes\WHMCS\Models\User;

use CesarLopes\WHMCS\Models\Billing\Invoice;
use CesarLopes\WHMCS\Models\Domain\Domain;
use CesarLopes\WHMCS\Models\User\Client\Affiliate;
use CesarLopes\WHMCS\Models\User\Client\Contact;
use CesarLopes\WHMCS\Models\CustomField\CustomFieldStore;

use WHMCS\Database\Capsule;

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

	/* public function customFieldStore()
	{
		if (!$customFieldsStore) $customFieldsStore = new CustomFieldStore($this);
		return $customFieldsStore;
	} */

	public function findByID($userid)
    {
		return self::find($userid);
	}

	public function loadCustomFields(){		

        $dbFields = Capsule::table('tblcustomfieldsvalues')
            ->join('tblcustomfields', 'tblcustomfields.id', '=', 'tblcustomfieldsvalues.fieldid')
            ->where('tblcustomfields.type', 'client')
            ->where('tblcustomfieldsvalues.relid', $this->id)
            ->get();

		$customFields = new \stdClass();

        foreach ($dbFields as $field) {
            if (mb_strrpos(mb_strtoupper($field->fieldname), 'CPF') !== false) {
                $cpf = $this->sanitizeField($field->value);
                if (strlen($cpf) == 11) {
                    $customFields->cpf = $cpf;
                    $customFields->document = $cpf;
                    $customFields->doc_name = $this->firstname . ' ' . $this->lastname;
                }
            }
            if (mb_strrpos(mb_strtoupper($field->fieldname), 'CNPJ') !== false) {
                $cnpj = $this->sanitizeField($field->value);
                if (strlen($cnpj) == 14) {
                    $customFields->cnpj = $cnpj;
                    $customFields->document = $cnpj;
                    $customFields->doc_name = $this->companyname;
                }
            }
            if (mb_strrpos(mb_strtoupper($field->fieldname), 'IM') !== false OR mb_strrpos(mb_strtoupper($field->fieldname), 'INSCRIÇÃO MUNICIPAL') !== false) {
                if (strlen($field->value) > 0) {
                    $customFields->im = $field->value;
                }
            }
            $customFields->{$this->under_score($field->fieldname)} = $field->value;
        }

		$this->custom_fields = $customFields;

		return $this;
		
    }

    public function findByDomain($domain)
    {
        $userid = $this->getClientByDomainRegister($domain);
        if (!$userid) {
            $userid = $this->getClientByDomainService($domain);
        }
        if ($userid) {            
			return self::find($userid);
        }        
    }

    public function getClientByDomainRegister($domain)
    {
        $stdClass = Capsule::table('tbldomains')->where('domain', $domain)->first();

        if ($stdClass) {
            foreach ($stdClass as $property => $value) {
                if($property == 'userid'){
                    return $value;
                }
            }
        }
        return false;
    }
    public function getClientByDomainService($domain)
    {
        $stdClass = Capsule::table('tblhosting')->where('domain', $domain)->first();

        if ($stdClass) {
            foreach ($stdClass as $property => $value) {
                if($property == 'userid'){
                    return $value;
                }
            }            
        }
        return false;
    }

    public function buildAddress()
    {
        $address = new \stdClass();

		$address->street   = str_replace(',', '', preg_replace('/[0-9]+/i', '', $this->address1));
		$address->number   = preg_replace('/[^0-9]/', '', $this->address1);
		$address->district = $this->address2;
        $address->city     = $this->city;
        $address->state    = $this->state;
		$address->postcode = preg_replace('/[^0-9]/', '', $this->postcode);

		$this->address = $address;
		
		return $this;
    }

    private function under_score(string $str, array $noStrip = [])
    {
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = strtolower($str);
        $str = str_replace(" ", "_", $str);

        return $str;
    }

    private function sanitizeField(?string $param)
    {
        if (empty($param)) {
            return null;
        }
        return str_replace(['.', '-', '/', '(', ')', ' '], '', trim($param));
    }
}
