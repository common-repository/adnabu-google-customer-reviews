<br>
<div class="container">
    <div class="row">
        <div class="col-8 offset-2">
            <a href="https://ads.google.com/">
                <img src="<?php $instance = new AdNabuGoogleCustomerReviews();
                echo $instance->app_dir_url . '/assets/images/google-transparent-review.png';
                ?>"
                     alt="Google_Ads_logo"
                     style="width:20%">
            </a>
            <strong style ="font-size: 300%; text-align: center">Customer Reviews</strong>
        </div>
    </div>
    <br>
        <?php
        $instance::uninstall_app(); // use this to clear settings
        $merchant_center_choices_json = '';
        $query = http_build_query(array(
            'app_id' => $instance::$app_id,
            'store' => get_option('adnabu_store_id'),
        ));
        $get_chouces_url = "https://www.adnabu.com/woocommerce/merchant/center/choices?$query";
        $transient = $instance::$app_prefix . 'get_merchant_center_choices';
        if(get_transient($transient) == 1){
            $response = $instance->custom_wp_remote($get_chouces_url);
            if(!is_wp_error( $response ) ) {
                $merchant_center_choices = wp_remote_retrieve_body($response);
                $merchant_center_choices_json = json_decode($merchant_center_choices, true);
                delete_transient( $transient );
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST'  and isset($_POST["get_merchant_center_choices"])){
            set_transient($transient,1);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' and
            wp_verify_nonce($_POST["_wpnonce"],$this::$app_prefix . "nonce") == 1) {
            if (isset($_POST["merchant_id"])) {
                update_option($this::$app_prefix . "merchant_id",
                    sanitize_text_field($_POST["merchant_id"]));
            }
            if (isset($_POST["google_reviews_status"])) {
                update_option($this::$app_prefix . "reviews_status",
                    sanitize_text_field($_POST["google_reviews_status"]));
            }
            else{
                update_option($this::$app_prefix . "reviews_status", "off");
            }

            if (isset($_POST["opt_in_position"])) {
                update_option($this::$app_prefix . "opt_in_position",
                    sanitize_text_field($_POST["opt_in_position"]));
            }
            if (isset($_POST['expected_delivery_in_days'])) {
                update_option($this::$app_prefix . "expected_delivery_in_days",
                    absint($_POST['expected_delivery_in_days']));
            }
            if (isset($_POST["rating_badge_status"])) {
                update_option($this::$app_prefix . "rating_badge_status",
                    sanitize_text_field($_POST["rating_badge_status"]));
                if (isset($_POST["rating_badge_position"])) {
                    update_option($this::$app_prefix . "rating_badge_position",
                        sanitize_text_field($_POST["rating_badge_position"]));
                }
            }
            else {
                update_option($this::$app_prefix . "rating_badge_status", "off");
            }

            if(isset($_POST["product_reviews_status"])){
                update_option($this::$app_prefix . "product_reviews_status",
                    sanitize_text_field($_POST["product_reviews_status"]));
                if(isset($_POST["gtin_field"])){
                    update_option($this::$app_prefix . "gtin_field",
                        sanitize_text_field($_POST["gtin_field"]));
                }
            }
            else {
                update_option($this::$app_prefix . "product_reviews_status", "off");
            }
        }

        $show_setting_form = '';


        $instance = new AdNabuGoogleCustomerReviews();
        $connect_merchant_center_url = "\"https://www.adnabu.com/social/login/content-google-oauth2?$query\"";
        $form_status = 'disabled';
        $save_button_display = 'none';
        $edit_button_display = '';
        if (!get_option($this::$app_prefix . "merchant_id") && empty($merchant_center_choices_json)){
            $show_setting_form = 'none';
            ?>
            <div class="row" id="connect_merchant_center_div">
                <div class="col-4 offset-4 align-content-center">
                    <button type='button' onclick='connect_merchant_center(<?php echo $connect_merchant_center_url?>)'
                            class='btn btn-primary'>Create Google Customer Reviews Pixel
                    </button>
                </div>
            </div>
            <?php
        }
        else{
            $merchant_id_option = '';
            if(empty($merchant_center_choices_json)){
                $merchant_id = get_option($this::$app_prefix . "merchant_id");
                if($merchant_id){
                    $merchant_id_option = "<option>$merchant_id</option>";
                }
            }
            else{
                foreach ($merchant_center_choices_json as $choice){
                    $id = $choice['id'];
                    $merchant_id_option = $merchant_id_option . "<option>$id</option>";
                }
                $form_status = '';
                $save_button_display = '';
                $edit_button_display = 'none';
            }
            ?>
    <br><br>
    <form action="#" method="post">
        <?php wp_nonce_field( $this::$app_prefix . "nonce");?>
        <div class="form-group row">
            <label for="merchant" class="col-sm-3 offset-3  col-form-label">Merchant Center</label>
            <div class="col-sm-2 mt-2" id="merchant_center_div">
                <select id='merchant_center_div_select' name = "merchant_id" <?php echo $form_status ?> >
                    <?php echo $merchant_id_option; ?>
                </select>
            </div>
            <br>
            <button type='button' onclick='connect_merchant_center(<?php echo $connect_merchant_center_url?>)'
                    class='btn btn-primary btn-sm'>Change Merchant Center
            </button>
        </div>
        <div class="form-group row">
            <label for="google_reviews_status" class="col-sm-3 offset-3 col-form-label switch">
                Google Customer Reviews
            </label>
            <div class="col-sm-2 mt-2">
                <label class='switch '>
                    <input <?php echo $form_status ?>
                        <?php
                        $current_gcr_status = get_option($this::$app_prefix . "reviews_status");
                        $display_review_setting_div = "none";
                        if($current_gcr_status == 'on'){
                            echo "checked";
                            $display_review_setting_div = "";
                        }
                        ?>
                            class="form-control" onclick="showDivOnCheck(this.id,'review_setting_div')"
                            type="checkbox" id="google_reviews_status" name="google_reviews_status">
                    <span class='slider round'></span>
                </label>
            </div>
        </div>
        <div id="review_setting_div" style="display:<?php echo $display_review_setting_div ?>">
            <div class="form-group row">
                <label for="opt-in" class="col-sm-3 offset-3 col-form-label">
                    Select Position Of Opt-In Servey Module
                </label>
                <div class="col-sm-2 mt-2">
                    <select class="form-control"  name="opt_in_position" <?php echo $form_status ?>>
                        <?php
                        $current_opt_in_position = get_option($this::$app_prefix . "opt_in_position");
                        $opt_in_choices = array(
                            "CENTER_DIALOG",
                            "BOTTOM_RIGHT_DIALOG",
                            "BOTTOM_LEFT_DIALOG",
                            "TOP_RIGHT_DIALOG",
                            "TOP_LEFT_DIALOG",
                            "BOTTOM_TRAY"
                        );

                        foreach ($opt_in_choices as $opt_in_choice){?>
                            <option
                            <?php if($opt_in_choice == $current_opt_in_position) echo "selected"?>
                            >
                            <?php echo $opt_in_choice ?>
                            </option>
                            <?php
                        }?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="expected_delivery_in_days" class="col-sm-3 offset-3 col-form-label">
                    Average Delivery Days
                </label>
                <div class="col-sm-2 mt-2">
                    <input <?php echo $form_status ?> class="form-control" type="number" min="0" name="expected_delivery_in_days"
                           value="<?php echo get_option($this::$app_prefix . "expected_delivery_in_days")?>"
                           id="expected_delivery_in_days">
                </div>
            </div>

            <div class="form-group row">
                <label for="rating_badge_enable" class="col-sm-3 offset-3 col-form-label">
                    Google Rating Badge
                </label>
                <div class="col-sm-2 mt-2">
                    <label class="switch ">
                        <input <?php echo $form_status ?>
                            <?php
                            $current_badge_status = get_option($this::$app_prefix . "rating_badge_status");
                            $display_badge_position_div = "none";
                            if($current_badge_status == 'on')
                            {
                                echo "checked";
                                $display_badge_position_div = "";
                            }
                            ?>
                                class="form-control" onclick="showDivOnCheck(this.id,'rating_position_div')"
                               type="checkbox" id="rating_badge_status" name="rating_badge_status">
                        <span class='slider round'></span>
                    </label>
                </div>
            </div>
            <div class="form-group row" id="rating_position_div" style="display: <?php echo $display_badge_position_div ?>">
                <label for="opt-in" class="col-sm-3 offset-3 col-form-label">
                    Select Position Of Rating Badge
                </label>
                <div class="col-sm-2 mt-2">
                    <select <?php echo $form_status ?> class="form-control"  name="rating_badge_position">
                        <?php
                        $current_badge_position = get_option($this::$app_prefix . "rating_badge_position");
                        $badge_position_choices = array("BOTTOM_LEFT", "BOTTOM_RIGHT", "INLINE");
                        foreach ($badge_position_choices as $badge_position_choice){
                            ?><option
                            <?php if($badge_position_choice == $current_badge_position) echo "selected"?>
                            >
                            <?php echo $badge_position_choice ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label for="product_reviews_status" class="col-sm-3 offset-3 col-form-label">
                    Google Product Reviews
                </label>
                <div class="col-sm-2 mt-2">
                    <label class="switch ">
                        <input <?php echo $form_status ?>
                            <?php
                            $current_product_reviews_status = get_option($this::$app_prefix . "product_reviews_status");
                            $display_gtin_selection_div = 'none';
                            if($current_product_reviews_status == 'on')
                            {
                                echo "checked";
                                $display_gtin_selection_div = '';
                            }

                            ?>
                                onclick="showDivOnCheck(this.id,'gtin_selection_div')"
                                class="form-control"  type="checkbox" id="product_reviews_status"
                               name="product_reviews_status">
                        <span class='slider round'></span>
                    </label>
                </div>
            </div>


            <div class="form-group row" id="gtin_selection_div" style="display: <?php echo $display_gtin_selection_div ?>">
                <label for="opt-in" class="col-sm-3 offset-3 col-form-label">
                    Select GTIN coloumn
                </label>
                <div class="col-sm-2 mt-2">
                    <select class="form-control"  name="gtin_field" <?php echo $form_status ?>>
                        <?php
                        $fields = $instance->get_product_meta_gtin_related_fields();
                        $current_gtin_field = get_option($this::$app_prefix . "gtin_field");
                        foreach ($fields as $field) {
                            ?>
                            <option <?php if($current_gtin_field == $field->meta_key) echo 'selected'?> >
                                <?php echo $field->meta_key?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group row" id="gcr_edit_button" style="display: <?php echo $edit_button_display ?>">
            <div class="col-sm-2 offset-5">
                <button type="button" class="btn btn-primary" onclick="startGCREditMode()">Edit</button>
            </div>
        </div>
        
        <div class="form-group row" style="display: <?php echo $save_button_display ?> " id="gcr_save_button">
            <div class="col-sm-2 offset-5">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>
    </form>
</div>
<?php } ?>