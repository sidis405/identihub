<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Icon extends Model
{
    use SoftDeletes;

    protected $table = 'icons';

    protected $fillable = [
        'filename', 'filename_png', 'bridge_id', 'width_ratio', 'section_id', 'order'
    ];

    protected $casts = [
        'order' => 'int'
    ];

    public static function boot()
    {
        static::created(function ($icon) {
            Bridge::whereId($icon->bridge_id)->increment('nr_icons');
        });

        static::updated(function ($icon) {
            Bridge::whereId($icon->bridge_id)->increment('nr_icons');
        });

        static::created(function ($icon) {
            $icon->order = Icon::where('section_id', $icon->section->id)->where('bridge_id', $icon->bridge->id)->count();
            $icon->save();
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

    public function getFilenamePngAttribute($value)
    {
        return (strlen($value) > 1) ? $value :  $this->filename;
    }

    public function converted()
    {
        return $this->hasMany(IconConverted::class, 'icon_id', 'id');
    }
}
