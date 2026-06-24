<?php
return [
 'driver'=>env('SMS_DRIVER','log'),
 'sender'=>env('SMS_SENDER','UTBLOCATION'),
 'country_code'=>env('SMS_COUNTRY_CODE','225'),
 'infobip'=>['base_url'=>env('INFOBIP_BASE_URL'),'api_key'=>env('INFOBIP_API_KEY')],
 'orange'=>[
  'base_url'=>env('ORANGE_API_BASE_URL','https://api.orange.com'),
  'client_id'=>env('ORANGE_CLIENT_ID'),
  'client_secret'=>env('ORANGE_CLIENT_SECRET'),
  'sender_address'=>env('ORANGE_SENDER_ADDRESS','+2250000'),
  'sender_name'=>env('ORANGE_SENDER_NAME','UTBLOCATION'),
 ],
];
