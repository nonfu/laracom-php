<?php
namespace App\MicroApi\Items;

class ProductItem
{
    public $id;
    public $brand_id;
    public $sku;
    public $name;
    public $slug;
    public $status;
    public $description;
    public $cover;
    public $quantity;
    public $price;
    public $sale_price;
    public $length;
    public $width;
    public $height;
    public $weight;
    public $distance_unit;
    public $mass_unit;
    public $created_at;
    public $updated_at;
    public $brand;
    public $categories;
    public $images;
    public $attributes;

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
                case 'brand_id':
                    $this->brand_id = $value;
                    break;
                case 'sku':
                    $this->sku = $value;
                    break;
                case 'name':
                    $this->name = $value;
                    break;
                case 'slug':
                    $this->slug = $value;
                    break;
                case 'status':
                    $this->status = $value;
                    break;
                case 'description':
                    $this->description = $value;
                    break;
                case 'cover':
                    $this->cover = $value;
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
                case 'length':
                    $this->length = $value;
                    break;
                case 'width':
                    $this->width = $value;
                    break;
                case 'height':
                    $this->height = $value;
                    break;
                case 'weight':
                    $this->weight = $value;
                    break;
                case 'distance_unit':
                    $this->distance_unit = $value;
                    break;
                case 'mass_unit':
                    $this->mass_unit = $value;
                    break;
                case 'created_at':
                    $this->created_at = $value;
                    break;
                case 'updated_at':
                    $this->updated_at = $value;
                    break;
                case 'brand':
                    $this->brand = $value;
                    break;
                case 'images':
                    $this->images = $value;
                    break;
                case 'categories':
                    $this->categories = $value;
                    break;
                case 'attributes':
                    $this->attributes = $value;
                    break;
                default:
                    break;
            }
        }
        return $this;
    }
}
