<?php
namespace App\MicroApi\Items;

class ProductImage
{
    public $id;
    public $product_id;
    public $src;

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
                case 'src':
                    $this->src = $value;
                    break;
                default:
                    break;
            }
        }
        return $this;
    }
}
