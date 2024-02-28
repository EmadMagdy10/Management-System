<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['comment', 'project_id', 'technician_id'];
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }
    public function technician()
    {
        return $this->belongsTo(Technician::class, 'technician_id', 'technician_id');
    }
}
