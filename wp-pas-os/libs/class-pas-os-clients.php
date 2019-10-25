<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Wc_pasOs_Clients extends WP_List_Table
{
    public function __construct()
        {
            parent::__construct(array(
                'singular' => 'wp_list_text_link',
                'plural' => 'wp_list_test_links',
                'ajax' => false
            ));
            wp_enqueue_script('pasos_ajax_script', PLUGIN_URL . 'assets/js/pasos-admin.js', array('jquery'), '1.0.0', true );
            wp_enqueue_script( 'pasos_ajax_script' );

        }
    public function process_bulk_action() {
        if ( 'new-client' === $this->current_action() ) {
            echo $this->pas_os_client_form();
        }
    }
    public function pas_os_client_form(){
        $html = '<div class="pas-os-result"></div>
            <h3>'.$result['question']['title'].'</h3>';
        $html .= '<div class="notice notice-warning is-dismissible"><p>برای مشاهده و تکمیل پاسخ نامه لطفا اطلاعات زیر را کامل کنید. موارد ستاره دار الزامی می باشد</p></div>';
        $html .= '<div class="pas-os-data" style="padding: 16px; background: #FFFFFF; margin-bottom: 5px; border: 1px solid #EEEEEE">';
        $form = new MyForms('POST','','<div class="form-group row"><div class="col-sm-3">','</div></div><br>','</div><div class="col-sm-9">','','pas-os-client');
        $form->addInput('text','clientName','','required','','نام و نام خانوادگی <span class="text-danger">*</span>','لطفا نام خود را وارد کنید','regular-text');
        $form->addInput('text','bday','','required','','سال تولد شمسی <span class="text-danger">*</span>','لطفا سال تولد خود را با فرمت 1363 وارد نمایید','regular-text');
        $form->addInput('select','gender','','required',['1'=>'آقا','2'=>'خانم'],'جنسیت <span class="text-danger">*</span>','انتخاب کنید','regular-text');
        $form->addInput('select','marital','','required',['1'=>'مجرد','2'=>'متاهل'],'وضعیت تاهل <span class="text-danger">*</span>','انتخاب کنید','regular-text');
        $form->addInput('text','edu','','','','سطح تحصیلات ','سطح  تحصیلات شما (اختیاری)','regular-text');
        $form->addInput('text','city','','','','شهر محل سکونت ','شهر محل سکونت (اختیاری)','regular-text');
        $form->addInput('text','phone','','','','شماره تماس ','شماره تماس (اختیاری)','regular-text');
        $form->addInput('hidden','pas_os_nonce',wp_create_nonce( 'pas_os-ref-nonce' ), '','','','','');
        $form->addContent('<br>');
        $form->addInput('submit','', 'ایجاد کاربر','','','','','button button-primary');
        $html .= $form->printForm();
        $html .= '</div>';
        return $html;
    }
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();// print_r($sortable);
        $alldata = $this->table_data();
        $currentPage = $this->get_pagenum();
        $this->set_pagination_args( array(
            'total_items' => $alldata['paging']['count'],
            'per_page'    => $alldata['paging']['limit']
        ) );
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $alldata['clients'];
    }
    public function get_columns()
    {
        $columns = array(
            'cb'      => '<input type="checkbox" />',
            'name'       => 'نام کاربر',
            'cid'          => 'کد یکتای کاربر',
            'bday'       => 'سال تولد',
            'phone'       => 'شماره تماس',
            'gender'       => 'جنسیت',
            'marital'          => 'تاهل',
            'edu'        => 'تحصیلات',
            'created'          => 'تاریخ ایجاد',
        );
        return $columns;
    }
    public function no_items() {
      echo 'موردی یافت نشد';
    }
    public function column_name( $item ) {
      $order_nonce = wp_create_nonce( 'pasos_cancel_order' );
      $title = '<strong>' . $item['name'] . '</strong>';
      $actions = [
        'bulk-orders' => sprintf( '<a href="?page=%s&cid=%s">لیست پاسخ نامه ها</a>', esc_attr( 'pardanesh-pasos-answers.php' ), absint( $item['cid'] ) ),
      ];
      return $title . $this->row_actions( $actions );
    }
    public function column_created( $item ) {
      return date_i18n('Y-m-d',$item['created']);
    }
    public function column_gender( $item ) {
      return ($item['gender']==1)?'آقا':'خانم';
    }
    public function column_phone( $item ) {
      return ($item['phone']==1)?:'-';
    }
    public function column_marital( $item ) {
      return ($item['marital']==1)?'مجرد':'متاهل';
    }
    public function column_cb( $item ) {
      return sprintf(
        '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['cid']
      );
    }
    public function get_hidden_columns()
    {
        return array();
    }

    public function get_sortable_columns()
    {
        return array('name' => array('name', false),'bday' => array('bday', false),'cid' => array('cid', false),'gender' => array('gender', false),'marital' => array('marital', false),'created' => array('created', false));
    }
    private function table_data()
    {
        $data = ['questions'=>[],'paging'=>['paging'=>['count'=>0]]];
        $filter['limit'] =  20;
        $filter['filter'] = [];
        if(isset($_REQUEST['orderby']) AND $_REQUEST['orderby']!='') $filter['orderby'] = $_REQUEST['orderby'];
        if(isset($_REQUEST['order']) AND $_REQUEST['order']!='') $filter['order'] = $_REQUEST['order'];
        if(isset($_REQUEST['s']) AND $_REQUEST['s']!='') $filter['filter']['name'] = $_REQUEST['s'];
        if(isset($_REQUEST['marital']) AND $_REQUEST['marital']!='') $filter['filter']['marital'] = $_REQUEST['marital'];
        if(isset($_REQUEST['bday']) AND $_REQUEST['bday']!='') $filter['filter']['bday'] = $_REQUEST['bday'];
        if(isset($_REQUEST['cid']) AND $_REQUEST['cid']!='') $filter['filter']['cid'] = $_REQUEST['cid'];
        if(isset($_REQUEST['gender']) AND $_REQUEST['gender']!='') $filter['filter']['gender'] = $_REQUEST['gender'];
        $pasos =  new WC_PasOs_Api;     //  echo $this->get_pagenum();
        $ref_orders = $pasos->getClients($this->get_pagenum(),$filter);
        //print_r($ref_orders);
        if(isset($ref_orders['status']) AND $ref_orders['status']==1){
           $data = $ref_orders;
        }
        return $data;
    }

    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'cid':
            case 'name':
            case 'marital':
            case 'edu':
            case 'bday':
            case 'phone':
            case 'gender':
            case 'created':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
    private function sort_data( $a, $b )
    {
        $orderby = 'cid';
        $order = 'DESC';
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }
        $result = strcmp( $a[$orderby], $b[$orderby] );
        if($order === 'DESC')
        {
            return $result;
        }
        return -$result;
    }
     public function search_box( $text, $input_id ) {  ?>
        <p class="search-box">
          <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
          <input type="search" id="<?= $input_id ?>" name="s" placeholder="نام کاربر" value="<?php _admin_search_query(); ?>" />
          <input type="text" id="cid" name="cid" placeholder="شناسه کاربر" value="<?php echo ( isset( $_REQUEST['cid'] ) ) ? $_REQUEST['cid'] : false; ?>" />
          <?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
      </p>
    <?php }
}