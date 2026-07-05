<?php

namespace App\Models;

use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Item extends Model
{

    protected $fillable = ['category_id','subcategory_id','childcategory_id','brand_id','name','slug','sku','tags','video','sort_details','specification_name','specification_description','is_specification','details','photo','thumbnail','discount_price','previous_price','stock','meta_keywords','meta_description','seo_title','seo_focus_keyword','seo_canonical_url','seo_robots','og_title','og_description','og_image','twitter_title','twitter_description','twitter_image','seo_score','seo_analysis','seo_last_analyzed_at','status','is_type','tax_id','date','item_type','file','link','file_type','license_name','license_key','affiliate_link',"seller_id"];

    protected $casts = [
        'seo_analysis' => 'array',
        'seo_last_analyzed_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo('App\Models\Category')->withDefault();
    }

    public function subcategory()
    {
        return $this->belongsTo('App\Models\Subcategory')->withDefault();
    }

    public function childcategory()
    {
        return $this->belongsTo('App\Models\ChieldCategory')->withDefault();
    }

    public function brand()
    {
        return $this->belongsTo('App\Models\Brand')->withDefault();
    }

    public function campaigns()
    {
        return $this->hasMany('App\Models\CampaignItem');
    }

    public function tax()
    {
        return $this->belongsTo('App\Models\Tax')->withDefault();
    }

    public function attributes()
    {
        return $this->hasMany('App\Models\Attribute');
    }

    public function galleries()
    {
        return $this->hasMany('App\Models\Gallery');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\Review');
    }

    public static function taxCalculate($item)
    {
        if($item->tax){
            $price = $item->discount_price;
            $percentage = $item->tax->value;
            $tax = ($price * $percentage) / 100;
            return $tax;
        }else{
            return 0;
        }
        
    }




    public function getWishlistItemId()
    {
        return Wishlist::whereItemId($this->id)->first()->id;
    }


    public function user()
    {
    	return $this->belongsTo('App\Models\User','vendor_id')->withDefault();
    }

    public function seoTitle(): string
    {
        return trim((string) ($this->seo_title ?: $this->name));
    }

    public function seoDescription(): string
    {
        $description = trim((string) ($this->meta_description ?: $this->sort_details));

        return Str::limit(strip_tags($description ?: $this->details), 160, '');
    }

    public function seoKeywords(): string
    {
        return trim((string) ($this->meta_keywords ?: $this->tags));
    }

    public function seoCanonicalUrl(): string
    {
        return $this->seo_canonical_url ?: route('front.product', $this->slug);
    }

    public function seoRobots(): string
    {
        return $this->seo_robots ?: 'index,follow';
    }

    public function seoSocialImageUrl(): string
    {
        return $this->resolveSeoImageUrl($this->og_image ?: ($this->twitter_image ?: ($this->photo ?: $this->thumbnail)));
    }

    public function resolveSeoImageUrl(?string $image): string
    {
        $image = trim((string) $image);
        if ($image && Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        return url('/core/public/storage/images/' . ($image ?: 'placeholder.png'));
    }


    public function is_stock()
    {
        $item = $this;
        // license product stock check------------
        if($item->item_type == 'license'){
            if($item->license_key){
                $lisense_key = json_decode($item->license_key,true);
                if(count($lisense_key) > 0){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }

        // digital product stock check-------------

        if($item->item_type == 'digital'){
            return true;
        }
        if($item->item_type == 'affiliate'){
            return true;
        }

        // physical product stock check

        if($item->item_type == 'normal'){
            if($item->stock){
                if($item->stock != 0){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
          
        }
     
    }

}
