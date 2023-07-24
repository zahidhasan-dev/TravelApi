<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TourListRequest;
use App\Http\Resources\TourResource;
use App\Models\Travel;

class TourController extends Controller
{
    
    public function index($travel, TourListRequest $request)
    {
        $travel = Travel::where('is_public',true)->where('slug',$travel)->firstOrFail();
        
        $tours = $travel->tours()
                    ->filter($request)
                    ->orderBy('starting_date')
                    ->paginate();

        return TourResource::collection($tours);
    }

}
