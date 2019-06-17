<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FairMedia extends Model
{
    protected $fillable = [
       'fair_id',
       'fair_media_name',
       'fair_media_type',
       'fair_media_description',
       'fair_media_link',
       'fair_media_source',
       'fair_media_video',
       'fair_media_document'
     ];
}
