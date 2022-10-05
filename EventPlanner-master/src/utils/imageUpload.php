<?php

namespace App\utils;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class imageUpload
{
    public function save($image, $name, $dir){
        if ($image){
            /**
             * @var UploadedFile $file
             */
            $newName = preg_replace("/\s/", "_", $name)."-".uniqid().".".$image->guessExtension();
            $image->move($dir, $newName);
        }else{
            $newName="default.png";
        }
        return $newName;
    }
}