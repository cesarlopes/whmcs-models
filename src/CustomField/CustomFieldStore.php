<?php

namespace CesarLopes\WHMCS\Models\CustomField;

use CesarLopes\WHMCS\Models\CustomField;
use CesarLopes\WHMCS\Models\User\Client;
use Illuminate\Support\Str;

class CustomFieldStore
{
    protected $relatedModel;
    protected $slugMap = [];
    protected $customFields;

    /**
     * CustomFieldStore constructor.
     *
     * @param $relatedModel (Service|Client)
     *
     * @throws \Exception
     */
    public function __construct($relatedModel)
    {
        $this->relatedModel = $relatedModel;

        if ($this->relatedModel instanceof Client) {
            $this->customFields = CustomField::clientFields()->get();
        } else {
            throw new \Exception('Requested instance type not supported for Salmon custom fields');
        }

        // Map their slugs
        foreach ($this->customFields as $customField) {
            $this->slugMap[self::slugString($customField->fieldname)] = $customField->id;
        }
    }

    protected static function slugString($string)
    {
        return Str::snake(Str::camel($string));
    }

    /**
     * @param $slug
     *
     * @return CustomField|null
     */
    public function getFieldBySlug($slug)
    {
        $slug = self::slugString($slug);
        if (isset($this->slugMap[$slug])) return $this->customFields->where('id', $this->slugMap[$slug])->first();
        return null;
    }

    /**
     * @param $name
     *
     * @return CustomField|null
     */
    public function getFieldByName($name)
    {
        return $this->customFields->where('fieldname', $name)->first();
    }

    /**
     * @param CustomField|null $customField
     *
     * @return CustomFieldValue|null
     */
    public function getCustomFieldValue(CustomField $customField = null)
    {
        if (!$customField) return null; // Convinience to pass result directly from a field getter method
        return CustomFieldValue::fieldId($customField->id)->relId($this->relatedModel->id)->first();
    }

    /**
     * @param $fieldSlug (snake or camel case)
     *
     * @return field value formatted|null
     */
    public function __get($fieldSlug)
    {
        $customFieldValue = $this->getCustomFieldValue($this->getFieldBySlug($fieldSlug));
        if ($customFieldValue) return $customFieldValue->value;
        return null;
    }

    /**
     * @param $fieldName (literal name or snake case)
     *
     * @return field value formatted|null
     */
    public function get($fieldName)
    {
        // Check it by name
        $customFieldValue = $this->getCustomFieldValue($this->getFieldByName($fieldName));
        if ($customFieldValue) return $customFieldValue->value;

        // Check the slug
        $customFieldValue = $this->getCustomFieldValue($this->getFieldBySlug($fieldName));
        if ($customFieldValue) return $customFieldValue->value;

        return null;
    }
}
