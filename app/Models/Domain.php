<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Builder|static upsert(array $values, $uniqueBy, $update = null)
 * @method static Builder|Domain updateOrCreate(array $attributes, array $values = [])
 * @property string $domain
 * @property int $rank
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Domain extends Model
{
    protected $fillable = [
        'domain',
        'rank'
    ];
}
