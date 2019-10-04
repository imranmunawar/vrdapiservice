<?php 
namespace App;
use Illuminate\Database\Eloquent\Model;

class CompanyWebinar extends Model {

    public function company()
    {
        return $this->belongsTo('App\Company', 'id', 'company_id' );
    }

    public function fair()
    {
        return $this->hasOne('App\Fair',  'id', 'fair_id');
    }

    protected $fillable = [
        'company_id',
        'recruiter_id',
        'fair_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'link',
        'type',
        'match'
    ];

}

