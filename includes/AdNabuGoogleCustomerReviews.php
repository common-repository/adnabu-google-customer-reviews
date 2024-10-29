<?php
/**
 * Created by PhpStorm.
 * User: Mahaveer Chouhan
 * Date: 13/11/18
 * Time: 6:17 PM
 */

class AdNabuGoogleCustomerReviews extends AdNabuPixelBase {
     public static $app_prefix = "adnabu_woocommerce_google_customer_reviews_";
     public static $app_id = 'GOOGLE_CUSTOMER_REVIEWS';
     public static $app_version = "1.0.0";
     public static $app_name = "AdNabu Google Customer Reviews";

     function __construct() {
         $this->app_dir = plugin_dir_path( dirname( __FILE__, 1 ) );
         $this->app_dir_url = plugins_url(basename(dirname(__FILE__,2)));
     }


     function activate_app(){
         set_transient($this::$app_prefix, 1, 5);
         self::activate();
         if(!get_option($this::$app_prefix . "merchant_id")){
             update_option($this::$app_prefix . "reviews_status",'on');
             update_option($this::$app_prefix . "opt_in_position","CENTER_DIALOG");
             update_option($this::$app_prefix . "expected_delivery_in_days",5);
             update_option($this::$app_prefix . "rating_badge_status", "off");
             update_option($this::$app_prefix . "rating_badge_position", "BOTTOM_RIGHT");
             update_option($this::$app_prefix . "gtin_field",'');
             update_option($this::$app_prefix . "product_reviews_status", "off");
         }
     }


     function settings_link($links){
         $settings_link = '<a href="admin.php?page=adnabu-google-customer-reviews">Home</a>';
         array_push($links, $settings_link);
         return $links;
     }


     function admin_index(){
         require_once  $this->app_dir . '/templates/app_home.php' ;
     }


     function add_app_page(){
         add_submenu_page('adnabu_plugin',
             'Google Customer Reviews',
             'Google Customer Reviews',
             'manage_options',
             'adnabu-google-customer-reviews',
             array($this, 'admin_index'));
     }


     function enqueue_admin_assets($hook){
         if($hook == 'adnabu_page_adnabu-google-customer-reviews'){
             $this->enqueue_base_assets();
             $this->enqueue_app_assets($this::$app_prefix);
         }
     }


    function get_product_meta_gtin_related_fields(){
        global $wpdb;
        $query = "
            SELECT DISTINCT($wpdb->postmeta.meta_key)
            FROM $wpdb->posts 
            LEFT JOIN $wpdb->postmeta 
            ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
            WHERE ($wpdb->posts.post_type = 'product' 
            OR $wpdb->posts.post_type = 'product_variation') 
            AND $wpdb->postmeta.meta_key != ''
            AND ($wpdb->postmeta.meta_key LIKE '%gtin%'
            OR $wpdb->postmeta.meta_key LIKE '%ean%'
            OR $wpdb->postmeta.meta_key LIKE '%barcode%'
            OR $wpdb->postmeta.meta_key LIKE '%isbn%'
            OR $wpdb->postmeta.meta_key LIKE '%upc%')
            ORDER BY $wpdb->postmeta.meta_key
          ";
        $meta_keys = $wpdb->get_results($query);
        return $meta_keys;
    }


    function get_gtin_id($product_id){
        $gtin_field = get_option($this::$app_prefix . "gtin_field");
        $post_type = get_post_type( $product_id );
        $gtin_value = '';
        if($post_type == 'product') {
            $gtin_value = get_post_meta($product_id, $gtin_field, $single = true);
        }
        elseif($post_type == 'product_variation') {
            $gtin_value = get_post_meta($product_id, $gtin_field.'_variation', $single = true);
            if(empty($gtin_value)) {
                $gtin_value = get_post_meta($product_id, $gtin_field, $single = true);
            }
        }
        return $gtin_value;
    }


     function add_google_reviews_snippet(){
         if(is_order_received_page() and get_option($this::$app_prefix . "reviews_status") == 'on') {
             $order_id = wc_get_order_id_by_order_key($_GET['key']);
             $order = wc_get_order($order_id);
             $expected_date = date('Y-m-d',
                 strtotime($order->get_date_created()->date_i18n($format = 'Y-m-d') . '+ 7 days'));
             $merchant_id = get_option($this::$app_prefix . "merchant_id");
             $item_list = array();
             $items = $order->get_items();
             foreach ($items as $item) {
                 $product_id = $item['product_id'];
                 $item_list[] = array('gtin' => $this->get_gtin_id($product_id));
             }

             $item_list = json_encode($item_list);

             ?>
             <!-- BEGIN GCR Opt-in Module Code -->
             <script src="https://apis.google.com/js/platform.js?onload=renderOptIn"
                     async defer>
             </script>

             <script>
                 window.renderOptIn = function () {
                     window.gapi.load('surveyoptin', function () {
                         window.gapi.surveyoptin.render(
                             {
                                 // REQUIRED
                                 "merchant_id": '<?php echo $merchant_id ?>',
                                 "order_id": '<?php echo $order_id ?>',
                                 "email": '<?php echo $order->get_billing_email() ?>',
                                 "delivery_country": '<?php echo $order->get_shipping_country()?>',
                                 "estimated_delivery_date": '<?php echo $expected_date ?>',

                                 // OPTIONAL
                                 "opt_in_style": '<?php echo get_option($this::$app_prefix . "opt_in_position") ?>',
                                 <?php
                                 if (get_option($this::$app_prefix . "product_reviews_status") == 'on') {
                                     echo "\"products\" : $item_list";
                                 }
                                 ?>
                             });
                     });
                 }
             </script>
             <!-- END GCR Opt-in Module Code -->
             <?php if (get_option($this::$app_prefix . "rating_badge_status") === 'on') { ?>
                 <!-- BEGIN GCR Badge Code -->
                 <script src="https://apis.google.com/js/platform.js?onload=renderBadge"
                         async defer>
                 </script>

                 <script>
                     window.renderBadge = function () {
                         var ratingBadgeContainer = document.createElement("div");
                         document.body.appendChild(ratingBadgeContainer);
                         window.gapi.load('ratingbadge', function () {
                             window.gapi.ratingbadge.render(
                                 ratingBadgeContainer, {
                                     // REQUIRED
                                     "merchant_id": '<?php echo $merchant_id ?>',
                                     // OPTIONAL
                                     "position": '<?php echo get_option($this::$app_prefix . "rating_badge_position") ?>'
                                 });
                         });
                     }
                 </script>
                 <!-- END GCR Badge Code -->

                 <?php
             }
         }
     }

    /**
     * delete wp options with given prefix
     * @param $prefix
     * make sure the prefix is as specific as possible
     */
    public static function delete_options_with_prefix($prefix){
         global $wpdb;
         $get_options_query = "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '{$prefix}%'";
         $options = $wpdb->get_results($get_options_query);
         foreach ($options as $option){
             delete_option($option->option_name);
         }
     }


    public static function uninstall_app(){
         $prefix = self::$app_prefix;
         self::delete_options_with_prefix($prefix);
     }
}