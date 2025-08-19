<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
     * Get all test attempts by this user
     */
    public function testAttempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    /**
     * Get completed test attempts
     */
    public function completedAttempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class)->completed();
    }

    /**
     * Get passed test attempts
     */
    public function passedAttempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class)->passed();
    }

    /**
     * Get section progress records for this user
     */
    public function sectionProgress(): HasMany
    {
        return $this->hasMany(UserSectionProgress::class);
    }

    /**
     * Get completed section progress records
     */
    public function completedSections(): HasMany
    {
        return $this->hasMany(UserSectionProgress::class)->completed();
    }

    /**
     * Get progress for a specific section
     */
    public function getProgressForSection($sectionId): ?UserSectionProgress
    {
        return $this->sectionProgress()->where('section_id', $sectionId)->first();
    }

    /**
     * Check if user has completed a specific section
     */
    public function hasCompletedSection($sectionId): bool
    {
        return $this->completedSections()->where('section_id', $sectionId)->exists();
    }

    /**
     * Get exercise attempts for this user
     */
    public function exerciseAttempts(): HasMany
    {
        return $this->hasMany(ExerciseAttempt::class);
    }

    /**
     * Get completed exercise attempts
     */
    public function completedExerciseAttempts(): HasMany
    {
        return $this->hasMany(ExerciseAttempt::class)->completed();
    }

    /**
     * Get the latest attempt for a specific exercise
     */
    public function getLatestExerciseAttempt($exerciseId): ?ExerciseAttempt
    {
        return $this->exerciseAttempts()
            ->where('exercise_id', $exerciseId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get the best attempt for a specific exercise
     */
    public function getBestExerciseAttempt($exerciseId): ?ExerciseAttempt
    {
        return $this->exerciseAttempts()
            ->where('exercise_id', $exerciseId)
            ->where('is_completed', true)
            ->orderBy('score', 'desc')
            ->orderBy('time_spent_seconds', 'asc')
            ->first();
    }

    /**
     * Check if user has completed a specific exercise
     */
    public function hasCompletedExercise($exerciseId): bool
    {
        return $this->completedExerciseAttempts()->where('exercise_id', $exerciseId)->exists();
    }
}
