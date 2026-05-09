<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'cv_file',
    ];

    /**
     * علاقة العميل بطلباته
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the full URL for the CV file.
     * (تم نقل هذا من مودل Contact)
     */
    public function getCvFileUrlAttribute()
    {
        return $this->cv_file ? route('media.file', ['path' => ltrim((string) $this->cv_file, '/')]) : null;
    }
}