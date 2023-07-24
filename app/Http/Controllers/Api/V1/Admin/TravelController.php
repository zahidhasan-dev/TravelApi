<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Travel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest;
use App\Http\Resources\TravelResource;

class TravelController extends Controller
{

    public function index()
    {
        $travels = Travel::paginate();

        return TravelResource::collection($travels);
    }
    
    
    public function store(TravelRequest $request)
    {
        $travel = Travel::create($request->validated());

        return new TravelResource($travel);
    }


    public function update(Travel $travel, Request $request)
    {
        $validated = $request->validate([
            'is_public' => 'boolean',
            'name' => ['required', 'string', Rule::unique('travels', 'name')->ignore($travel->id, 'id')],
            'description' => ['required', 'string'],
            'number_of_days' => ['required', 'integer'],
        ]);

        $travel->update($validated + ['slug' => str($request->name)->slug()]);

        return new TravelResource($travel);
    }

}
