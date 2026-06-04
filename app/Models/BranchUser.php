<?php

namespace App\Models;

use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BranchUser extends Pivot
{
    use Tenantable;
    
    protected $table = 'branch_users';
    protected $fillable = [
        'company_id',
        'branch_id',
        'user_id',
    ];

    public function company() 
    {
        return $this->belongsTo(Company::class); 
    }

    public function branch() 
    {
        return $this->belongsTo(Branch::class);
    }
    
    public function user() 
    {
        return $this->belongsTo(User::class);
    }
}
