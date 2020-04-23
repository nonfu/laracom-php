<?php
namespace App\MicroApi\Items;

class AttributeValue
{
    public $id;
    public $value;
    public $attribute_id;
    public $created_at;
    public $updated_at;
    public $attribute;
    public $product_attributes;

    public function fillAttributes($data)
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        foreach ($data as $key => $value) {
            switch (strtolower($key)) {
                case 'id':
                    $this->id = $value;
                    break;
                case 'value':
                    $this->value = $value;
                    break;
                case 'attribute_id':
                    $this->attribute_id = $value;
                    break;
                case 'created_at':
                    $this->created_at = $value;
                    break;
                case 'updated_at':
                    $this->updated_at = $value;
                    break;
                case 'attribute':
                    $this->attribute = $value;
                    break;
                case 'product_attributes':
                    $this->product_attributes = $value;
                    break;
                default:
                    break;
            }
        }
        return $this;
    }
}
