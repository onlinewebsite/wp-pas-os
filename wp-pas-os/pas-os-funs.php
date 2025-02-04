<?php
// Security improvements and code optimization
class PasOs_Handler {
    private static function verify_request($qid = null) {
        if (!isset($_POST['pas_os_form'])) {
            wp_send_json_error('Invalid request format');
        }
        
        parse_str($_POST['pas_os_form'], $form_data);
        $nonce_action = $qid ? 'pas_os-ref-nonce-' . $qid : 'pas_os-ref-nonce';
        
        if (!wp_verify_nonce($form_data['pas_os_nonce'] ?? '', $nonce_action)) {
            wp_send_json_error('Nonce verification failed');
        }
        
        return $form_data;
    }

    private static function sanitize_client_data($data) {
        return [
            'clientName' => sanitize_text_field($data['clientName'] ?? ''),
            'bday'       => absint($data['bday'] ?? 0),
            'gender'     => in_array($data['gender'] ?? '', ['1', '2']) ? $data['gender'] : '1',
            'marital'    => in_array($data['marital'] ?? '', ['1', '2']) ? $data['marital'] : '1',
            'edu'        => sanitize_text_field($data['edu'] ?? ''),
            'city'       => sanitize_text_field($data['city'] ?? ''),
            'phone'      => sanitize_text_field($data['phone'] ?? ''),
            'qid'        => absint($data['qid'] ?? 0)
        ];
    }

