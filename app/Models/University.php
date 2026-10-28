<?php

namespace App\Models;

use Database\Factories\UniversityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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
     * In-memory memoized university instance for the current request lifecycle.
     */
    private static ?self $currentInstance = null;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(function (): void {
            self::clearCurrentCache();
        });

        static::deleted(function (): void {
            self::clearCurrentCache();
        });
    }

    /**
     * Clear the cached root university state.
     */
    public static function clearCurrentCache(): void
    {
        self::$currentInstance = null;
        Cache::forget('root_university');
        Cache::forget('root_university_attrs');
    }

    /**
     * Retrieve the root university instance (cached for efficiency without model serialization issues).
     */
    public static function current(): ?self
    {
        if (self::$currentInstance instanceof self) {
            return self::$currentInstance;
        }

        try {
            $cachedAttrs = Cache::remember('root_university_attrs', 3600, function (): ?array {
                $university = self::first();

                return $university ? $university->getAttributes() : null;
            });

            if (is_array($cachedAttrs)) {
                return self::$currentInstance = (new self)->newFromBuilder($cachedAttrs);
            }
        } catch (\Throwable) {
            // In case of cache driver issues, fall back to direct DB query
        }

        $university = self::first();
        if ($university instanceof self) {
            self::$currentInstance = $university;
        }

        return $university;
    }

    /**
     * Get the URL for the institution logo, falling back to the default AdminLTE brand asset.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return Storage::disk('public')->url($this->logo_path);
        }

        return asset('vendor/adminlte/assets/img/AdminLTELogo.png');
    }

    /**
     * Determine if a custom logo is currently uploaded on disk.
     */
    public function hasCustomLogo(): bool
    {
        return ! empty($this->logo_path) && Storage::disk('public')->exists($this->logo_path);
    }

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
