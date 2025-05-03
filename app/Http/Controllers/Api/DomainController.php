<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DomainResource;
use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DomainController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $domains = Domain::when($request->query('search'), function ($query, $search) {
            $query->where('domain', 'like', "%$search%");
        })->paginate(100);

        return DomainResource::collection($domains);
    }
}
