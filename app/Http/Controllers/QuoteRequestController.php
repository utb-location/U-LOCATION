<?php
namespace App\Http\Controllers;
use App\Mail\QuoteResponseMail;
use App\Models\QuoteRequest;
use App\Models\QuoteMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
class QuoteRequestController extends Controller {
 public function store(Request $request): RedirectResponse {
  $data=$request->validate(['name'=>'required|string|max:150','organization'=>'nullable|string|max:150','phone'=>'required|string|max:40','sms_consent'=>'nullable|boolean','email_consent'=>'nullable|boolean','whatsapp_consent'=>'nullable|boolean','email'=>'nullable|required_if:email_consent,1|email|max:150','departure_date'=>'required|date','return_date'=>'nullable|date|after_or_equal:departure_date','origin'=>'required|string|max:150','destination'=>'required|string|max:150','passengers'=>'required|integer|min:1|max:80','service_type'=>'required|string|max:100','vehicle_id'=>'nullable|exists:vehicles,id','driver_required'=>'required|boolean','message'=>'nullable|string|max:3000']);
  $data['sms_consent']=(bool)($data['sms_consent']??false);$data['sms_consent_at']=$data['sms_consent']?now():null;$data['email_consent']=(bool)($data['email_consent']??false);$data['email_consent_at']=$data['email_consent']?now():null;$data['whatsapp_consent']=(bool)($data['whatsapp_consent']??false);$data['whatsapp_consent_at']=$data['whatsapp_consent']?now():null;
  $quote=DB::transaction(function()use($data){$next=(int)QuoteRequest::lockForUpdate()->max('id')+1;$data['reference']='UTB-LOC-'.date('Y').'-'.str_pad((string)$next,4,'0',STR_PAD_LEFT);return QuoteRequest::create($data);});
  return back()->with('quote_success','Votre demande a bien ete envoyee. Reference : '.$quote->reference);
 }
 public function show(QuoteRequest $quoteRequest): View { $quoteRequest->load(['vehicle','messages.user']);return view('admin.quotes.show',compact('quoteRequest')); }
 public function updateStatus(Request $request,QuoteRequest $quoteRequest): RedirectResponse { $data=$request->validate(['status'=>'required|in:Nouvelle demande,En cours,Devis envoye,Confirmee,Annulee']);$quoteRequest->update($data);return back()->with('admin_success','Statut mis a jour.'); }
 public function manage(Request $request,QuoteRequest $quoteRequest): RedirectResponse { $data=$request->validate(['status'=>'required|in:Nouvelle demande,En cours,Devis envoye,Confirmee,Annulee','internal_notes'=>'nullable|string|max:5000','sms_consent'=>'nullable|boolean','email_consent'=>'nullable|boolean','whatsapp_consent'=>'nullable|boolean']);$data['sms_consent']=(bool)($data['sms_consent']??false);$data['sms_consent_at']=$data['sms_consent']?($quoteRequest->sms_consent_at?:now()):null;$data['email_consent']=(bool)($data['email_consent']??false)&&filled($quoteRequest->email);$data['email_consent_at']=$data['email_consent']?($quoteRequest->email_consent_at?:now()):null;$data['whatsapp_consent']=(bool)($data['whatsapp_consent']??false);$data['whatsapp_consent_at']=$data['whatsapp_consent']?($quoteRequest->whatsapp_consent_at?:now()):null;$quoteRequest->update($data);return back()->with('admin_success','Demande mise a jour.'); }
 public function sendEmail(Request $request,QuoteRequest $quoteRequest): RedirectResponse {
  if(!$quoteRequest->email)return back()->withErrors(['email'=>'Ce client n a pas renseigne d adresse email.']);
  $data=$request->validate(['subject'=>'required|string|max:180','body'=>'required|string|max:10000','attachment'=>['nullable','file','max:'.config('attachments.max_kilobytes',307200),'mimes:'.config('attachments.extensions')]]);
  $testMode=config('mail.default')==='log';
  $file=$request->file('attachment');$path=$file?->store('quote-attachments/'.$quoteRequest->id,'local');
  $message=$quoteRequest->messages()->create(['user_id'=>$request->user()->id,'recipient'=>$quoteRequest->email,'subject'=>$data['subject'],'body'=>$data['body'],'attachment_name'=>$file?->getClientOriginalName(),'attachment_path'=>$path,'attachment_mime'=>$file?->getMimeType(),'attachment_size'=>$file?->getSize()]);
  try { Mail::to($quoteRequest->email)->send(new QuoteResponseMail($quoteRequest,$data['subject'],$data['body'],$message)); }
  catch(Throwable $exception){report($exception);if($path)Storage::disk('local')->delete($path);$message->delete();return back()->withErrors(['email'=>'Echec de l envoi. Verifiez la configuration SMTP et la taille du fichier.']);}
  $message->update(['sent_at'=>$testMode?null:now()]);
  return $testMode
   ? back()->with('mail_warning','Mode test : le message a ete journalise mais aucun email externe n a ete livre.')
   : back()->with('admin_success','Email transmis au serveur SMTP et ajoute a l historique.');
 }
 public function downloadAttachment(QuoteMessage $quoteMessage): StreamedResponse {
  abort_unless($quoteMessage->attachment_path&&Storage::disk('local')->exists($quoteMessage->attachment_path),404);
  return Storage::disk('local')->download($quoteMessage->attachment_path,$quoteMessage->attachment_name,['Content-Type'=>$quoteMessage->attachment_mime]);
 }
 public function destroy(QuoteRequest $quoteRequest): RedirectResponse {
  $reference=$quoteRequest->reference;
  $quoteRequest->messages->each(fn($message)=>$message->attachment_path?Storage::disk('local')->delete($message->attachment_path):null);
  $quoteRequest->delete();
  return redirect()->route('admin.quotes.index')->with('admin_success','La demande '.$reference.' a ete supprimee definitivement.');
 }
}
