<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class PasOs_Questions_List_Table extends WP_List_Table
{
    private $api;
    private $nonce_action = 'pasos_questions_action';

    public function __construct()
    {
        parent::__construct([
            'singular' => 'question',
            'plural'   => 'questions',
            'ajax'     => true
        ]);

        $this->api = new WC_PasOs_Api();
        
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function enqueue_admin_assets()
    {
        wp_enqueue_script(
            'pasos-admin',
            PLUGIN_URL . 'assets/js/pasos-admin.js',
            ['jquery'],
            filemtime(PLUGIN_PATH . 'assets/js/pasos-admin.js'),
            true
        );

        wp_localize_script('pasos-admin', 'pasosData', [
            'ajaxUrl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce($this->nonce_action),
            'confirmMsg' => esc_html__('آیا از انجام این عمل اطمینان دارید؟', 'pas-os')
        ]);
    }

    public function prepare_items()
    {
        $this->_column_headers = [
            $this->get_columns(),
            $this->get_hidden_columns(),
            $this->get_sortable_columns()
        ];

        $current_page = $this->get_pagenum();
        $filter = $this->get_sanitized_filters();
        
        $data = $this->api->getQuestions(
            $current_page,
            $this->get_items_per_page('questions_per_page'),
            $filter
        );

        $this->set_pagination_args([
            'total_items' => $data['paging']['count'] ?? 0,
            'per_page'    => $data['paging']['limit'] ?? 20
        ]);

        $this->items = $data['questions'] ?? [];
    }

    private function get_sanitized_filters(): array
    {
        return [
            'orderby' => $this->sanitize_orderby($_REQUEST['orderby'] ?? 'qid'),
            'order'   => $this->sanitize_order($_REQUEST['order'] ?? 'DESC'),
            's'       => sanitize_text_field($_REQUEST['s'] ?? ''),
            'price'   => absint($_REQUEST['price'] ?? 0),
            'category' => absint($_REQUEST['category'] ?? 0),
            'groups'   => absint($_REQUEST['groups'] ?? 0)
        ];
    }

    private function sanitize_orderby(string $orderby): string
    {
        $allowed = ['title', 'qid', 'price', 'category', 'groups'];
        return in_array($orderby, $allowed) ? $orderby : 'qid';
    }

    private function sanitize_order(string $order): string
    {
        return strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
    }

    public function get_columns()
    {
        return [
            'cb'         => '<input type="checkbox" />',
            'title'      => esc_html__('عنوان آزمون', 'pas-os'),
            'category'   => esc_html__('دسته بندی', 'pas-os'),
            'groups'     => esc_html__('گروه سنی', 'pas-os'),
            'qid'        => esc_html__('شماره آزمون', 'pas-os'),
            'shortcode'  => esc_html__('کد کوتاه', 'pas-os'),
            'price'      => esc_html__('قیمت به ریال', 'pas-os'),
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'qid':
                return absint($item[$column_name]);
            
            case 'title':
                return $this->column_title($item);
            
            case 'price':
                return $this->format_price($item[$column_name]);
            
            case 'category':
            case 'groups':
                return esc_html($item[$column_name]['title'] ?? '');
            
            case 'shortcode':
                return $this->get_shortcode($item['qid']);
            
            default:
                return esc_html(print_r($item, true));
        }
    }

    private function format_price($price): string
    {
        if (!is_numeric($price)) return esc_html__('نامعلوم', 'pas-os');
        return ($price == 0) ? 
            esc_html__('رایگان', 'pas-os') : 
            number_format_i18n($price) . ' ' . esc_html__('ریال', 'pas-os');
    }

    private function get_shortcode(int $qid): string
    {
        return sprintf(
            '<code dir="ltr">[pas-os qid="%d"]</code>',
            absint($qid)
        );
    }

    public function column_title(array $item): string
    {
        $title = sprintf(
            '<strong>%s</strong>',
            esc_html($item['title'])
        );

        $actions = [
            'answers' => sprintf(
                '<a href="?page=%s&qid=%d">%s</a>',
                esc_attr('pardanesh-pasos-answers'),
                absint($item['qid']),
                esc_html__('لیست پاسخ‌نامه‌ها', 'pas-os')
            ),
            'view' => sprintf(
                '<a href="?page=%s&action=view-form&qid=%d" class="pasos-question">%s</a>',
                esc_attr('pardanesh-pasos-questions'),
                absint($item['qid']),
                esc_html__('مشاهده', 'pas-os')
            )
        ];

        return $title . $this->row_actions($actions);
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />',
            absint($item['qid'])
        );
    }

    public function get_sortable_columns()
    {
        return [
            'title'    => ['title', false],
            'qid'      => ['qid', false],
            'price'    => ['price', false],
            'category' => ['category', false],
            'groups'   => ['groups', false]
        ];
    }

    public function search_box($text, $input_id)
    {
        $metas = $this->api->getMetas();
        ?>
        <div class="pasos-search-box">
            <p class="search-box">
                <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>">
                    <?php echo esc_html($text); ?>
                </label>
                
                <input type="search" 
                    id="<?php echo esc_attr($input_id); ?>" 
                    name="s" 
                    placeholder="<?php esc_attr_e('عنوان آزمون', 'pas-os'); ?>"
                    value="<?php _admin_search_query(); ?>"
                />
                
                <input type="number" 
                    id="price" 
                    name="price" 
                    placeholder="<?php esc_attr_e('قیمت آزمون', 'pas-os'); ?>"
                    value="<?php echo isset($_REQUEST['price']) ? absint($_REQUEST['price']) : ''; ?>"
                />

                <?php if ($metas['status'] === 1) : ?>
                    <select name="category">
                        <option value=""><?php esc_html_e('دسته بندی', 'pas-os'); ?></option>
                        <?php foreach ($metas['category'] as $key => $value) : ?>
                            <option 
                                value="<?php echo absint($key); ?>"
                                <?php selected(isset($_REQUEST['category']) && $_REQUEST['category'] == $key); ?>
                            >
                                <?php echo esc_html($value); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="groups">
                        <option value=""><?php esc_html_e('گروه سنی', 'pas-os'); ?></option>
                        <?php foreach ($metas['groups'] as $key => $value) : ?>
                            <option 
                                value="<?php echo absint($key); ?>"
                                <?php selected(isset($_REQUEST['groups']) && $_REQUEST['groups'] == $key); ?>
                            >
                                <?php echo esc_html($value); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <?php submit_button($text, 'button', '', false, ['id' => 'search-submit']); ?>
            </p>
        </div>
        <?php
    }

    public function process_bulk_action()
    {
        if ('view-form' === $this->current_action() && !empty($_GET['qid'])) {
            echo $this->render_question_form(absint($_GET['qid']));
        }
    }

    private function render_question_form(int $qid): string
    {
        $result = $this->api->getQuestion($qid);
        
        if (!isset($result['status']) || $result['status'] !== 1) {
            return $this->render_error_message(
                $result['message'] ?? esc_html__('خطا در دریافت اطلاعات پرسشنامه', 'pas-os')
            );
        }

        ob_start();
        ?>
        <div class="pasos-admin-form">
            <div class="notice notice-info">
                <p><?php echo esc_html($result['question']['description']); ?></p>
            </div>

            <form method="post" class="pasos-question-form">
                <?php $this->render_form_header(); ?>
                <?php $this->render_form_fields($result); ?>
                <?php $this->render_form_footer($qid); ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_form_header(): void
    {
        ?>
        <div class="notice notice-warning">
            <p><?php esc_html_e('برای ثبت پاسخ‌نامه، تعریف کاربر الزامی است.', 'pas-os'); ?></p>
        </div>
        <?php
    }

    private function render_form_fields(array $result): void
    {
        ?>
        <div class="form-section">
            <label for="pasos-cid">
                <?php esc_html_e('شناسه یکتای کاربر:', 'pas-os'); ?>
                <span class="text-danger">*</span>
            </label>
            <input 
                type="text" 
                id="pasos-cid" 
                name="cid" 
                required
                class="regular-text"
                placeholder="<?php esc_attr_e('کد یکتای کاربر را وارد نمایید', 'pas-os'); ?>"
            >
            <p class="description">
                <?php esc_html_e('برای ارسال پاسخ‌نامه ابتدا کاربر را از قسمت کاربران ثبت کنید', 'pas-os'); ?>
            </p>
        </div>

        <?php foreach ($result['question']['questions'] as $key => $question) : ?>
            <fieldset class="form-fieldset">
                <legend><?php echo esc_html($question); ?></legend>
                <?php foreach ($result['question']['answers'] as $value => $label) : ?>
                    <label class="form-radio-label">
                        <input 
                            type="radio" 
                            name="question[<?php echo absint($key); ?>]" 
                            value="<?php echo esc_attr($value); ?>"
                            required
                        >
                        <?php echo esc_html($label); ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
        <?php
    }

    private function render_form_footer(int $qid): void
    {
        wp_nonce_field('pasos_question_submit', 'pasos_question_nonce');
        ?>
        <input type="hidden" name="qid" value="<?php echo absint($qid); ?>">
        <?php submit_button(esc_html__('ثبت پاسخ‌نامه', 'pas-os'), 'primary'); ?>
        <?php
    }

    private function render_error_message(string $message): string
    {
        return sprintf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html($message)
        );
    }

    public function no_items()
    {
        echo esc_html__('موردی یافت نشد', 'pas-os');
    }
}
