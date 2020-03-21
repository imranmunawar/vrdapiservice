<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyStandCount extends Model
{
  public function company()
  {
      return $this->hasOne('App\Company','id','company_id');

  }
  protected $fillable = array(
     'candidate_id',
     'company_id',
     'fair_id'
 );
}
