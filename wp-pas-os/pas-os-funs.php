<?php
    function pas_os_ajax_admin_request() {
        parse_str($_REQUEST['pas_os_form'], $pasos_form);
        if ( !wp_verify_nonce( $pasos_form['pas_os_nonce'], 'pas_os-ref-nonce-' .$pasos_form['qid'])) {
          exit("pasos.com");
        }
        $result = ['status'=>false,'content'=>'خطا در پردازش اطلاعات'];
        if ( isset($_REQUEST) ) {
            $current_user = wp_get_current_user();
            //array_values($params['question']);
            $pasos =  new WC_PasOs_Api;
            $answerList = array_values($pasos_form['question']);
            $answerData = $pasos->answerData($pasos_form['qid'], $answerList);
            $clientDate = ['cid'=>$pasos_form['cid']];
            $result = $pasos->sendAnswer($clientDate, $answerData);
            if($result['status']==1){
                $result['data'] = str_replace('<table','<table class="wp-list-table widefat fixed striped"',$result['data']);
                $result['chart'] = '<script>function pas_os_chart_js(){'.str_replace('$(','jQuery(', $result['chart']).'} pas_os_chart_js();</script>';
                $result = ['status'=> true,'result'=>$result,'content'=>'<div class="notice notice-success"><p>پاسخ نامه مورد نظر با موفقیت ثبت شد</p></div>'];
            } else{
                $result = ['status'=>false,'content'=>$result['message']];
            }
        }
       if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
          $result = json_encode($result);
          echo $result;
       }
       else {
          header("Location: ".$_SERVER["HTTP_REFERER"]);
       }
       die();
    }
    function pas_os_ajax_request() {
        parse_str($_REQUEST['pas_os_form'], $pasos_form);
        if ( !wp_verify_nonce( $pasos_form['pas_os_nonce'], 'pas_os-ref-nonce-' .$pasos_form['qid'])) {
          exit("pasos.com");
        }
        $result = ['status'=>false,'content'=>'خطا در پردازش اطلاعات'];
        if ( isset($_REQUEST) ) {
            $current_user = wp_get_current_user();
            //array_values($params['question']);
            $pasos =  new WC_PasOs_Api;
            $answerList = array_values($pasos_form['question']);
            $answerData = $pasos->answerData($pasos_form['qid'], $answerList);
            $cid = get_user_meta( $current_user->ID, 'pas_os_cid' , true );
            $clientDate = ['cid'=>$cid];
            $result = $pasos->sendAnswer($clientDate, $answerData);
            if($result['Status']==1){
                if(!get_option('pas_os_user_access_proccess')) $result['data'] = pas_os_form($pasos_form['qid'],$answerList,'disabled');
                $result['chart'] = '<script>function pas_os_chart_js(){'.str_replace('$(','jQuery(', $result['chart']).'} pas_os_chart_js();</script>';
                $result = ['status'=> true,'result'=>$result,'content'=>'<div class="alert alert-success">پاسخ نامه مورد نظر با موفقیت ثبت شد</div>'];
            } else{
                $result = ['status'=>false,'content'=>$result['message']];
            }
        }
       if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
          $result = json_encode($result);
          echo $result;
       }
       else {
          header("Location: ".$_SERVER["HTTP_REFERER"]);
       }
       die();
    }
    function pas_os_ajax_client_request() {
        parse_str($_REQUEST['pas_os_form'], $pasos_form);
        if ( !wp_verify_nonce( $pasos_form['pas_os_nonce'], 'pas_os-ref-nonce-' .$pasos_form['qid'])) {
          exit("pasos.com");
        }
        $result = ['status'=>false,'content'=>'خطا در پردازش اطلاعات'];
        if ( isset($_REQUEST) ) {
            $current_user = wp_get_current_user();
            //array_values($params['question']);
            $pasos =  new WC_PasOs_Api;
            //$answerData = $pasos->answerData($pasos_form['qid'], array_values($pasos_form['question']));
            //$pasos_form['cid'] = '23123144';
            $clientDate = $pasos->clientDate($pasos_form);
            $result = $pasos->addClient($clientDate);
            if($result['Status']==1){
                update_user_meta( $current_user->ID, 'pas_os_cid',   $result['client']['cid']  );
                $result = ['status'=> true,'content'=>'<div class="alert alert-success">کاربر مورد نظر با موفقیت ثبت شد</div>'];
            } else{
                $result = ['status'=>false,'content'=>$result['message']];
            }
        }
       if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
          $result = json_encode($result);
          echo $result;
       }
       else {
          header("Location: ".$_SERVER["HTTP_REFERER"]);
       }
       die();
    }
    function pas_os_ajax_client_admin_request() {
        parse_str($_REQUEST['pas_os_form'], $pasos_form);
        if ( !wp_verify_nonce( $pasos_form['pas_os_nonce'], 'pas_os-ref-nonce')) {
          exit("pasos.com");
        }
        $result = ['status'=>false,'content'=>'خطا در پردازش اطلاعات'];
        if ( isset($_REQUEST) ) {
            $current_user = wp_get_current_user();
            //array_values($params['question']);
            $pasos =  new WC_PasOs_Api;
            //$answerData = $pasos->answerData($pasos_form['qid'], array_values($pasos_form['question']));
            //$pasos_form['cid'] = '23123144';
            $clientDate = $pasos->clientDate($pasos_form);
            $result = $pasos->addClient($clientDate);
            if($result['status']==1){
               // update_user_meta( $current_user->ID, 'pas_os_cid',   $result['client']['cid']  );
                $result = ['status'=> true,'content'=>'<div class="notice notice-success"><p>کاربر مورد نظر با موفقیت ثبت شد.</p>
                  <p><b>کد یکتای کاربر : '.$result['client']['cid'].'</b></p>
                </div>'];
            } else{
                $result = ['status'=>false,'content'=>'<div class="notice notice-warning">'.$result['message'].'</div>'];
            }
        }
       if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
          $result = json_encode($result);
          echo $result;
       }
       else {
          header("Location: ".$_SERVER["HTTP_REFERER"]);
       }
       die();
    }
    add_action( 'wp_ajax_pas_os_ajax_client_admin_request', 'pas_os_ajax_client_admin_request' );
    add_action( 'wp_ajax_pas_os_ajax_client_request', 'pas_os_ajax_client_request' );
    add_action( 'wp_ajax_pas_os_ajax_request', 'pas_os_ajax_request' );
    add_action( 'wp_ajax_pas_os_ajax_admin_request', 'pas_os_ajax_admin_request' );