    public static function handle_admin_request() {
        try {
            $form_data = self::verify_request();
            $pasos = new WC_PasOs_Api;
            $client_data = self::sanitize_client_data($form_data);
            
            $result = $pasos->addClient($client_data);
            if ($result['status'] != 1) {
                throw new Exception($result['message'] ?? 'Operation failed');
            }
            
            wp_send_json_success([
                'content' => sprintf(
                    '<div class="notice notice-success"><p>کاربر با موفقیت ثبت شد. کد یکتا: <b>%s</b></p></div>',
                    esc_html($result['client']['cid'] ?? '')
                )
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error('<div class="notice notice-error">' . esc_html($e->getMessage()) . '</div>');
        }
    }

    public static function handle_client_request() {
        try {
            $form_data = self::verify_request();
            $current_user = wp_get_current_user();
            $pasos = new WC_PasOs_Api;
            
            $client_data = self::sanitize_client_data($form_data);
            $result = $pasos->addClient($client_data);
            
            if ($result['status'] != 1) {
                throw new Exception($result['message'] ?? 'Operation failed');
            }
            
            update_user_meta($current_user->ID, 'pas_os_cid', sanitize_text_field($result['client']['cid']));
            
            wp_send_json_success([
                'form'    => self::generate_form($form_data['qid']),
                'content' => '<div class="alert alert-success">اطلاعات با موفقیت ثبت شد</div>'
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error('<div class="alert alert-danger">' . esc_html($e->getMessage()) . '</div>');
        }
    }

    public static function handle_answer_submission() {
        try {
            $form_data = self::verify_request($form_data['qid'] ?? 0);
            $current_user = wp_get_current_user();
            $pasos = new WC_PasOs_Api;
            
            $answers = array_map('absint', $form_data['question'] ?? []);
            $answer_data = $pasos->answerData(
                absint($form_data['qid']),
                $answers
            );
            
            $client_id = get_user_meta($current_user->ID, 'pas_os_cid', true);
            $result = $pasos->sendAnswer(
                ['cid' => sanitize_text_field($client_id)],
                $answer_data
            );
            
            if ($result['status'] != 1) {
                throw new Exception($result['message'] ?? 'Submission failed');
            }
            
            $response = [
                'status'  => true,
                'content' => '<div class="alert alert-success">پاسخ‌نامه با موفقیت ثبت شد</div>',
                'data'    => self::sanitize_output($result['data'] ?? '')
            ];
            
            if (!get_option('pas_os_user_access_proccess')) {
                $response['data'] = self::generate_form($form_data['qid'], $answers, 'disabled');
            } else {
                $response['chart'] = self::sanitize_chart_script($result['chart'] ?? '');
            }
            
            wp_send_json_success($response);
            
        } catch (Exception $e) {
            wp_send_json_error('<div class="alert alert-danger">' . esc_html($e->getMessage()) . '</div>');
        }
    }

    private static function sanitize_output($html) {
        return wp_kses_post(str_replace('<table', '<table class="wp-list-table widefat fixed striped"', $html));
    }

    private static function sanitize_chart_script($script) {
        $sanitized = str_replace('$(', 'jQuery(', $script);
        return '<script type="text/javascript">' . wp_kses($sanitized, ['script' => []]) . '</script>';
    }

    public static function generate_form($qid, $answers = [], $disabled = '') {
        if (!is_user_logged_in()) {
            return sprintf(
                '<div class="alert alert-warning">%s</div>
                <a href="%s" class="btn btn-primary">ثبت نام</a>
                <a href="%s" class="btn btn-info">ورود</a>',
                esc_html__('لطفا وارد شوید یا ثبت نام کنید'),
                esc_url(get_option('pas_os_signup_url')),
                esc_url(get_option('pas_os_login_url'))
            );
        }
        
        $current_user = wp_get_current_user();
        if (!get_user_meta($current_user->ID, 'pas_os_cid', true)) {
            return self::generate_client_form($qid);
        }
        
        $pasos = new WC_PasOs_Api;
        $question_data = $pasos->getQuestion(absint($qid));
        
        if (empty($question_data['status']) || $question_data['status'] != 1) {
            return '<div class="alert alert-danger">خطا در دریافت اطلاعات پرسشنامه</div>';
        }
        
        ob_start();
        ?>
        <div class="pas-os-result"></div>
        <div class="pas-os-data">
            <h3><?php echo esc_html($question_data['question']['title']); ?></h3>
            <div class="alert alert-info"><?php echo esc_html($question_data['question']['description']); ?></div>
            <form method="post">
                <?php 
                foreach ($question_data['question']['questions'] as $key => $question) {
                    printf(
                        '<div class="form-group">
                            <label>%s</label>
                            %s
                        </div>',
                        esc_html($question),
                        self::generate_radio_inputs($key, $question_data['question']['answers'], $answers[$key] ?? null, $disabled)
                    );
                }
                ?>
                <input type="hidden" name="qid" value="<?php echo absint($qid); ?>">
                <?php wp_nonce_field('pas_os-ref-nonce-' . $qid, 'pas_os_nonce'); ?>
                <?php if (!$disabled) : ?>
                    <button type="submit" class="btn btn-primary">ثبت پاسخ</button>
                <?php endif; ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function generate_radio_inputs($name, $options, $selected = null, $disabled = '') {
        $html = '';
        foreach ($options as $value => $label) {
            $html .= sprintf(
                '<div class="form-check">
                    <input class="form-check-input" type="radio" name="question[%s]" id="q%s_%s" value="%s" %s %s>
                    <label class="form-check-label" for="q%s_%s">%s</label>
                </div>',
                esc_attr($name),
                esc_attr($name),
                esc_attr($value),
                esc_attr($value),
                $selected == $value ? 'checked' : '',
                $disabled ? 'disabled' : '',
                esc_attr($name),
                esc_attr($value),
                esc_html($label)
            );
        }
        return $html;
    }
}

// AJAX handlers
add_action('wp_ajax_pas_os_ajax_client_admin_request', [PasOs_Handler::class, 'handle_admin_request']);
add_action('wp_ajax_pas_os_ajax_client_request', [PasOs_Handler::class, 'handle_client_request']);
add_action('wp_ajax_pas_os_ajax_request', [PasOs_Handler::class, 'handle_answer_submission']);

// Shortcode implementations
add_shortcode('pas-os', function($atts) {
    $atts = shortcode_atts(['qid' => 0], $atts);
    return PasOs_Handler::generate_form(absint($atts['qid']));
});

add_shortcode('pas-os-answers', function() {
    if (empty($_GET['answer_id'])) return '<div class="alert alert-warning">پارامتر مورد نیاز یافت نشد</div>';
    
    $pasos = new WC_PasOs_Api;
    $result = $pasos->getAnswer(absint($_GET['answer_id']));
    
    if (empty($result['status']) || $result['status'] != 1) {
        return '<div class="alert alert-danger">' . esc_html($result['message'] ?? 'خطا در دریافت اطلاعات') . '</div>';
    }
    
    return wp_kses_post($result['data']) . PasOs_Handler::sanitize_chart_script($result['chart'] ?? '');
});
