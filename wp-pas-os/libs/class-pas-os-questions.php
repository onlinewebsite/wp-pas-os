<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Wc_pasOs_Questions extends WP_List_Table
{
    public function __construct()
        {
            parent::__construct(array(
                'singular' => 'wp_list_text_link',
                'plural' => 'wp_list_test_links',
                'ajax' => true
            ));
            wp_enqueue_script('pasos_ajax_script', PLUGIN_URL . 'assets/js/pasos-admin.js', array('jquery'), '1.0.0', true );
            wp_enqueue_script( 'pasos_ajax_script' );
        }
    public function pas_os_form($qid,$answers=[],$disabel=''){
        $pasos =  new WC_PasOs_Api;
        $result = $pasos->getQuestion($qid); // print_r($result);
        if(!isset($result['status'])) return;
        if($result['status']!=1){
            return pas_os_error($result['message']);
        }
        $html = '<div class="pas-os-result"></div>
            <div class="pas-os-data"><h3>'.$result['question']['title'].'</h3>';
        $html .= '<div class="notice notice-success is-dismissible">'.$result['question']['description'].'</div>';
        $html .= '';
        $form = new MyForms('POST','','<div style="padding: 10px; background: #FFFFFF; margin-bottom: 5px; border: 1px solid #EEEEEE" class="form-group"><div>','</div></div>','</p><p>',$disabel);
        $form->addContent('<div class="notice notice-warning is-dismissible"><p>برای ثبت پاسخ نامه تعریف کاربر الزامی می باشد. در صورتی که قبلا کاربر را تعریف کرده اید کد یکتای کاربر را از لیست کاربران انتخاب و  وارد کنید</p></div>');
        $form->addInput('text','cid','','required','','شناسه یکتای کاربر <span class="text-danger">*</span>','شناسه یکتای کاربر را وارد نمایید','regular-text','برای ارسال پاسخ نامه ابتدا کاربر را از قسمت کاربران ثبت کنید و کد یکتای کاربر را وارد نمایید');
        foreach($result['question']['questions'] as $key=>$question){
            $form->addInput('radio','question['.$key.']', '','',$result['question']['answers'],$question.' <span class="text-danger">*</span>','Please Select','form-check-input');
        }
        //$form->addContent('1234543545');
        $form->addInput('hidden','qid',$qid, '','','','','');
        $form->addInput('hidden','pas_os_nonce',wp_create_nonce( 'pas_os-ref-nonce-' . $qid ), '','','','','');
        if(!$disabel) $form->addInput('submit','', 'ثبت پاسخ نامه','','','','','button button-primary');
        $html .= $form->printForm();
        $html .= '</div>';
        return $html;
    }
    public function prepare_items()
    {
        //$this->process_bulk_action();
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
        $this->items = $alldata['questions'];
    }
    public function get_columns()
    {
        $columns = array(
            'cb'      => '<input type="checkbox" />',
            'title'       => 'عنوان آزمون',
            'category'       => 'دسته بندی',
            'groups'       => 'گروه سنی',
            'qid'          => 'شماره آزمون',
            'shortcode'          => 'کد کوتاه',
            'price'        => 'قیمت به ریال',
        );
        return $columns;
    }
    public function no_items() {
      echo 'موردی یافت نشد';
    }
    public function column_title( $item ) {
      $order_nonce = wp_create_nonce( 'pasos_cancel_order' );
      $title = '<strong>' . $item['title'] . '</strong>';
      $actions = [
        'bulk-orders' => sprintf( '<a href="?page=%s&qid=%s">لیست پاسخ نامه ها</a>', esc_attr( 'pardanesh-pasos-answers.php' ), absint( $item['qid'] ) ),
        'bulk-cancel' => sprintf( '<a href="?page=%s&action=view-form&qid=%s" class="pasos-question">مشاهده</a>',esc_attr( 'pardanesh-pasos-questions.php' ),  absint( $item['qid'] ))
      ];
      return $title . $this->row_actions( $actions );
    }
    public function column_groups( $item ) {
      return $item['groups']['title'];
    }
    public function column_price( $item ) {
      return ($item['price']==0)?'رایگان':number_format($item['price']).' ریال';
    }
    public function column_category( $item ) {
      return $item['category']['title'];
    }
    public function column_shortcode( $item ) {
      return '<span dir="ltr">[pas-os qid="'.$item['qid'].'"]</span>';
    }
    public function column_cb( $item ) {
      return sprintf(
        '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['qid']
      );
    }
    public function get_hidden_columns()
    {
        return array();
    }

    public function get_sortable_columns()
    {
        return array('title' => array('title', false),'qid' => array('qid', false),'price' => array('price', false),'category' => array('category', false),'groups' => array('groups', false));
    }
    private function table_data()
    {
        $data = ['questions'=>[],'paging'=>['paging'=>['count'=>0]]];
        $filter['limit'] =  20;
        $filter['filter'] = [];
        if(isset($_REQUEST['orderby']) AND $_REQUEST['orderby']!='') $filter['orderby'] = $_REQUEST['orderby'];
        if(isset($_REQUEST['order']) AND $_REQUEST['order']!='') $filter['order'] = $_REQUEST['order'];
        if(isset($_REQUEST['s']) AND $_REQUEST['s']!='') $filter['filter']['title'] = $_REQUEST['s'];
        if(isset($_REQUEST['price']) AND $_REQUEST['price']!='') $filter['filter']['price'] = $_REQUEST['price'];
        if(isset($_REQUEST['category']) AND $_REQUEST['category']!='') $filter['filter']['category'] = $_REQUEST['category'];
        if(isset($_REQUEST['groups']) AND $_REQUEST['groups']!='') $filter['filter']['groups'] = $_REQUEST['groups'];
        $pasos =  new WC_PasOs_Api;     //  echo $this->get_pagenum();
        $ref_orders = $pasos->getQuestions($this->get_pagenum(),$filter);
        //print_r($ref_orders);
        if(isset($ref_orders['status']) AND $ref_orders['status']==1){
           $data = $ref_orders;
        }
        return $data;
    }
    public function process_bulk_action() {
        if ( 'view-form' === $this->current_action() ) {
            echo $this->pas_os_form(absint( $_GET['qid'] ));
        }
    }
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'qid':
            case 'title':
            case 'price':
            case 'category':
            case 'groups':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
    private function sort_data( $a, $b )
    {
        $orderby = 'qid';
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
     public function search_box( $text, $input_id ) {
         $pasos =  new WC_PasOs_Api; $metas = $pasos->getMetas(); //print_r($metas); ?>
        <p class="search-box">
          <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
          <input type="search" id="<?= $input_id ?>" name="s" placeholder="عنوان آزمون" value="<?php _admin_search_query(); ?>" />
          <input type="text" id="price" name="price" placeholder="قیمت آزمون" value="<?php echo ( isset( $_REQUEST['price'] ) ) ? $_REQUEST['price'] : false; ?>" />
          <?php if($metas['status']==1){ ?>
          <select name="category">
              <option value="">دسته بندی</option>
              <?php foreach($metas['category'] as $kk=>$vv) echo '<option value="'.$kk.'">'.$vv.'</div>';?>
          </select>
          <select name="groups">
              <option value="">گروه سنی</option>
                <?php foreach($metas['groups'] as $kg=>$vg) echo '<option value="'.$kg.'">'.$vg.'</div>';?>
          </select>
          <?php } ?>
          <?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
      </p>
    <?php }
}