function pas_os_func( $atts ) {
    $a = shortcode_atts( array(
        'qid' => '',
        'aid' => '',
        'page' => '',
    ), $atts );
    if(isset($atts['qid'])) return pas_os_form($atts['qid']);
    elseif(isset($atts['aid'])) return pas_os_form($atts['qid']);
}
add_shortcode( 'pas-os', 'pas_os_func' );
add_shortcode( 'pas-os-answers', 'pas_os_answers' );
function pas_os_answers($atts){
    $result = 'خطای دریافت';
    if(isset($_GET['answer_id'])){
        $answer_id = $_GET['answer_id'];
        $pasos =  new WC_PasOs_Api;
        $result = $pasos->getAnswer($answer_id); // print_r($result);
        if($result['status']==1){
            $result = $result['data']."\n".' <script type="text/javascript">'.
            str_replace('$(','jQuery(', $result['chart']).'
            </script>';
        } else{
            $result = $result['message'];
        }
    }
    return $result;
}

function pas_os_form($qid,$answers=[],$disabel=''){
    if ( !is_user_logged_in() ) {
        $html = '<div class="alert alert-warning">برای مشاهده لطفا وارد پنل کاربری خود شوید یا ثبت نام کنید.</div>';
        $html .= '<hr /><a href="" type="button" class="btn btn-primary">ثبت نام</a>
                    <a href="" type="button" class="btn btn-info">ورود به کاربری</a><br>';
        return $html;
    }
    $current_user = wp_get_current_user();
    //$user_id = $current_user->ID;
    if(!get_user_meta( $current_user->ID, 'pas_os_cid' , true )){
        return pas_os_client_form($qid);
    }
    $pasos =  new WC_PasOs_Api;
    $result = $pasos->getQuestion($qid); // print_r($result);
    if(!isset($result['status'])) return;
    if($result['status']!='1'){
        return pas_os_error($result['message']);
    }
    $html = '<div class="pas-os-result"></div>
        <div class="pas-os-data"><h3>'.$result['question']['title'].'</h3>';
    $html .= '<div class="alert alert-info">'.$result['question']['description'].'</div>';
    $html .= '';
    $form = new MyForms('POST','','<div class="form-group"><div>','</div></div>','</div><div class="form-check">',$disabel);
    foreach($result['question']['questions'] as $key=>$question){
        $form->addInput('radio','question['.$key.']', '','',$result['question']['answers'],$question.' <span class="text-danger">*</span>','Please Select','form-check-input');
    }
    $form->addInput('hidden','qid',$qid, '','','','','');
    $form->addInput('hidden','pas_os_nonce',wp_create_nonce( 'pas_os-ref-nonce-' . $qid ), '','','','','');
    if(!$disabel) $form->addInput('submit','', 'ثبت پاسخ نامه','','','','','btn btn-primary');
    $html .= $form->printForm();
    $html .= '</div>';
    return $html;
}
function pas_os_error($pm){ return '<div class="alert alert-warning">'.$pm.'</div>';}
function pas_os_client_form($qid){
    $current_user = wp_get_current_user();
    $html = '<div class="pas-os-result"></div>
        <h3>'.$result['question']['title'].'</h3>';
    $html .= '<div class="alert alert-warning">برای مشاهده و تکمیل پاسخ نامه لطفا اطلاعات زیر را کامل کنید. موارد ستاره دار الزامی می باشد</div>';
    $html .= '<div class="pas-os-data">';
    $form = new MyForms('POST','','<div class="form-group row"><div class="col-sm-3">','</div></div>','</div><div class="col-sm-9">','','pas-os-client');
    $form->addInput('text','clientName','','required','','نام و نام خانوادگی <span class="text-danger">*</span>','لطفا نام خود را وارد کنید','form-control');
    $form->addInput('text','bday','','required','','سال تولد شمسی <span class="text-danger">*</span>','لطفا سال تولد خود را با فرمت 1363 وارد نمایید','form-control');
    $form->addInput('select','gender','','required',['1'=>'آقا','2'=>'خانم'],'جنسیت <span class="text-danger">*</span>','انتخاب کنید','form-control');
    $form->addInput('select','marital','','required',['1'=>'مجرد','2'=>'متاهل'],'وضعیت تاهل <span class="text-danger">*</span>','انتخاب کنید','form-control');
    $form->addInput('text','edu','','','','سطح تحصیلات ','سطح  تحصیلات شما (اختیاری)','form-control');
    $form->addInput('text','city','','','','شهر محل سکونت ','شهر محل سکونت (اختیاری)','form-control');
    $form->addInput('text','phone','','','','شماره تماس ','شماره تماس (اختیاری)','form-control');
    $form->addInput('hidden','qid',$qid, '','','','','');
    $form->addInput('hidden','pas_os_nonce',wp_create_nonce( 'pas_os-ref-nonce-' . $qid ), '','','','','');
    $form->addInput('submit','', 'تکمیل مشخصات','','','','','btn btn-primary');
    $html .= $form->printForm();
    $html .= '</div>';
    return $html;
}