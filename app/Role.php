<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
   public function users()
   {
       return $this->belongsToMany(User::class);
   }

   public static function IsRoleExist($name){
       return self::where('name',$name)->first();
   }
}
