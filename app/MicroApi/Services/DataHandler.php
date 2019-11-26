<?php
# app/MicroApi/Services/DataHandler.php
namespace App\MicroApi\Services;

trait DataHandler
{
    public function encode($data)
    {
        return json_encode($data);
    }

    public function decode($content)
    {
        return json_decode($content);
    }
}
