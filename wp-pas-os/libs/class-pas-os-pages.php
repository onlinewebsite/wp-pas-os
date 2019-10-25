<?php
class Pas_Os_Pages
{
    public function __construct()
    {
        //add_action( 'init', array($this, 'pasos_page_actions' ));
        add_action('admin_head', array($this,'pasos_admin_style'));
    	add_action( 'admin_init', array($this, 'register_pasos_settings') );
        add_action( 'admin_menu', array($this, 'admin_menu' ));
        add_action('admin_bar_menu', array($this,'admin_bar_menu') , 95);
        add_action('admin_head', array($this, 'loading_scripts_hicharts'));
        add_action( 'show_user_profile', array($this, 'pas_os_show_extra_profile_fields') );
        add_action( 'edit_user_profile', array($this, 'pas_os_show_extra_profile_fields' ));
        add_action( 'personal_options_update', array($this, 'pas_os_update_profile_fields') );
        add_action( 'edit_user_profile_update', array($this, 'pas_os_update_profile_fields') );
        add_action( 'wp_enqueue_scripts', array($this, 'pas_os_scripts_method' ));
    }
    public function pasos_admin_style() {
        echo '<style>
            #wp-admin-bar-pasos-charge a {
                background-color: #0063C7;
                color: #fff;
            }
        </style>';
    }
    public function loading_scripts_hicharts() {
        if(($_REQUEST['page']=='pardanesh-pasos-answers.php' AND $_GET['action']=='view-answer') OR $_GET['action']=='view-form')
    	echo '<script type="text/javascript" src="https://pas-os.com/theme/online/js/highcharts.js"></script>';
    }

    function pas_os_scripts_method() {
        wp_enqueue_script('highcharts', 'https://pas-os.com/theme/online/js/highcharts.js',array( 'jquery' ),  '1.0.0', false);
        if(get_option('pas_os_style')) {
             wp_enqueue_style('pasos-style', plugins_url( 'wp-pas-os/assets/css/pasos.css' ), false, '1.0.0');
        }
        wp_enqueue_script(
            'pasos-script',
            plugins_url( 'wp-pas-os/assets/js/pasos.js' ),
            array( 'jquery' ) #dependencies
        );
        wp_localize_script(
            'pasos-script',
            'pasos_ajax_obj',
            array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
        );
    }
    function pas_os_update_profile_fields( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        if ( isset( $_POST['pas_os_cid'] ) ) {
            update_user_meta( $user_id, 'pas_os_cid',   $_POST['pas_os_cid']  );
        }
    }
    function pas_os_show_extra_profile_fields( $user ) {
        $pas_os_cid = get_the_author_meta( 'pas_os_cid', $user->ID );
        ?>
        <h3>سامانه سنجش روان</h3>

        <table class="form-table">
            <tr>
                <th><label for="pas-os-cid">Cid کاربر</label></th>
                <td>
                    <input type="text"
                       id="pas-os-cid"
                       name="pas_os_cid"
                       value="<?php echo esc_attr( $pas_os_cid ); ?>"
                       class="regular-text"
                    />
                </td>
            </tr>
        </table>
    <?php }
    public function admin_bar_menu( \WP_Admin_Bar $bar )
    {
        if(get_option( 'pasos_api_url' ) AND get_option( 'pasos_api_key' )){
           // echo get_option( 'woocommerce_pardanesh_pasos_username' );
            $pasos =  new WC_pasos_Api;
            $account = $pasos->account();
            if($account['status'] != 1) return;
            $bar->add_menu( array(
                    'id'     => 'pasos-charge',
                    'parent' => null,
                    'group'  => null,
                    'title'  => ($account['type']>1)?'پاسخ نامه مجاز (<span class="plugin-count">'.$account['balance'].'</span>)':'اعتبار سامانه ('.number_format($account['charge']).')',
                    'href'   => admin_url( 'admin.php?page=pardanesh-pasos.php'),
            ) );
        }
    }

    public function admin_menu()
    {
        add_menu_page(
            'تنظیمات سامانه سنجش روان',
            'سامانه سنجش روان',
            'manage_options',
            'pardanesh-pasos.php',
            array($this,'pardanesh_pasos_options'),
            plugins_url( 'wp-pas-os/assets/pasos.png' ),
            56
        );
        add_submenu_page(
            'pardanesh-pasos.php',
            'پرسش نامه ها',
            'پرسش نامه ها',
            'manage_options',
            'pardanesh-pasos-questions.php',
            array($this,'pardanesh_pasos_questions')
        );
        add_submenu_page(
            'pardanesh-pasos.php',
            'پاسخ نامه ها',
            'پاسخ نامه ها',
            'manage_options',
            'pardanesh-pasos-answers.php',
            array($this,'pardanesh_pasos_answers')
        );

        add_submenu_page(
            'pardanesh-pasos.php',
            'کاربران',
            'لیست کاربران',
            'manage_options',
            'pardanesh-pasos-clients.php',
            array($this,'pardanesh_pasos_clients')
        );

       add_submenu_page(
            'pardanesh-pasos.php',
            'سایت سامانه سنجش روان',
            'راهنمای استفاده',
            'manage_options',
            'https://pas-os.com/pages/plugins',
            ''
        );
       add_submenu_page(
            'pardanesh-pasos.php',
            'سایت سامانه سنجش روان',
            'ورود به سایت',
            'manage_options',
            'https://pas-os.com',
            ''
        );

    }
    public function pardanesh_pasos_options(){ ?>
        <div class="wrap">
        <h1>تنظیمات افزونه سامانه سنجش روان</h1>
        <a href="https://pas-os.com/pages/plugins" target="_top" title="راهنمای استفاده">برای مشاهده راهنمای استفاده اینجا کلیک کنید.</a>
        <form method="post" action="options.php">
            <?php settings_fields( 'pardanesh_pasos-group' ); ?>
            <?php do_settings_sections( 'pardanesh_pasos-group' ); ?>
            <table class="form-table" style="width: 100%">
                <tr valign="top">
                <th scope="row">آدرس وب سرویس سامانه</th>
                <td><input type="text" class="regular-text" name="pasos_api_url" dir="ltr" value="<?php echo esc_attr( get_option('pasos_api_url') ); ?>" />
                    <p class="description">آدرس وب سرویس سامانه سنجش روان. لطفا در صورت عدم اطمینان این ادرس را دستکاری نفرمایید. آدرس پیش فرض :  https://pas-os.com/api_v2/</p>
                </td>
                </tr>
                <tr valign="top">
                <th scope="row">کد Api سامانه سنجش روان</th>
                <td><input type="text" class="regular-text" name="pasos_api_key" dir="ltr" value="<?php echo esc_attr( get_option('pasos_api_key') ); ?>" />
                    <p class="description">کد Api کاربری سامانه سنجش روان . برای دسترسی به این کد لطفا پس از ثبت نام و ارسال مدارک به قسمت کاربری خود مراجعه نمایید</p>
                </td>
                </tr>
                <tr valign="top">
                <th scope="row">دسترسی کاربر به نتیجه پاسخ نامه</th>
                <td><input name="pas_os_user_access_proccess" type="checkbox" id="pas_os_user_access_proccess" value="1" <?= ( get_option('pas_os_user_access_proccess') )?' checked="checked"':''; ?> />
                    <p class="description">در صورت فعال کردن این گزینه به کاربر اجازه مشاهده نتیجه تفسیر پاسخ نامه داده می شود.  در صورتی که مایل به نمایش این قسمت به کاربر خود نیستید تیک این قسمت را بردارید</p>
                </td>
                </tr>
                <tr valign="top">
                <th scope="row">فعال بودن استایل اختصاصی</th>
                <td><input name="pas_os_style" type="checkbox" id="pas_os_style" value="1" <?= ( get_option('pas_os_style') )?' checked="checked"':''; ?> />
                    <p class="description">در صورتی که قالب وردپرس شما با فریم ورک بوتاسترپ نمی باشد و یا استایل برای افزونه سامانه ندارد این گزینه را فعال کنید.</p>
                </td>
                </tr>
                <tr valign="top">
                <th scope="row">آدرس صفحه ورود</th>
                <td><input type="text" class="regular-text" name="pas_os_login_url" dir="ltr" value="<?php echo esc_attr( get_option('pas_os_login_url') ); ?>" />
                    <p class="description">آدرس صفحه ورود کاربران به پنل کاربری سایت را وارد نمایید  . آدرس را به صورت کامل وارد نمایید مانند  https://pas-os.com/pages/plugins</p>
                </td>
                </tr>
                <tr valign="top">
                <th scope="row">آدرس صفحه عضویت</th>
                <td><input type="text" class="regular-text" name="pas_os_signup_url" dir="ltr" value="<?php echo esc_attr( get_option('pas_os_signup_url') ); ?>" />
                    <p class="description" >آدرس صفحه عضویت برای ثبت نام کاربران را وارد نمایید.  آدرس را به صورت کامل وارد نمایید مانند  https://pas-os.com/pages/plugins</p>
                </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
    }
    function register_pasos_settings() {
        //register our settings
        register_setting( 'pardanesh_pasos-group', 'pasos_api_key' );
        register_setting( 'pardanesh_pasos-group', 'pas_os_style' );
        register_setting( 'pardanesh_pasos-group', 'pasos_api_url' );
        register_setting( 'pardanesh_pasos-group', 'pas_os_user_access_proccess' );
        register_setting( 'pardanesh_pasos-group', 'pas_os_login_url' );
        register_setting( 'pardanesh_pasos-group', 'pas_os_signup_url' );
    }
    public function pardanesh_pasos_questions()
    {
            $pasosQuestions = new Wc_pasOs_Questions();
            $pasosQuestions->prepare_items();
            ?>
                <div class="wrap">
                    <div id="icon-users" class="icon32"></div>
                    <?php $pasosQuestions->process_bulk_action(); ?>
                    <h1>لیست آزمون های فعال</h1>
                    <form method="post">
                        <?php $pasosQuestions->search_box('جستجوی آزمون', 'search_questions'); ?>
                        <?php $pasosQuestions->display(); ?>
                    </form>
                </div>
            <?php
    }
    public function pardanesh_pasos_answers()
    {
        $pasosAnswers = new Wc_pasos_Answers();
        $pasosAnswers->prepare_items();
        ?>
            <div class="wrap">
                    <?php $pasosAnswers->process_bulk_action(); ?>
                <h1>لیست پاسخ نامه ها</h1>
                    <form method="post">
                    <?php $pasosAnswers->search_box('جستجوی پاسخ نامه', 'search_questions'); ?>
                    <?php $pasosAnswers->views(); ?>
                    <?php $pasosAnswers->display(); ?>
                    </form>
            </div>
        <?php
    }
    public function pardanesh_pasos_clients()
    {
        $pasosClients = new Wc_pasos_Clients();
        $pasosClients->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">لیست کاربران</h1>
            <a href="<?= admin_url( 'admin.php?page=pardanesh-pasos-clients.php&action=new-client' ) ?>" class="page-title-action">افزودن کاربر جدید</a>
            <hr class="wp-header-end">
            <?php $pasosClients->process_bulk_action(); ?>
            <form method="post">
                <?php // $pasosDepots->prepare_items(); ?>
                <?php $pasosClients->search_box('جستجوی کاربر', 'search_questions'); ?>
                <?php $pasosClients->display(); ?>
            </form>
    </div>
    <?php
    }
}