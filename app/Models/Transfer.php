<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table = 'transfers';

    protected $guarded = ['id'];


    public function payers()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payees()
    {
        return $this->belongsTo(User::class, 'payee_id');
    }
}
