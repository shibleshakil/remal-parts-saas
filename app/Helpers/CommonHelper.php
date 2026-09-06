<?php
namespace App\Helpers;


class CommonHelper
{

   public static function userHasAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

}
