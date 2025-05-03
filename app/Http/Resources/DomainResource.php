<?php

namespace App\Http\Resources;

use App\Models\Domain;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainResource extends JsonResource
{
    /**
     * @var Domain
     */
    public $resource;

    public function toArray($request): array
    {
        return [
            'domain' => $this->resource->domain,
            'rank'   => $this->resource->rank,
        ];
    }
}
