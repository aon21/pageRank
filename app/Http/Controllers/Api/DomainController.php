<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DomainIndexRequest;
use App\Http\Resources\DomainResource;
use App\Models\Domain;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DomainController extends Controller
{
    public function index(DomainIndexRequest $request): AnonymousResourceCollection
    {
        $domains = Domain::when($request->validated('search'), function ($query, $search) {
            $query->where('domain', 'like', "%$search%");
        })->paginate(
            $request->validated('per_page', 100)
        );

        return DomainResource::collection($domains);
    }
}
