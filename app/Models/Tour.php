<?php

namespace App\Models;

use App\Http\Requests\TourListRequest;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tour extends Model
{
    use HasFactory;


    protected $fillable = [
        'travel_id',
        'name',
        'starting_date',
        'ending_date',
        'price'
    ];



    public function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }


    public function scopeFilter($query, TourListRequest $request): Builder
    {
        return $query->when($request->dateFrom, function($query) use($request) {
                    $query->where('starting_date', '>=', $request->dateFrom);
                })
                ->when($request->dateTo, function($query) use($request) {
                    $query->where('ending_date', '<=', $request->dateTo);
                })
                ->when($request->priceFrom, function($query) use($request) {
                    $query->where('price', '>=', $request->priceFrom * 100);
                })
                ->when($request->priceTo, function($query) use($request) {
                    $query->where('price', '<=', $request->priceTo * 100);
                })
                ->when($request->sortBy && $request->sortOrder, function($query) use($request) {
                    $query->orderBy($request->sortBy, $request->sortOrder);
                });
    }

}
