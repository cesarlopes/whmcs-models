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
		if (!$this->customFieldStore) $this->customFieldStore = new CustomFieldStore($this);
		return $this->customFieldStore;
	} */

	public function findByID($userid)
    {
		return self::find($userid);
	}

	public function loadCustomFields(){		

        $customFields = Capsule::table('tblcustomfieldsvalues')
            ->join('tblcustomfields', 'tblcustomfields.id', '=', 'tblcustomfieldsvalues.fieldid')
            ->where('tblcustomfields.type', 'client')
            ->where('tblcustomfieldsvalues.relid', $this->id)
            ->get();

        foreach ($customFields as $field) {
            if (mb_strrpos(mb_strtoupper($field->fieldname), 'CPF') !== false) {
                $cpf = $this->sanitizeField($field->value);
                if (strlen($cpf) == 11) {
                    $this->cpf = $cpf;
                    $this->document = $cpf;
                    $this->doc_name = $this->firstname . ' ' . $this->lastname;
                }
            }
            if (mb_strrpos(mb_strtoupper($field->fieldname), 'CNPJ') !== false) {
                $cnpj = $this->sanitizeField($field->value);
                if (strlen($cnpj) == 14) {
                    $this->cnpj = $cnpj;
                    $this->document = $cnpj;
                    $this->doc_name = $this->companyname;
                }
            }
            if (mb_strrpos(mb_strtoupper($field->fieldname), 'IM') !== false OR mb_strrpos(mb_strtoupper($field->fieldname), 'INSCRIÇÃO MUNICIPAL') !== false) {
                if (strlen($field->value) > 0) {
                    $this->im = $field->value;
                }
            }
            $this->{$this->under_score($field->fieldname)} = $field->value;
        }

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

    public function setAddress()
    {
        $this->address->street   = str_replace(',', '', preg_replace('/[0-9]+/i', '', $this->address1));
		$this->address->number   = preg_replace('/[^0-9]/', '', $this->address1);
		$this->address->district = $this->address2;
        $this->address->city     = $this->city;
        $this->address->state    = $this->state;
		$this->address->postcode = preg_replace('/[^0-9]/', '', $this->postcode);
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
