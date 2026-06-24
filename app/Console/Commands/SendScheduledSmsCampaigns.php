<?php
namespace App\Console\Commands;
use App\Models\SmsCampaign;
use App\Services\SmsCampaignSender;
use Illuminate\Console\Command;
class SendScheduledSmsCampaigns extends Command {
 protected $signature='sms:send-scheduled';
 protected $description='Envoie automatiquement les campagnes SMS, email et multicanal arrivees a echeance';
 public function handle(SmsCampaignSender $sender): int{SmsCampaign::where('status','Programmee')->where('scheduled_at','<=',now())->each(fn($campaign)=>$sender->send($campaign));return self::SUCCESS;}
}
