<?php
namespace App\Events;
use App\Models\Business; use Illuminate\Foundation\Events\Dispatchable; use Illuminate\Queue\SerializesModels;
class BusinessExpired { use Dispatchable, SerializesModels; public function __construct(public readonly Business $business) {} }
