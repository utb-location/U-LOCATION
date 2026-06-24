<?php
namespace Tests\Feature;
use App\Models\QuoteRequest;
use App\Models\SmsCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\SmsGateway;
use App\Mail\CampaignEmail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
class SmsCampaignTest extends TestCase {
 use RefreshDatabase;
 private function admin(): User{return User::create(['name'=>'Admin SMS','username'=>'sms-admin','email'=>'sms@utb-ci.net','role'=>'admin','password'=>Hash::make('secret')]);}
 private function client(array $extra=[]): QuoteRequest{return QuoteRequest::create(array_merge(['reference'=>'UTB-LOC-2026-0099','name'=>'Awa Koffi','phone'=>'0701020304','sms_consent'=>true,'sms_consent_at'=>now(),'departure_date'=>now()->addWeek(),'origin'=>'Abidjan','destination'=>'Bouake','passengers'=>20,'service_type'=>'Voyage touristique','driver_required'=>true,'status'=>'Confirmee'],$extra));}
 public function test_admin_creates_personalized_campaign_for_consented_clients(): void {
  $admin=$this->admin();$client=$this->client();
  $response=$this->actingAs($admin)->post(route('admin.sms.store'),['name'=>'Merci clients','sender'=>'UTBLOCATION','message'=>'Bonjour {nom}, voyage vers {destination} le {date}.','audience'=>'selected','recipients'=>[$client->id]]);
  $campaign=SmsCampaign::first();$response->assertRedirect(route('admin.sms.show',$campaign));$this->assertSame(1,$campaign->recipient_count);$this->assertDatabaseHas('sms_messages',['phone'=>'+2250701020304','recipient_name'=>'Awa Koffi']);$this->assertStringContainsString('Bonjour Awa Koffi',$campaign->messages()->first()->message);
 }
 public function test_log_driver_processes_campaign_without_external_sms(): void {
  config(['sms.driver'=>'log']);$admin=$this->admin();$this->client();
  $this->actingAs($admin)->post(route('admin.sms.store'),['name'=>'Fidelite','sender'=>'UTBLOCATION','message'=>'Merci {nom} pour votre confiance.','audience'=>'all']);$campaign=SmsCampaign::first();
  $this->actingAs($admin)->post(route('admin.sms.launch',$campaign))->assertRedirect();$this->assertDatabaseHas('sms_campaigns',['id'=>$campaign->id,'status'=>'Envoyee','sent_count'=>1]);$this->assertDatabaseHas('sms_messages',['sms_campaign_id'=>$campaign->id,'status'=>'Journalise','provider'=>'log']);
 }
 public function test_non_consented_client_is_not_added_to_campaign(): void {
  $admin=$this->admin();$this->client(['sms_consent'=>false,'sms_consent_at'=>null]);
  $this->actingAs($admin)->from(route('admin.sms.create'))->post(route('admin.sms.store'),['name'=>'Fidelite','sender'=>'UTBLOCATION','message'=>'Merci pour votre confiance.','audience'=>'all'])->assertRedirect(route('admin.sms.create'))->assertSessionHasErrors('recipients');
  $this->assertDatabaseCount('sms_campaigns',0);
 }
 public function test_orange_gateway_authenticates_and_sends_expected_payload(): void {
  Cache::clear();config(['sms.driver'=>'orange','sms.orange.base_url'=>'https://api.orange.com','sms.orange.client_id'=>'client-test','sms.orange.client_secret'=>'secret-test','sms.orange.sender_address'=>'+2250000','sms.orange.sender_name'=>'UTBLOCATION']);
  Http::fake(['https://api.orange.com/oauth/v3/token'=>Http::response(['access_token'=>'token-test','expires_in'=>3600]),'https://api.orange.com/smsmessaging/*'=>Http::response(['outboundSMSMessageRequest'=>['resourceURL'=>'https://api.orange.com/sms/123']],201)]);
  $result=app(SmsGateway::class)->send('+2250701020304','Bonjour client','UTBLOCATION');
  $this->assertTrue($result['success']);$this->assertSame('Accepte par Orange',$result['status']);
  Http::assertSent(fn($request)=>str_contains($request->url(),'smsmessaging/v1/outbound/tel%3A%2B2250000/requests')&&$request['outboundSMSMessageRequest']['senderName']==='UTBLOCATION'&&$request['outboundSMSMessageRequest']['address']==='tel:+2250701020304');
 }
 public function test_orange_expired_contract_is_reported_as_failure(): void {
  Cache::clear();config(['sms.driver'=>'orange','sms.orange.base_url'=>'https://api.orange.com','sms.orange.client_id'=>'client-test','sms.orange.client_secret'=>'secret-test','sms.orange.sender_address'=>'+2250000','sms.orange.sender_name'=>'UTBLOCATION']);
  Http::fake(['https://api.orange.com/oauth/v3/token'=>Http::response(['access_token'=>'token-test','expires_in'=>3600]),'https://api.orange.com/smsmessaging/*'=>Http::response(['requestError'=>['policyException'=>['variables'=>['Expired contract. You can buy a new bundle.']]]],403)]);
  $result=app(SmsGateway::class)->send('+2250701020304','Bonjour client','UTBLOCATION');
  $this->assertFalse($result['success']);$this->assertSame('Echec',$result['status']);$this->assertStringContainsString('bundle SMS indisponible',$result['error']);
 }
 public function test_email_campaign_is_sent_without_sms_credit(): void {
  config(['mail.default'=>'array']);Mail::fake();$admin=$this->admin();$client=$this->client(['email'=>'client@example.com','email_consent'=>true,'email_consent_at'=>now()]);
  $this->actingAs($admin)->post(route('admin.sms.store'),['name'=>'Merci par email','channel'=>'email','email_subject'=>'Merci pour votre confiance','message'=>'Bonjour {nom}, merci pour votre confiance.','audience'=>'selected','recipients'=>[$client->id]]);
  $campaign=SmsCampaign::first();$this->actingAs($admin)->post(route('admin.sms.launch',$campaign))->assertRedirect();$this->assertDatabaseHas('sms_messages',['sms_campaign_id'=>$campaign->id,'email'=>'client@example.com','status'=>'Non requis','email_status'=>'Envoye']);Mail::assertSent(CampaignEmail::class,1);
 }
 public function test_email_only_campaign_never_calls_sms_gateway(): void {
  config(['mail.default'=>'array','sms.driver'=>'orange']);Mail::fake();Http::fake();
  $admin=$this->admin();$client=$this->client(['email'=>'client@example.com','email_consent'=>true,'email_consent_at'=>now()]);$this->actingAs($admin)->post(route('admin.sms.store'),['name'=>'Email uniquement','channel'=>'email','email_subject'=>'Message UTB','message'=>'Bonjour {nom}, merci pour votre confiance.','audience'=>'selected','recipients'=>[$client->id]]);
  $campaign=SmsCampaign::first();$this->actingAs($admin)->post(route('admin.sms.launch',$campaign));$this->assertDatabaseHas('sms_messages',['sms_campaign_id'=>$campaign->id,'status'=>'Non requis','email_status'=>'Envoye']);$this->assertSame('Envoyee',$campaign->fresh()->status);Mail::assertSent(CampaignEmail::class,1);Http::assertNothingSent();
 }
 public function test_whatsapp_campaign_creates_prefilled_link_and_tracks_manual_send(): void {
  $admin=$this->admin();$client=$this->client(['whatsapp_consent'=>true,'whatsapp_consent_at'=>now()]);
  $this->actingAs($admin)->post(route('admin.sms.store'),['name'=>'WhatsApp fidelite','channel'=>'whatsapp','message'=>'Bonjour {nom}, merci pour votre confiance.','audience'=>'selected','recipients'=>[$client->id]]);
  $campaign=SmsCampaign::first();$message=$campaign->messages()->first();$this->assertSame('A envoyer',$message->whatsapp_status);$this->assertStringContainsString('wa.me/2250701020304',$message->whatsappUrl());$this->assertStringContainsString('Bonjour%20Awa%20Koffi',$message->whatsappUrl());
  $this->actingAs($admin)->post(route('admin.sms.launch',$campaign));$this->assertSame('A envoyer via WhatsApp',$campaign->fresh()->status);
  $this->actingAs($admin)->post(route('admin.sms.whatsapp.confirm',[$campaign,$message]))->assertRedirect();$this->assertDatabaseHas('sms_messages',['id'=>$message->id,'whatsapp_status'=>'Envoye manuellement']);$this->assertSame('Envoyee',$campaign->fresh()->status);$this->assertSame(1,$campaign->fresh()->whatsapp_sent_count);
 }
 public function test_due_email_campaign_is_sent_automatically_by_scheduler(): void {
  config(['mail.default'=>'array']);Mail::fake();$admin=$this->admin();$client=$this->client(['email'=>'client@example.com','email_consent'=>true,'email_consent_at'=>now()]);
  $campaign=SmsCampaign::create(['name'=>'Email programme','sender'=>'UTBLOCATION','channel'=>'email','email_subject'=>'Votre message UTB','message'=>'Bonjour Awa','audience'=>'selected','status'=>'Programmee','scheduled_at'=>now()->subMinute(),'recipient_count'=>1,'created_by'=>$admin->id]);
  $campaign->messages()->create(['quote_request_id'=>$client->id,'recipient_name'=>$client->name,'phone'=>'','email'=>$client->email,'message'=>'Bonjour Awa','status'=>'Non requis','email_status'=>'En attente','whatsapp_status'=>'Non requis']);
  $this->artisan('sms:send-scheduled')->assertSuccessful();
  $this->assertDatabaseHas('sms_campaigns',['id'=>$campaign->id,'status'=>'Envoyee','email_sent_count'=>1]);$this->assertDatabaseHas('sms_messages',['sms_campaign_id'=>$campaign->id,'email_status'=>'Envoye']);Mail::assertSent(CampaignEmail::class,1);
 }
}
