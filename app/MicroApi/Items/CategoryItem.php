<?php
namespace App\MicroApi\Items;

class CategoryItem
{
    public $id;
    public $name;
    public $slug;
    public $description;
    public $cover;
    public $status;
    public $parent_id;
    public $lft;
    public $rgt;
    public $created_at;
    public $updated_at;

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
                case 'name':
                    $this->name = $value;
                    break;
                case 'slug':
                    $this->slug = $value;
                    break;
                case 'description':
                    $this->description = $value;
                    break;
                case 'cover':
                    $this->cover = $value;
                    break;
                case 'status':
                    $this->status = $value;
                    break;
                case 'parent_id':
                    $this->parent_id = $value;
                    break;
                case 'lft':
                    $this->lft = $value;
                    break;
                case 'rgt':
                    $this->rgt = $value;
                    break;
                case 'created_at':
                    $this->created_at = $value;
                    break;
                case 'updated_at':
                    $this->updated_at = $value;
                    break;
                default:
                    break;
            }
        }
        return $this;
    }
}
