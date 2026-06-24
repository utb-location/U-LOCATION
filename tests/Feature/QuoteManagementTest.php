<?php
namespace Tests\Feature;
use App\Mail\QuoteResponseMail;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
class QuoteManagementTest extends TestCase {
 use RefreshDatabase;
 private function admin(): User { return User::create(['name'=>'Admin','username'=>'admin-test','email'=>'admin-test@utb.ci','role'=>'admin','password'=>Hash::make('secret')]); }
 private function quote(): QuoteRequest { return QuoteRequest::create(['reference'=>'UTB-LOC-2026-0099','name'=>'Client Test','phone'=>'0500000000','email'=>'client@example.com','departure_date'=>'2026-07-10','origin'=>'Abidjan','destination'=>'Bouake','passengers'=>40,'service_type'=>'Voyage touristique','driver_required'=>true,'status'=>'Nouvelle demande']); }
 public function test_admin_can_view_and_manage_quote(): void { $admin=$this->admin();$quote=$this->quote();$this->actingAs($admin)->get(route('admin.quotes.show',$quote))->assertOk()->assertSee($quote->reference);$this->put(route('admin.quotes.manage',$quote),['status'=>'En cours','internal_notes'=>'Client prioritaire'])->assertRedirect();$this->assertDatabaseHas('quote_requests',['id'=>$quote->id,'status'=>'En cours','internal_notes'=>'Client prioritaire']); }
 public function test_admin_can_send_and_log_email(): void { Mail::fake();$admin=$this->admin();$quote=$this->quote();$this->actingAs($admin)->post(route('admin.quotes.email',$quote),['subject'=>'Votre devis UTB','body'=>'Votre devis est disponible.'])->assertRedirect();Mail::assertSent(QuoteResponseMail::class,fn($mail)=>$mail->hasTo('client@example.com'));$this->assertDatabaseHas('quote_messages',['quote_request_id'=>$quote->id,'recipient'=>'client@example.com','subject'=>'Votre devis UTB']); }
 public function test_admin_can_send_email_with_private_attachment_and_signed_download(): void {
  Storage::fake('local');Mail::fake();$admin=$this->admin();$quote=$this->quote();$file=UploadedFile::fake()->create('devis-utb.pdf',1024,'application/pdf');
  $this->actingAs($admin)->post(route('admin.quotes.email',$quote),['subject'=>'Votre devis PDF','body'=>'Veuillez trouver votre devis.','attachment'=>$file])->assertRedirect();
  $message=$quote->messages()->first();$this->assertSame('devis-utb.pdf',$message->attachment_name);$this->assertTrue($message->shouldAttachToEmail());Storage::disk('local')->assertExists($message->attachment_path);
  Mail::assertSent(QuoteResponseMail::class,fn($mail)=>count($mail->attachments())===1);
  $this->get($message->attachmentUrl())->assertOk()->assertDownload('devis-utb.pdf');
 }
 public function test_large_email_file_uses_secure_link_instead_of_smtp_attachment(): void {
  Storage::fake('local');Mail::fake();$admin=$this->admin();$quote=$this->quote();$file=UploadedFile::fake()->create('documents.zip',25*1024,'application/zip');
  $this->actingAs($admin)->post(route('admin.quotes.email',$quote),['subject'=>'Documents UTB','body'=>'Voici les documents demandes.','attachment'=>$file])->assertRedirect();
  $message=$quote->messages()->first();$this->assertFalse($message->shouldAttachToEmail());Mail::assertSent(QuoteResponseMail::class,fn($mail)=>count($mail->attachments())===0);$this->get($message->attachmentUrl())->assertOk()->assertDownload('documents.zip');
 }
 public function test_commercial_user_can_delete_quote_and_quality_user_cannot(): void {
  $commercial=User::create(['name'=>'Commercial','username'=>'commercial-delete','email'=>'commercial-delete@utb.ci','role'=>'commercial','active'=>true,'must_change_password'=>false,'password'=>Hash::make('secret')]);$quote=$this->quote();
  $this->actingAs($commercial)->delete(route('admin.quotes.destroy',$quote))->assertRedirect(route('admin.quotes.index'));$this->assertDatabaseMissing('quote_requests',['id'=>$quote->id]);
  $quality=User::create(['name'=>'Qualite','username'=>'quality-no-delete','email'=>'quality-no-delete@utb.ci','role'=>'quality','active'=>true,'must_change_password'=>false,'password'=>Hash::make('secret')]);$second=$this->quote();
  $this->actingAs($quality)->delete(route('admin.quotes.destroy',$second))->assertForbidden();$this->assertDatabaseHas('quote_requests',['id'=>$second->id]);
 }
}
