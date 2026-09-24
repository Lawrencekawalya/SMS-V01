<?php

namespace App\Models;

use Database\Factories\UniversityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class University extends Model
{
    /** @use HasFactory<UniversityFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'address',
        'website',
        'logo_path',
    ];

    /**
     * Get the campuses belonging to the university.
     *
     * @return HasMany<Campus, $this>
     */
    public function campuses(): HasMany
    {
        return $this->hasMany(Campus::class);
    }
}
