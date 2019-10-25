<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Wc_pasOs_Answers extends WP_List_Table
{
    public function __construct()
        {
            parent::__construct(array(
                'singular' => 'wp_list_text_link',
                'plural' => 'wp_list_test_links',
                'ajax' => false
            ));
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
        $this->items = $alldata['answers'];
    }
    public function get_columns()
    {
        $columns = array(
            'cb'      => '<input type="checkbox" />',
            'question'       => 'عنوان آزمون',
            'client'       => 'نام کاربر',
            'aid'       => 'شماره پاسخ نامه',
            'qid'          => 'شماره آزمون',
            'answerd'          => 'زمان تکمیل',
            'price'        => 'قیمت به ریال',
        );
        return $columns;
    }
    public function no_items() {
      echo 'موردی یافت نشد';
    }
    public function column_question( $item ) {
      $order_nonce = wp_create_nonce( 'pasos_cancel_order' );
      $title = '<strong>' . $item['question']['title'] . '</strong>';
      $actions = [
        'bulk-orders' => sprintf( '<a href="?page=%s&action=view-answer&aid=%s">مشاهده پاسخ نامه</a>', esc_attr( $_REQUEST['page'] ), absint( $item['aid'] ) ),
      ];
      return $title . $this->row_actions( $actions );
    }
    public function process_bulk_action() {
        if ( 'view-answer' === $this->current_action() ) {
            $pasos =  new WC_PasOs_Api;
            $result = $pasos->getAnswer(absint( $_GET['aid'] )); // print_r($result);
            if($result['status']==1){
                echo str_replace('<table','<table class="wp-list-table widefat fixed striped"',$result['data'])."\n".' <script type="text/javascript">'.
                str_replace('$(','jQuery(', $result['chart']).'
                </script>';
            } else{
                echo $result['Error'];
            }
        }
    }

    public function column_price( $item ) {
      return ($item['question']['price']==0)?'رایگان':number_format($item['question']['price']).' ریال';
    }
    public function column_client( $item ) {
      return $item['client']['name'];
    }
    public function column_answerd( $item ) {
      return (!$item['answerd'])?'-':date_i18n('Y-m-d', $item['answerd']);
    }
    public function column_cb( $item ) {
      return sprintf(
        '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['aid']
      );
    }
    public function get_hidden_columns()
    {
        return array();
    }

    public function get_sortable_columns()
    {
        return array('question' => array('qid', false),'aid' => array('aid', false),'client' => array('cid', false),'answerd' => array('answerd', false));
    }
    private function table_data()
    {
        $data = ['questions'=>[],'paging'=>['paging'=>['count'=>0]]];
        $filter['limit'] =  20;
        $filter['filter'] = [];
        if(isset($_REQUEST['orderby']) AND $_REQUEST['orderby']!='') $filter['orderby'] = $_REQUEST['orderby'];
        if(isset($_REQUEST['order']) AND $_REQUEST['order']!='') $filter['order'] = $_REQUEST['order'];
        if(isset($_REQUEST['s']) AND $_REQUEST['s']!='') $filter['filter']['qid'] = $_REQUEST['s'];
        if(isset($_REQUEST['qid']) AND $_REQUEST['qid']!='') $filter['filter']['qid'] = $_REQUEST['qid'];
        if(isset($_REQUEST['aid']) AND $_REQUEST['aid']!='') $filter['filter']['aid'] = $_REQUEST['aid'];
        if(isset($_REQUEST['cid']) AND $_REQUEST['cid']!='') $filter['filter']['cid'] = $_REQUEST['cid'];
        if(isset($_REQUEST['groups']) AND $_REQUEST['groups']!='') $filter['filter']['groups'] = $_REQUEST['groups'];
        $pasos =  new WC_PasOs_Api;     //  echo $this->get_pagenum();
        $answers = $pasos->getAnswers($this->get_pagenum(),$filter);
        // print_r($answers);
        if(isset($answers['status']) AND $answers['status']==1){
           $data = $answers;
        }
        return $data;
    }

    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'qid':
            case 'question':
            case 'price':
            case 'answerd':
            case 'client':
            case 'aid':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
    private function sort_data( $a, $b )
    {
        $orderby = 'aid';
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
          <input type="search" id="<?= $input_id ?>" name="s" placeholder="شماره آزمون" value="<?php _admin_search_query(); ?>" />
          <input type="text" id="aid" name="aid" placeholder="شناسه پاسخ نامه" value="<?php echo ( isset( $_REQUEST['aid'] ) ) ? $_REQUEST['aid'] : false; ?>" />
          <?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
      </p>
    <?php }
}