<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
class SmsGateway {
 public function driver(): string{return (string)config('sms.driver','log');}
 public function configured(): bool{return match($this->driver()){'log'=>true,'infobip'=>filled(config('sms.infobip.base_url'))&&filled(config('sms.infobip.api_key')),'orange'=>filled(config('sms.orange.client_id'))&&filled(config('sms.orange.client_secret'))&&filled(config('sms.orange.sender_address')),default=>false};}
 public function normalizePhone(string $phone): ?string {$digits=preg_replace('/\D+/','',$phone);if(str_starts_with($digits,'00'))$digits=substr($digits,2);if(strlen($digits)===10)$digits=config('sms.country_code','225').$digits;if(strlen($digits)<8||strlen($digits)>15)return null;return '+'.$digits;}
 public function send(string $phone,string $message,?string $sender=null): array {
  $sender=$sender?:config('sms.sender','UTBLOCATION');
  if($this->driver()==='log'){Log::info('SMS TEST',['from'=>$sender,'to'=>$phone,'message'=>$message]);return ['success'=>true,'status'=>'Journalise','id'=>null,'response'=>['mode'=>'log']];}
  if(!$this->configured())throw new RuntimeException('La passerelle SMS n est pas configuree.');
  if($this->driver()==='orange')return $this->sendOrange($phone,$message,$sender);
  if($this->driver()!=='infobip')throw new RuntimeException('Pilote SMS non pris en charge.');
  $response=Http::timeout(20)->withHeaders(['Authorization'=>'App '.config('sms.infobip.api_key'),'Accept'=>'application/json'])->post(rtrim(config('sms.infobip.base_url'),'/').'/sms/2/text/advanced',['messages'=>[['from'=>$sender,'destinations'=>[['to'=>ltrim($phone,'+')]],'text'=>$message]]]);
  $payload=$response->json()?:['body'=>$response->body()];if(!$response->successful())return ['success'=>false,'status'=>'Echec','id'=>null,'error'=>'HTTP '.$response->status(),'response'=>$payload];
  $item=$payload['messages'][0]??[];$group=$item['status']['groupName']??'';$success=!in_array($group,['REJECTED','UNDELIVERABLE','EXPIRED'],true);
  return ['success'=>$success,'status'=>$success?'Envoye':'Echec','id'=>$item['messageId']??null,'error'=>$success?null:($item['status']['description']??'Envoi refuse'),'response'=>$payload];
 }
 public function checkConnection(): array {
  if(!$this->configured())throw new RuntimeException('La passerelle SMS n est pas configuree.');
  if($this->driver()==='orange'){$response=Http::timeout(20)->withToken($this->orangeToken())->acceptJson()->get(rtrim(config('sms.orange.base_url'),'/').'/sms/admin/v1/contracts');if(!$response->successful())throw new RuntimeException('Orange refuse la verification HTTP '.$response->status().'.');return ['driver'=>'orange','contracts'=>$response->json()?:[]];}
  return ['driver'=>$this->driver(),'configured'=>true];
 }
 private function orangeToken(): string {
  return Cache::remember('orange-sms-access-token',3000,function(){$response=Http::timeout(20)->withBasicAuth(config('sms.orange.client_id'),config('sms.orange.client_secret'))->asForm()->acceptJson()->post(rtrim(config('sms.orange.base_url'),'/').'/oauth/v3/token',['grant_type'=>'client_credentials']);if(!$response->successful()||!$response->json('access_token'))throw new RuntimeException('Authentification Orange impossible HTTP '.$response->status().'.');return $response->json('access_token');});
 }
 private function sendOrange(string $phone,string $message,string $sender): array {
  $address='tel:'.$phone;$senderAddress='tel:'.config('sms.orange.sender_address');$encoded=rawurlencode($senderAddress);$payload=['outboundSMSMessageRequest'=>['address'=>$address,'senderAddress'=>$senderAddress,'senderName'=>config('sms.orange.sender_name',$sender),'outboundSMSTextMessage'=>['message'=>$message]]];
  $response=Http::timeout(20)->withToken($this->orangeToken())->acceptJson()->post(rtrim(config('sms.orange.base_url'),'/').'/smsmessaging/v1/outbound/'.$encoded.'/requests',$payload);$body=$response->json()?:['body'=>$response->body()];$resource=$body['outboundSMSMessageRequest']['resourceURL']??null;
  $success=$response->successful();
  return ['success'=>$success,'status'=>$success?'Accepte par Orange':'Echec','id'=>$resource?basename($resource):null,'error'=>$success?null:$this->orangeError($body,$response->status()),'response'=>$body];
 }
 private function orangeError(array $body,int $status): string {
  $details=$body['requestError']['policyException']['variables'][0]??$body['requestError']['serviceException']['text']??null;
  if(is_string($details)&&str_contains(strtolower($details),'expired contract'))return 'Contrat Orange expire ou bundle SMS indisponible. Achetez ou renouvelez un bundle Orange.';
  return is_string($details)?$details:'Refus Orange HTTP '.$status.'.';
 }
}
