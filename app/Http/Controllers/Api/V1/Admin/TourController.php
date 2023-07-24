<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TourListRequest;
use App\Http\Requests\TourRequest;
use App\Http\Resources\TourResource;
use App\Models\Tour;
use App\Models\Travel;

class TourController extends Controller
{
    public function index(Travel $travel, TourListRequest $request)
    {
        $tours = $travel->tours()
                    ->filter($request)
                    ->orderBy('starting_date')
                    ->paginate();

        return TourResource::collection($tours);
    }


    public function store(Travel $travel, TourRequest $request)
    {
        $tour = $travel->tours()->create($request->validated());

        return new TourResource($tour);
    }


    public function update(Travel $travel, $tourId, TourRequest $request)
    {
        $tour = $travel->tours()
                    ->where('id', $tourId)
                    ->firstOrFail();

        $tour->update($request->validated());

        return new TourResource($tour);
    }

}
