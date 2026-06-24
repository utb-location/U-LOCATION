<?php
namespace Tests\Feature;
use App\Models\CustomerFeedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class CustomerFeedbackTest extends TestCase {
    use RefreshDatabase;
    public function test_customer_can_submit_feedback(): void {
        $response=$this->post(route('feedback.store'),['customer_name'=>'Client UTB','email'=>'client@example.com','phone'=>'0700000000','type'=>'Reclamation','category'=>'Ponctualite','rating'=>2,'message'=>'Le depart a accuse un retard important.']);
        $response->assertRedirect(route('home').'#qualite');
        $this->assertDatabaseHas('customer_feedback',['email'=>'client@example.com','type'=>'Reclamation','priority'=>'Haute','status'=>'Nouveau']);
    }
    public function test_admin_can_view_and_process_feedback(): void {
        $admin=User::create(['name'=>'Admin','username'=>'quality-admin','email'=>'admin@example.com','role'=>'admin','password'=>Hash::make('secret')]);
        $feedback=CustomerFeedback::create(['reference'=>'UTB-EXP-TEST','customer_name'=>'Client','email'=>'client@example.com','type'=>'Avis','category'=>'Confort','rating'=>4,'message'=>'Le voyage etait confortable.']);
        $this->actingAs($admin)->get(route('admin.feedback.index'))->assertOk()->assertSee('UTB-EXP-TEST');
        $this->actingAs($admin)->put(route('admin.feedback.update',$feedback),['status'=>'Traite','priority'=>'Normale','admin_response'=>'Client contacte et retour pris en compte.'])->assertRedirect();
        $this->assertDatabaseHas('customer_feedback',['id'=>$feedback->id,'status'=>'Traite','handled_by'=>$admin->id]);
    }
    public function test_quality_user_can_delete_feedback_and_commercial_user_cannot(): void {
        $quality=User::create(['name'=>'Qualite','username'=>'quality-delete','email'=>'quality-delete@example.com','role'=>'quality','active'=>true,'must_change_password'=>false,'password'=>Hash::make('secret')]);
        $feedback=CustomerFeedback::create(['reference'=>'UTB-EXP-DELETE','customer_name'=>'Client','email'=>'client@example.com','type'=>'Avis','category'=>'Confort','rating'=>4,'message'=>'Retour client a supprimer.']);
        $this->actingAs($quality)->delete(route('admin.feedback.destroy',$feedback))->assertRedirect(route('admin.feedback.index'));
        $this->assertDatabaseMissing('customer_feedback',['id'=>$feedback->id]);
        $commercial=User::create(['name'=>'Commercial','username'=>'commercial-no-delete','email'=>'commercial-no-delete@example.com','role'=>'commercial','active'=>true,'must_change_password'=>false,'password'=>Hash::make('secret')]);
        $second=CustomerFeedback::create(['reference'=>'UTB-EXP-KEEP','customer_name'=>'Client','email'=>'client@example.com','type'=>'Avis','category'=>'Confort','rating'=>4,'message'=>'Retour client a conserver.']);
        $this->actingAs($commercial)->delete(route('admin.feedback.destroy',$second))->assertForbidden();
        $this->assertDatabaseHas('customer_feedback',['id'=>$second->id]);
    }
}
