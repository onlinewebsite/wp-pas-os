<?php
class WC_PasOs_Api{
    protected $pasos_api, $pasos_key;
	public function __construct() {
		$this->pasos_api   = rtrim(get_option( 'pasos_api_url' ), '/') . '/';  //
		$this->pasos_key   = get_option( 'pasos_api_key' ); //
    }
    private function pasos_remote($url, $method='POST', $body=array(), $headers = array()){
        //  echo $this->pasos_api.$url;
        $body['userKay'] = $this->pasos_key;
        $response = wp_remote_post( $this->pasos_api.$url, array(
        	'method' => $method,
        	'timeout' => 45,
        	'redirection' => 5,
        	'httpversion' => '1.0',
        	'blocking' => true,
        	'headers' => $headers,
        	'body' =>  json_encode($body),
        	'cookies' => array(),
            'sslverify'   => false
            )
        );
        //return $response;
        if ( is_wp_error( $response ) ) {
           $error_message['error'] = $response->get_error_message();
          // return $error_message;
        } else {
           $response = json_decode( wp_remote_retrieve_body( $response ), true );   return $response;
           if(isset($response['error'])){
                echo '<div class="notice notice-warning"><p>'.$response['message'].'</p></div>';
           } else return $response;
        }
    }
    public function getQuestions($page = 1,$data=[]){
        $response = $this->pasos_remote('questions/page:'.$page, 'POST',$data);
        return $response;
    }
    public function getAnswers($page = 1,$data=[]){
        $response = $this->pasos_remote('answers/page:'.$page, 'POST',$data);
        return $response;
    }
    public function getClients($page = 1,$data=[]){
        $response = $this->pasos_remote('clients/page:'.$page, 'POST',$data);
        return $response;
    }
    public function getMetas(){
        $response = $this->pasos_remote('metas', 'POST',[]);
        return $response;
    }
    public function account(){
        $response = $this->pasos_remote('account', 'POST',[]);
        return $response;
    }
    public function getQuestion($qid = 1){
        $response = $this->pasos_remote('question/'.$qid, 'POST',[]);
        return $response;
    }
    public function getAnswer($aid = 1){
        $response = $this->pasos_remote('getAnswer/'.$aid, 'POST',[]);
        return $response;
    }
    public function sendAnswer($client=[],$answer=[]){
        $response = $this->pasos_remote('sendAnswer', 'POST',['clientData'=>$client,'answerData'=>$answer]);
        return $response;
    }
    public function addClient($client=[]){
        $response = $this->pasos_remote('addClient', 'POST',['clientData'=>$client]);
        return $response;
    }
    public function clientDate($data){
        $clientName = ['clientName'=> addslashes($data['clientName']) ,'bday'=>addslashes($data['bday']),'gender'=>(int)$data['gender'],'marital'=>(int)$data['marital'],'edu'=>addslashes($data['edu'])];
        if(isset($data['city'])) $clientName['city'] = $data['city'];
        if(isset($data['phone'])) $clientName['phone'] = $data['phone'];
        if(isset($data['cid'])) $clientName['cid'] = $data['cid'];
        return $clientName;
    }
    public function answerData($qid,$answer=[]){
        return ['qid'=>$qid,'answer'=> $answer];
    }
}
