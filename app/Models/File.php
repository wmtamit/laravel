<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['name', 'file_path'];

    public function getFilePathAttribute($value)
    {
        $file = $this->attributes['file_path'];
        if ($file != null) {
            return url('storage/' . $file);
        } else {
            return null;
        }
    }
}
