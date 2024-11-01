<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WOS_Function_Class')):

    class WOS_Function_Class {

        protected static $_instance = null;

        public function __construct() {
            add_action('admin_menu', array($this, 'wos_woocommerce_split_order_menu'));
            if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
                $this->frontend_css_js();
            }
        }

        function wos_woocommerce_split_order_menu() {
            add_submenu_page('woocommerce', 'split_order', 'Split Order', 'manage_options', 'split-order', array($this, 'wos_split_order_menu_callback'));
        }

        public function wos_split_order_menu_callback() {
          require_once wos_sunarc_path . '/inc/configuration.php';
        }

        public function frontend_css_js() {
            //add_action('wp_enqueue_scripts', array($this, 'SUNARC_frontend_scripts'));
            add_action('admin_enqueue_scripts', array($this, 'wos_frontend_scripts'));
            add_action('wp_head', array($this, 'wos_custom_ajax_url'));
            add_action('wp_ajax_wos_select_variation', array($this, 'wos_select_variation'));
        }

        public static function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function wos_select_variation() {
			
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            $taxonomy_terms = array();

            if ($attribute_taxonomies) :
                foreach ($attribute_taxonomies as $tax) :
                    if (taxonomy_exists(wc_attribute_taxonomy_name($tax->attribute_name))) :
                        if ($tax->attribute_name == sanitize_text_field($_POST['id'])) {
                            $taxonomy_terms = get_terms(wc_attribute_taxonomy_name($tax->attribute_name), 'orderby=name&hide_empty=0');
                        }
                    endif;
                endforeach;
            endif;
            foreach ($taxonomy_terms as $term) {
                ?>
                <option value="<?php echo $term->slug; ?>" <?php if (in_array($term->slug, $color)) echo 'selected'; ?>>
                    <?php echo $term->name; ?>
                </option>
                <?php
            }
            die;
        }

        public function wos_frontend_scripts() {
            wp_enqueue_style('woocommerce_admin_styles');
            wp_enqueue_style('wos-custom-style-css', plugins_url('/assets/css/custom_style.css', dirname(__FILE__)), wos_sunarc_version);
            wp_enqueue_script('wos-frontend-js', wos_sunarc_url . 'assets/js/custom.js', array('jquery', 'wp-color-picker'), wos_sunarc_version, true);
        }

        public function wos_custom_ajax_url() {
            $html = '<script type="text/javascript">';
            $html .= 'var ajaxurl = "' . admin_url('admin-ajax.php') . '"';
            $html .= '</script>';
            echo $html;
        }

    }

    endif;
?>