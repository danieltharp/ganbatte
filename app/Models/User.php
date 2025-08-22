<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'accepted_contributions',
        'rejected_contributions',
        'can_user_contribute',
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
            'can_user_contribute' => 'boolean',
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

    /**
     * Get all roles assigned to this user
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Check if user has a specific role by name
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Check if user has all of the specified roles
     */
    public function hasAllRoles(array $roleNames): bool
    {
        $userRoles = $this->roles()->pluck('name')->toArray();
        return count(array_intersect($roleNames, $userRoles)) === count($roleNames);
    }

    /**
     * Assign a role to the user
     */
    public function assignRole(string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
            return true;
        }
        return false;
    }

    /**
     * Remove a role from the user
     */
    public function removeRole(string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role->id);
            return true;
        }
        return false;
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is a developer
     */
    public function isDeveloper(): bool
    {
        return $this->hasRole('developer');
    }

    /**
     * Check if user is staff (admin, developer, or staff role)
     */
    public function isStaff(): bool
    {
        return $this->hasAnyRole(['admin', 'developer', 'staff']);
    }

    /**
     * Check if user is a trusted contributor
     */
    public function isTrustedContributor(): bool
    {
        return $this->hasRole('trusted_contributor');
    }

    /**
     * Check if user can manage contributions (staff or trusted contributor)
     */
    public function canManageContributions(): bool
    {
        return $this->hasAnyRole(['admin', 'developer', 'staff', 'trusted_contributor']);
    }

    /**
     * Get total contributions made by user
     */
    public function getTotalContributionsAttribute(): int
    {
        return $this->accepted_contributions + $this->rejected_contributions;
    }

    /**
     * Get contribution acceptance rate as percentage
     */
    public function getContributionAcceptanceRateAttribute(): float
    {
        $total = $this->total_contributions;
        return $total > 0 ? round(($this->accepted_contributions / $total) * 100, 1) : 0;
    }

    /**
     * Increment accepted contributions count
     */
    public function incrementAcceptedContributions(): void
    {
        $this->increment('accepted_contributions');
    }

    /**
     * Increment rejected contributions count
     */
    public function incrementRejectedContributions(): void
    {
        $this->increment('rejected_contributions');
    }

    /**
     * Check if user has made contributions
     */
    public function hasContributions(): bool
    {
        return $this->total_contributions > 0;
    }

    /**
     * Get all contributions made by this user
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    /**
     * Get contributions reviewed by this user
     */
    public function reviewedContributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'reviewer_id');
    }
}
