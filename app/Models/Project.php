<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'start_date', 'due_date', 'note_to_clients', 'status', 'technician_id', 'client_email'];
    public function technician()
    {

        return $this->belongsTo(Technician::class, 'technician_id');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class, 'project_id', 'project_id');
    }
}
