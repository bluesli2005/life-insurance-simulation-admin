<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SimulationApplicationIndexRequest;
use App\Http\Requests\SimulationApplicationRequest;
use App\Http\Resources\SimulationApplicationResource;
use App\Models\SimulationApplication;

class SimulationApplicationController extends Controller
{
    public function index(SimulationApplicationIndexRequest $request)
    {
        $filters = $request->validated();
        $query = SimulationApplication::query();

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($query) use ($term) {
                foreach (['application_number', 'applicant_name', 'insured_name', 'beneficiary_name', 'notes'] as $column) {
                    $query->orWhere($column, 'like', '%'.$term.'%');
                }
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $applications = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 10);
        $applications->appends($filters);

        return response()->json([
            'data' => SimulationApplicationResource::collection($applications->getCollection())->resolve(),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'from' => $applications->firstItem(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'to' => $applications->lastItem(),
                'total' => $applications->total(),
            ],
            'links' => [
                'first' => $applications->url(1),
                'last' => $applications->url($applications->lastPage()),
                'prev' => $applications->previousPageUrl(),
                'next' => $applications->nextPageUrl(),
            ],
        ]);
    }

    public function store(SimulationApplicationRequest $request)
    {
        $attributes = $request->validated();
        $attributes['currency'] = $attributes['currency'] ?? 'JPY';
        $application = SimulationApplication::create($attributes);

        return response()->json([
            'data' => (new SimulationApplicationResource($application))->resolve(),
        ], 201);
    }

    public function show(SimulationApplication $simulation_application)
    {
        return response()->json([
            'data' => (new SimulationApplicationResource($simulation_application))->resolve(),
        ]);
    }

    public function update(SimulationApplicationRequest $request, SimulationApplication $simulation_application)
    {
        $simulation_application->fill($request->validated())->save();

        return response()->json([
            'data' => (new SimulationApplicationResource($simulation_application->fresh()))->resolve(),
        ]);
    }

    public function destroy(SimulationApplication $simulation_application)
    {
        $simulation_application->delete();

        return response()->json(['data' => null]);
    }
}
