<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['nama', 'username', 'password', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id_user';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function hasRole(string ...$role): bool
    {
        return in_array($this->role, $role, true);
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'id_user', 'id_user');
    }

    public function punyaRole(string ...$role): bool
    {
        return $this->hasRole(...$role);
    }

    public function siswa()
    {
        return $this->student();
    }
}
