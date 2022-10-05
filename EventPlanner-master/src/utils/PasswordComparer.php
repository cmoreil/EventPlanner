<?php

namespace App\utils;

class PasswordComparer
{
    public function comparer($password, $confirmPassword){
        $valid=false;
        if ($password != $confirmPassword){
            $valid=false;
        }else{
            $valid=true;
        }
        return $valid;
    }
}