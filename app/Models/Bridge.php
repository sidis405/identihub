<?php

namespace App;

use App\Jobs\CreateSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Bridge extends Model
{
    protected $casts = [
        'user_id' => 'int'
    ];

    protected $fillable = [
        'name', 'user_id', 'slug', 'nr_images', 'nr_icons', 'nr_fonts', 'nr_colors'
    ];


    public static function boot()
    {
        parent::boot();

        static::created(function ($bridge) {
            foreach ([
                     SectionType::getColorsSectionType(),
                     SectionType::getIconsSectionType(),
                     SectionType::getImagesSectionType()
                ] as $sectionType) {
                (new CreateSection($bridge, $sectionType))->handle();
            }
        });
    }


    public function user()
    {
        return $this->hasOne(User::class);
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'bridge_id', 'id');
    }

    public function iconSections(Collection $collection)
    {
        return $this->getSectionsFromType($collection, SectionType::ICONS);
    }

    public function fontSections(Collection $collection)
    {
        return $this->getSectionsFromType($collection, SectionType::FONTS);
    }

    public function imageSections(Collection $collection)
    {
        return $this->getSectionsFromType($collection, SectionType::IMAGES);
    }

    public function colorsSections(Collection $collection)
    {
        return $this->getSectionsFromType($collection, SectionType::COLORS);
    }

    public function getSectionsFromType(Collection $collection, $type)
    {
        $sectionType = SectionType::where('name', $type)->get()->first();
        return $collection->filter(function ($value, $key) use ($sectionType) {
            return $value->section_type_id === $sectionType->id;
        });
    }

    public function getSectionsFromTypeModel(Collection $collection, $type)
    {
        return $collection->filter(function ($value, $key) use ($type) {
            return $value->section_type_id === $type->id;
        });
    }

    public function icons()
    {
        return $this->hasMany(Icon::class, 'bridge_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'bridge_id', 'id');
    }

    public function fonts()
    {
        return $this->hasMany(Font::class, 'bridge_id', 'id');
    }

    public function colors()
    {
        return $this->hasMany(Color::class, 'bridge_id', 'id');
    }

    public function loadCommonRelations()
    {
        return $this->fresh()->load('sections', 'icons', 'icons.converted', 'images', 'images.converted', 'fonts', 'fonts.variant', 'fonts.variant.fontFamily', 'colors');
    }
}
