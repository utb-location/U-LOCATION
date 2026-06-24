<?php
namespace App\Console\Commands;
use App\Services\SmsGateway;
use Illuminate\Console\Command;
use Throwable;
class CheckSmsConnection extends Command {
 protected $signature='sms:check-connection';protected $description='Verifie la connexion au prestataire SMS sans envoyer de message';
 public function handle(SmsGateway $gateway): int {try{$result=$gateway->checkConnection();$this->info('Connexion '.$result['driver'].' valide.');$contracts=$result['contracts']??[];$this->line('Contrats disponibles : '.count($contracts));foreach($contracts as $contract)$this->line('Contrat '.($contract['country']??'-').' : '.($contract['status']??'-').' - SMS disponibles : '.($contract['availableUnits']??0));return self::SUCCESS;}catch(Throwable $e){$this->error($e->getMessage());return self::FAILURE;}}
}
