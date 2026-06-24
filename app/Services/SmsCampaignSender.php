<?php
namespace App\Services;
use App\Mail\CampaignEmail;
use App\Models\SmsCampaign;
use App\Models\SmsMessage;
use Illuminate\Support\Facades\Mail;
use Throwable;
class SmsCampaignSender {
 public function __construct(private SmsGateway $gateway){}
 public function send(SmsCampaign $campaign): void {
  if(in_array($campaign->status,['Envoyee','En cours'],true))return;$campaign->update(['status'=>'En cours']);foreach($campaign->messages()->where('is_test',false)->get() as $message)$this->process($campaign,$message);$this->refreshStatus($campaign);
 }
 public function process(SmsCampaign $campaign,SmsMessage $record): SmsMessage {
  if($this->usesSms($campaign)&&$record->phone&&$record->status==='En attente'){try{$result=$this->gateway->send($record->phone,$record->message,$campaign->sender);$record->update(['status'=>$result['status'],'provider'=>$this->gateway->driver(),'provider_message_id'=>$result['id']??null,'error_message'=>$result['error']??null,'provider_response'=>$result['response']??null,'sent_at'=>$result['success']?now():null]);}catch(Throwable $e){report($e);$record->update(['status'=>'Echec','provider'=>$this->gateway->driver(),'error_message'=>$e->getMessage()]);}}
  if($this->usesEmail($campaign)&&$record->email&&$record->email_status==='En attente'){try{Mail::to($record->email)->send(new CampaignEmail($record->recipient_name,$campaign->email_subject?:$campaign->name,$record->message));$logged=config('mail.default')==='log';$record->update(['email_status'=>$logged?'Journalise':'Envoye','email_sent_at'=>$logged?null:now(),'email_error'=>null]);}catch(Throwable $e){report($e);$record->update(['email_status'=>'Echec','email_error'=>$e->getMessage()]);}}
  return $record->fresh();
 }
 public function refreshStatus(SmsCampaign $campaign): void {
  $messages=$campaign->messages()->where('is_test',false);$smsSent=$this->usesSms($campaign)?(clone $messages)->whereIn('status',['Envoye','Accepte par Orange','Journalise','Livre'])->count():0;$smsFailed=$this->usesSms($campaign)?(clone $messages)->where('status','Echec')->count():0;$emailSent=$this->usesEmail($campaign)?(clone $messages)->whereIn('email_status',['Envoye','Journalise'])->count():0;$emailFailed=$this->usesEmail($campaign)?(clone $messages)->where('email_status','Echec')->count():0;$waSent=$this->usesWhatsApp($campaign)?(clone $messages)->where('whatsapp_status','Envoye manuellement')->count():0;$waPending=$this->usesWhatsApp($campaign)?(clone $messages)->where('whatsapp_status','A envoyer')->count():0;$sent=$smsSent+$emailSent+$waSent;$failed=$smsFailed+$emailFailed;$status=$waPending?'A envoyer via WhatsApp':($failed&&$sent?'Partielle':($failed?'Echec':'Envoyee'));
  $campaign->update(['status'=>$status,'sent_count'=>$smsSent,'failed_count'=>$smsFailed,'email_sent_count'=>$emailSent,'email_failed_count'=>$emailFailed,'whatsapp_sent_count'=>$waSent,'sent_at'=>$waPending?null:now()]);
 }
 private function usesSms(SmsCampaign $campaign): bool{return in_array($campaign->channel,['sms','both','all'],true);}
 private function usesEmail(SmsCampaign $campaign): bool{return in_array($campaign->channel,['email','both','all'],true);}
 private function usesWhatsApp(SmsCampaign $campaign): bool{return in_array($campaign->channel,['whatsapp','all'],true);}
}
