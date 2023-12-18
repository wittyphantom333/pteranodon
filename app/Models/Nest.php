<?php

namespace Pteranodon\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property bool $private
 * @property string $uuid
 * @property string $author
 * @property string $name
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Database\Eloquent\Collection|\Pteranodon\Models\Server[] $servers
 * @property \Illuminate\Database\Eloquent\Collection|\Pteranodon\Models\Egg[] $eggs
 */
class Nest extends Model
{
    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'nest';

    /**
     * The table associated with the model.
     */
    protected $table = 'nests';

    /**
     * Fields that are mass assignable.
     */
    protected $fillable = [
        'name',
        'private',
        'description',
    ];

    public static array $validationRules = [
        'author' => 'required|string|email',
        'name' => 'required|string|max:191',
        'description' => 'nullable|string',
    ];

    /**
     * Gets all eggs associated with this service.
     */
    public function eggs(): HasMany
    {
        return $this->hasMany(Egg::class);
    }

    /**
     * Gets all servers associated with this nest.
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }
}
