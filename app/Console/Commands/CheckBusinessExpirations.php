<?php
namespace App\Console\Commands;
use App\Events\BusinessExpired; use App\Models\Business; use Illuminate\Console\Command;
class CheckBusinessExpirations extends Command { protected $signature='business:check-expirations'; protected $description='Mark active businesses whose subscription has expired as expired.'; public function handle(): int { $count=0; Business::query()->where('status','active')->whereNotNull('expires_at')->where('expires_at','<=',now())->eachById(function (Business $business) use (&$count): void { $business->update(['status'=>'expired']); BusinessExpired::dispatch($business->fresh()); $count++; }); $this->info("Expired {$count} business(es)."); return self::SUCCESS; } }
