<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['nama', 'username', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $primaryKey = 'id_user';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** relasi "mengelola" */
    public function kegiatan(): HasMany
    {
        return $this->hasMany(KegiatanRehabilitasi::class, 'id_user', 'id_user');
    }

    /** relasi "mencatat" */
    public function monitoring(): HasMany
    {
        return $this->hasMany(Monitoring::class, 'id_user', 'id_user');
    }
}
