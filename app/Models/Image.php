<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use SoftDeletes;

    protected $table = 'images';

    protected $fillable = [
        'filename', 'bridge_id', 'width_ratio', 'section_id', 'order'
    ];

    protected $casts = [
        'order' => 'int'
    ];

    public static function boot()
    {
        static::created(function ($image) {
            Bridge::whereId($image->bridge_id)->increment('nr_images');
        });

        static::updated(function ($image) {
            Bridge::whereId($image->bridge_id)->increment('nr_images');
        });

        static::created(function ($image) {
            $image->order = Image::where('section_id', $image->section->id)->where('bridge_id', $image->bridge->id)->count();
            $image->save();
        });
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function bridge()
    {
        return $this->belongsTo(Bridge::class);
    }

    public function converted()
    {
        return $this->hasMany(ImageConverted::class, 'image_id', 'id');
    }
}
