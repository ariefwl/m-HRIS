<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv_hris';
    protected $table = 'Employee';
    protected $primaryKey = 'EmpID';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'NIK', 'nik');
    }
}
