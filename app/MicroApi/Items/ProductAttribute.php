<?php
namespace App\MicroApi\Items;

class ProductAttribute
{
    public $id;
    public $product_id;
    public $quantity;
    public $price;
    public $sale_price;
    public $default;
    public $created_at;
    public $updated_at;
    public $attribute_values;

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
                case 'product_id':
                    $this->product_id = $value;
                    break;
                case 'quantity':
                    $this->quantity = $value;
                    break;
                case 'price':
                    $this->price = $value;
                    break;
                case 'sale_price':
                    $this->sale_price = $value;
                    break;
                case 'default':
                    $this->default = $value;
                    break;
                case 'created_at':
                    $this->created_at = $value;
                    break;
                case 'updated_at':
                    $this->updated_at = $value;
                    break;
                case 'attribute_values':
                    $this->attribute_values = $value;
                    break;
                default:
                    break;
            }
        }
        return $this;
    }
}
