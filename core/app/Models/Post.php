<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'slug','details', 'photo', 'category_id','tags','meta_keywords','meta_descriptions'];

    public function category()
    {
    	return $this->belongsTo('App\Models\Bcategory')->withDefault();
    }

    public function getPhotoAttribute($value)
    {
        return \App\Services\BlogPhotoNormalizer::normalize($value);
    }

    public function setPhotoAttribute($value)
    {
        $normalized = \App\Services\BlogPhotoNormalizer::normalize($value);
        $this->attributes['photo'] = json_encode($normalized);
    }
}
