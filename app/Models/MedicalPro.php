<?php

namespace App\Models; // <--- GARDE UNIQUEMENT CELUI-LÀ

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class MedicalPro extends Model 
{
    protected $table = 'medical_pros';
    protected $primaryKey = 'id';
    protected $keyType = 'string'; 
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function profile() {
        return $this->belongsTo(User::class, 'id', 'id');
    }
}