<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get assessment sheets instructed by this user.
     *
     * @return HasMany<CourseAssessmentSheet, $this>
     */
    public function instructedSheets(): HasMany
    {
        return $this->hasMany(CourseAssessmentSheet::class, 'instructor_id');
    }

    /**
     * Get assessment sheets moderated by this user.
     *
     * @return HasMany<CourseAssessmentSheet, $this>
     */
    public function moderatedSheets(): HasMany
    {
        return $this->hasMany(CourseAssessmentSheet::class, 'moderated_by_id');
    }

    /**
     * Get assessment sheets published by this user.
     *
     * @return HasMany<CourseAssessmentSheet, $this>
     */
    public function publishedSheets(): HasMany
    {
        return $this->hasMany(CourseAssessmentSheet::class, 'published_by_id');
    }

    /**
     * Get grade audit logs performed by this user.
     *
     * @return HasMany<GradeAuditLog, $this>
     */
    public function gradeAuditLogs(): HasMany
    {
        return $this->hasMany(GradeAuditLog::class, 'changed_by_id');
    }
}
