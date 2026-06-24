<?php
namespace App\Http\Controllers;
use App\Models\CustomerFeedback;
use App\Models\QuoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
class CustomerFeedbackController extends Controller {
    public function store(Request $request): RedirectResponse {
        $data=$request->validateWithBag('feedback',[
            'quote_reference'=>'nullable|string|max:40',
            'customer_name'=>'required|string|max:150',
            'email'=>'required|email|max:150',
            'phone'=>'nullable|string|max:40',
            'type'=>'required|in:Avis,Reclamation,Suggestion',
            'category'=>'required|in:Chauffeur,Confort,Ponctualite,Securite,Service commercial,Autre',
            'rating'=>'required|integer|min:1|max:5',
            'message'=>'required|string|min:10|max:3000',
        ]);
        $quote=null;
        if(!empty($data['quote_reference']))$quote=QuoteRequest::where('reference',trim($data['quote_reference']))->first();
        unset($data['quote_reference']);
        do{$reference='UTB-EXP-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));}while(CustomerFeedback::where('reference',$reference)->exists());
        $data['reference']=$reference;
        $data['quote_request_id']=$quote?->id;
        if($data['type']==='Reclamation'||$data['rating']<=2)$data['priority']='Haute';
        CustomerFeedback::create($data);
        return redirect(route('home').'#qualite')->with('feedback_success',"Merci. Votre retour a ete enregistre sous la reference {$reference}.");
    }
    public function index(Request $request): View {
        $query=CustomerFeedback::with('quoteRequest')->latest();
        if($request->filled('status'))$query->where('status',$request->string('status'));
        if($request->filled('type'))$query->where('type',$request->string('type'));
        if($request->filled('rating'))$query->where('rating',$request->integer('rating'));
        return view('admin.feedback.index',['feedbackItems'=>$query->paginate(30)->withQueryString(),'metrics'=>[
            'total'=>CustomerFeedback::count(),
            'average'=>round((float)CustomerFeedback::avg('rating'),1),
            'open'=>CustomerFeedback::whereNotIn('status',['Traite','Clos'])->count(),
            'complaints'=>CustomerFeedback::where('type','Reclamation')->whereNotIn('status',['Traite','Clos'])->count(),
        ]]);
    }
    public function show(CustomerFeedback $customerFeedback): View { return view('admin.feedback.show',compact('customerFeedback')); }
    public function update(Request $request,CustomerFeedback $customerFeedback): RedirectResponse {
        $data=$request->validate(['status'=>'required|in:Nouveau,En cours,Traite,Clos','priority'=>'required|in:Basse,Normale,Haute,Critique','admin_response'=>'nullable|string|max:5000']);
        $data['handled_by']=$request->user()->id;
        $data['handled_at']=in_array($data['status'],['Traite','Clos'],true)?now():null;
        $customerFeedback->update($data);
        return back()->with('admin_success','Le retour client a ete mis a jour.');
    }
    public function destroy(CustomerFeedback $customerFeedback): RedirectResponse {
        $reference=$customerFeedback->reference;
        $customerFeedback->delete();
        return redirect()->route('admin.feedback.index')->with('admin_success','Le retour '.$reference.' a ete supprime definitivement.');
    }
}
