<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyMedia extends Model
{
    protected $fillable = [
       'company_id',
       'company_media_name',
       'company_media_type',
       'company_media_description',
       'company_media_link',
       'company_media_image',
       'company_media_video',
       'company_media_document'
     ];
}
