<?php

if (!defined('WPINC')) {
    exit;
}

class Wt_Import_Export_For_Woo_Product_Import {

    public $post_type = 'product';
    public $parent_module = null;
    public $parsed_data = array();
    public $import_columns = array();
    public $merge;
    public $skip_new;
    public $merge_empty_cells;
    public $delete_products;
    public $use_sku_upsell_crosssell;
    public $prod_use_chidren_sku;
    public $pro_stop_thumbnail_regen;
    public $merge_with = 'id';
    public $found_action = 'skip';
    public $id_conflict = 'skip';


    var $processed_posts = array();
    var $post_orphans = array();
    var $attachments = array();
    var $upsell_skus = array();
    var $crosssell_skus = array();
    // Results
    var $import_results = array();
    
    public $item_data = array();
    public $is_product_exist = false;

    public function __construct($parent_object) {

        $this->parent_module = $parent_object;
        
    }
    
    /* WC object based import  */
    public function prepare_data_to_import($import_data,$form_data,$batch_offset,$is_last_batch){  

        $import_data = apply_filters( 'wt_ier_alter_import_data', $import_data);
        $this->merge_with = !empty($form_data['advanced_form_data']['wt_iew_merge_with']) ? $form_data['advanced_form_data']['wt_iew_merge_with'] : 'id';
        $this->found_action = !empty($form_data['advanced_form_data']['wt_iew_found_action']) ? $form_data['advanced_form_data']['wt_iew_found_action'] : 'skip'; 
        $this->id_conflict = !empty($form_data['advanced_form_data']['wt_iew_id_conflict']) ? $form_data['advanced_form_data']['wt_iew_id_conflict'] : 'skip'; 
        $this->merge_empty_cells = isset($form_data['advanced_form_data']['wt_iew_merge_empty_cells']) ? $form_data['advanced_form_data']['wt_iew_merge_empty_cells'] : 0;                
        $this->skip_new = isset($form_data['advanced_form_data']['wt_iew_skip_new']) ? $form_data['advanced_form_data']['wt_iew_skip_new'] : 0;
        
        $this->use_sku_upsell_crosssell = isset($form_data['advanced_form_data']['wt_iew_use_sku_upsell_crosssell']) ? $form_data['advanced_form_data']['wt_iew_use_sku_upsell_crosssell'] : 0;
        $this->prod_use_chidren_sku = isset($form_data['advanced_form_data']['wt_iew_prod_use_chidren_sku']) ? $form_data['advanced_form_data']['wt_iew_prod_use_chidren_sku'] : 0;
        $this->pro_stop_thumbnail_regen = isset($form_data['advanced_form_data']['wt_iew_pro_stop_thumbnail_regen']) ? $form_data['advanced_form_data']['wt_iew_pro_stop_thumbnail_regen'] : 0;
                    
        if(isset($form_data['advanced_form_data']['wt_iew_delete_existing'])){
			$this->delete_existing = ( ($form_data['advanced_form_data']['wt_iew_delete_existing']) === '1' || $form_data['advanced_form_data']['wt_iew_delete_existing'] === 1 ) ? 1 : 0;
		}else{
			$this->delete_existing = 0;
		}
		
        Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', "Preparing for import.");
        $success = 0;
        $failed = 0;
        
        if($batch_offset != 0){
            $start_row_count = get_option('wt_ier_import_row_count',null);
            $complete_row_count = get_option('wt_ier_import_row_complete_count',null);
            if($start_row_count == $complete_row_count){
                $skip_on_resume = $start_row_count+1;
            }else{
                $skip_on_resume = $start_row_count;
            }
        }

        foreach ($import_data as $key => $data) { 
            $row = $batch_offset+$key+1;
            if(isset($skip_on_resume) && $skip_on_resume > $key){
                continue;
            }
            update_option('wt_ier_import_row_count', $key);
            $this->is_product_exist = false;

            Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - Parsing item.");            
			$parsed_data = $this->parse_data($data);
            if (!is_wp_error($parsed_data)){
               
                Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - Processing item.");                
                $result =$this->process_item($parsed_data);
                if(!is_wp_error($result)){                    
                    if($this->is_product_exist){
                        $msg = 'Product updated successfully.';
                    }else{
                        $msg = 'Product imported successfully.';
                    }
                    $this->import_results[$row] = array('row'=>$row, 'message'=>$msg, 'status'=>true, 'status_msg' => __('Success', 'wt-import-export-for-woo'), 'post_id'=>$result['id'], 'post_link' => Wt_Import_Export_For_Woo_Product::get_item_link_by_id($result['id'])); 
                    Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - ".$msg);                    
                    $success++;
                }else{
                   $this->import_results[$row] = array('row'=>$row, 'message'=>$result->get_error_message(), 'status'=>false, 'status_msg' => __('Failed/Skipped', 'wt-import-export-for-woo'), 'post_id'=>'', 'post_link' => array( 'title' => __( 'Untitled', 'wt-import-export-for-woo' ), 'edit_url' => false ) );
                    Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - Processing failed. Reason: ".$result->get_error_message());
                   $failed++;
                }                
            }else{
               $this->import_results[$row] = array('row'=>$row, 'message'=>$parsed_data->get_error_message(), 'status'=>false, 'status_msg' => __('Failed/Skipped', 'wt-import-export-for-woo'), 'post_id'=>'', 'post_link' => array( 'title' => __( 'Untitled', 'wt-import-export-for-woo' ), 'edit_url' => false ) );
               Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', "Row :$row - Parsing failed. Reason: ".$parsed_data->get_error_message());
               $failed++;               
            }  
            update_option('wt_ier_import_row_complete_count', $key);           
        }
        
        if($is_last_batch && $this->delete_existing){
            $this->delete_existing();                        
        }
        
        $this->clean_after_import();
        
                        
        $import_response=array(
                'total_success'=>$success,
                'total_failed'=>$failed,
                'log_data'=>$this->import_results,
            );
		

		if ($is_last_batch && apply_filters( 'wt_iew_regenerate_product_thumbnails', true )) {
			if (isset($import_data[0]['mapping_fields']['images'])) {
				$tools_controller = new WC_REST_System_Status_Tools_Controller();

				// Regenerate thumnails for today imported images only and if images column is mapped
				add_filter('query', 'wt_alter_thumbnail_regen_query');
				function wt_alter_thumbnail_regen_query($sql) {
					global $wpdb;
					remove_filter('query', 'wt_alter_thumbnail_regen_query');

					if ("SELECT ID
			FROM $wpdb->posts
			WHERE post_type = 'attachment'
			AND post_mime_type LIKE 'image/%'
			ORDER BY ID DESC" === $sql) {

							$sql = "SELECT ID
							FROM {$wpdb->posts}
							WHERE post_type = 'attachment'
							AND post_date > '" . date('Y-m-d 00:00:00') . "'			
							AND post_mime_type LIKE 'image/%'
							ORDER BY ID DESC";
					}


					add_filter('query', 'wt_alter_thumbnail_regen_query');
					return $sql;
				}

				if (array_key_exists('regenerate_thumbnails',$tools_controller->get_tools())){
				$response = $tools_controller->execute_tool('regenerate_thumbnails');
				WC_Admin_Notices::add_notice('regenerating_thumbnails');
			}
		}
		}


		return $import_response;

    }
    
    public function clean_after_import() {
        global $wpdb;
        $posts = $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_status = '%s' AND post_type  IN ( 'product', 'product_variation')", 'importing')); 
        if($posts){
            array_map('wp_delete_post',$posts);
        }
    }
    
    
    public function delete_existing() {
    
        $query_args = [
            'post_type' => array( 'product', 'product_variation'),
            'fields' => 'ids',
            'posts_per_page' => -1,
            'post_status' => array( 'publish', 'private', 'draft', 'pending', 'future'),
            'meta_query' => [
                [
                    'key' => '_wt_delete_existing',
                    'compare' => 'NOT EXISTS',
                ]
            ], 
            'suppress_filters' => true, // Add this line to suppress filters           
        ];
        
        $query_args = apply_filters('wt_ier_product_importer_pre_delete_existing_query_args', $query_args);
        
        $posts = new WP_Query($query_args);
        
        if(apply_filters( 'wt_ier_product_importer_delete_existing_products', true)){                     
            foreach ($posts->posts as $post) {
                $this->import_results['detele_results'][$post] = wp_trash_post($post);
            }
            
        }
        
        $posts = new WP_Query([
            'post_type' => array( 'product', 'product_variation'),
            'fields' => 'ids',
            'posts_per_page' => -1,
            'post_status' => array( 'publish', 'private', 'draft', 'pending', 'future'),
            'meta_query' => [
                [
                    'key' => '_wt_delete_existing',
                    'compare' => 'EXISTS',
                ]
            ]
        ]);        
        foreach ($posts->posts as $post) {
            delete_post_meta($post,'_wt_delete_existing');
        }
                               
    }
    
    /**
    * Parse the data.
    *
    *
    * @param array $data value.
    *
    * @return array
    */
   public function parse_data($data) {

       try {            
            $mapped_data = $data['mapping_fields'];
            foreach ($data['meta_mapping_fields'] as $value) {
                $mapped_data = array_merge($mapped_data, $value);
            }  
                        
            $mapped_data = apply_filters('wt_woocommerce_product_importer_pre_parse_data', $mapped_data);

            $this->item_data = array(); // resetting WC default data before parsing new item to avoid merging last parsed item wp_parse_args
            $this->product_id = '';
            
            if((isset($mapped_data['ID']) && !empty($mapped_data['ID']))||(isset($mapped_data['_sku']) && !empty($mapped_data['_sku'])) || (isset($mapped_data['sku']) && !empty($mapped_data['sku']))){
                $this->item_data['id'] = $this->wt_product_existance_check($mapped_data);  // to determine wether merge or import
            }
            
            if(!$this->merge){
                $default_data = $this->get_default_data();                
                $this->item_data  = wp_parse_args( $this->item_data, $default_data );
            }
                            
            if($this->merge && !$this->merge_empty_cells){
                $this->item_data = array();
                $this->item_data['id'] = $this->product_id;  // re assinging id after reset default product datas for merge,  $this->product_id set from wt_product_existance_check
            }
            
             // Relative stock updates
            if ($this->merge && $this->product_id) {
                if (isset($mapped_data['stock'])) {
                    $mapped_data['stock'] = trim($mapped_data['stock']);
                    $mode = substr($mapped_data['stock'], 0, 3);

                    if ($mode == '(+)') {                        
                        $amount = intval(substr($mapped_data['stock'], 3));
                        $mapped_data['stock'] = $this->wt_relative_product_field_update('_stock', $amount, 'increase', $this->product_id);
                    }
                    if ($mode == '(-)') {
                        $amount = intval(substr($mapped_data['stock'], 3));
                        $mapped_data['stock'] = $this->wt_relative_product_field_update('_stock', $amount, 'decrease', $this->product_id);
                    }
                }
            }            
                        

            if (!$this->merge && !isset($data['meta_mapping_fields']['taxonomies']['tax:product_type'])) {
				if (!empty($mapped_data['post_parent']) || !empty($mapped_data['parent_sku'])) {
					$this->item_data['type'] = 'variation';
				} else {
					$this->item_data['type'] = 'simple';
				}
			} elseif ($this->merge && !isset($data['meta_mapping_fields']['taxonomies']['tax:product_type'])) {
				if ($this->item_data['id']) {
					$this->item_data['type'] = self::wt_get_product_type($this->item_data['id']); // get_product_type cannot be called statically
				} else {
					if (!empty($mapped_data['post_parent']) || !empty($mapped_data['parent_sku'])) {
						// check if the post_parent or parent_sku is of a parent product, set the below type according to that.
						$this->item_data['type'] = 'variation';
					} else {
						$this->item_data['type'] = 'simple';
					}
				}
			}

  

            foreach ($mapped_data as $column => $value) {
                
                if ($this->merge && !$this->merge_empty_cells && $value === '' && !strstr($column, 'attribute')) {
                    continue;
                }
                if('post_author' == $column){
                    $this->item_data['post_author'] = $this->wt_parse_int_field($value);                     
                    continue; 

                }
                if ('parent_id' == $column || 'post_parent' == $column || 'parent_sku' == $column) {
                    $this->item_data['parent_id'] = $this->wt_parse_parent_field($value,$column);
                    continue;
                }
                
                if ('type' == $column || 'tax:product_type' == $column) {
                    $this->item_data['type'] = $this->wt_parse_type_field($value);
                    continue;
                }
                if ('sku' == $column || '_sku' == $column) {
                    $this->item_data['sku'] = $value;
                    continue;
                }

                if ('name' == $column || 'post_title' == $column) {
                    $this->item_data['name'] = $value;
                    continue;
                }
                if ('slug' == $column || 'post_name' == $column) {
                    $this->item_data['slug'] = $value;
                    continue;
                }
                if ('date_created' == $column || 'post_date' == $column) {
                    $this->item_data['date_created'] = $value;
                    $this->item_data['date_modified'] = $value;
                    continue;
                }
                if ('status' == $column || 'post_status' == $column) {
                    $this->item_data['status'] = $this->wt_parse_published_field($value);                    
                    continue;
                }
                if ('featured' == $column || '_featured' == $column) {
                    $this->item_data['featured'] = $this->wt_parse_featured_field($value);
                    continue;
                }
                if ('catalog_visibility' == $column || '_visibility' == $column || 'tax:product_visibility' == $column) {
                    $this->item_data['catalog_visibility'] = $this->wt_parse_catalog_visibility_field($value);
                    continue;
                }
                if ('description' == $column || 'post_content' == $column) {
                    
                    $enable_chatgpt = Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings('enable_chatgpt');
                    if( 1 == $enable_chatgpt ){
                        $gpt_key = Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings('chatgpt_api_key');
                        if('' !== $gpt_key && empty( $value ) && !$this->merge ){ // ChatGPT key is entered, product description column is empty and it's not an update.
                            $start_description = isset( $mapped_data['post_title'] ) ? $mapped_data['post_title'] : $value;
                            if( !empty($start_description)){
                                $start_description = 'Product name is ' . $start_description;
                            }
                            if(isset($mapped_data['short_description']) && !empty($mapped_data['short_description']) ){
                                $start_description .= 'Product short description is ' . $mapped_data['short_description'];
                            }else  if(isset($mapped_data['post_excerpt']) && !empty($mapped_data['post_excerpt']) ){
                                $start_description .= 'Product short description is ' .$mapped_data['post_excerpt'];
                            }
                            
                            $this->item_data['description'] = $this->generate_product_description_using_openai($start_description, $gpt_key);
                            continue;
                        }
                    }
                    
                    $this->item_data['description'] = $this->wt_parse_description_field($value);
                    continue;
                }
                if ('short_description' == $column || 'post_excerpt' == $column) {
                    $this->item_data['short_description'] = $this->wt_parse_description_field($value);
                    continue;
                }
                if ('price' == $column || '_price' == $column) {
                    $this->item_data['price'] = wt_format_decimal($value);
                    continue;
                }
                if ('regular_price' == $column || '_regular_price' == $column) {
                    $this->item_data['regular_price'] = wt_format_decimal($value);
                    continue;
                }
                if ('sale_price' == $column || '_sale_price' == $column) {
                    $this->item_data['sale_price'] = wt_format_decimal($value);
                    continue;
                }
                if ('date_on_sale_from' == $column || '_sale_price_dates_from' == $column || 'sale_price_dates_from'  == $column) {
                    $this->item_data['date_on_sale_from'] = $value;
                    continue;
                }
                if ('date_on_sale_to' == $column || '_sale_price_dates_to' == $column || 'sale_price_dates_to' == $column) {
                    $this->item_data['date_on_sale_to'] = $value;
                    continue;
                }
                if ('total_sales' == $column) {
                    $this->item_data['total_sales'] = $this->wt_parse_int_field($value);
                    continue;
                }
                if ('tax_status' == $column || '_tax_status' == $column) {
                    $this->item_data['tax_status'] = $this->wt_parse_tax_status_field($value);
                    continue;
                }
                if ('tax_class' == $column || '_tax_class' == $column) {
                    $this->item_data['tax_class'] = ($value);
                    continue;
                }
                if ('stock_quantity' == $column || '_stock' == $column || 'stock' == $column) {
                    $this->item_data['stock_quantity'] = $this->wt_parse_stock_quantity_field($value);
                    continue;
                }
                if ('manage_stock' == $column || '_manage_stock' == $column) {
                    $this->item_data['manage_stock'] = $this->wt_parse_string_to_bool_field($value);
                    continue;
                }                
                if ('stock_status' == $column || '_stock_status' == $column) {
                    $this->item_data['stock_status'] = $this->wt_parse_stock_status_field($value);
                    continue;
                }
                if ('backorders' == $column || '_backorders' == $column) {
                    $this->item_data['backorders'] = $this->wt_parse_backorders_field($value);
                    continue;
                }
                if ('low_stock_amount' == $column || '_low_stock_amount' == $column) {
                    $this->item_data['low_stock_amount'] = $this->wt_parse_int_field($value);
                    continue;
                }
                if ('sold_individually' == $column || '_sold_individually' == $column) {
                    $this->item_data['sold_individually'] = $this->wt_parse_string_to_bool_field($value);
                    continue;
                }
                 if ('product_url' == $column || '_product_url' == $column) {
                    $this->item_data['product_url'] = $value;
                    continue;
                }
                if ('button_text' == $column || '_button_text' == $column) {
                    $this->item_data['button_text'] = $value;
                    continue;
                }
                if ('weight' == $column || '_weight' == $column) {
                    $this->item_data['weight'] = ($value);
                    continue;
                }
                if ('length' == $column || '_length' == $column) {
                    $this->item_data['length'] = ($value);
                    continue;
                }
                if ('width' == $column || '_width' == $column) {
                    $this->item_data['width'] = ($value);
                    continue;
                }
                if ('height' == $column || '_height' == $column) {
                    $this->item_data['height'] = ($value);
                    continue;
                }
                if ('upsell_ids' == $column || '_upsell_ids' == $column || '_upsell_skus' == $column) {
                    $this->item_data['upsell_ids'] = $this->wt_parse_product_ids_field($value);
                    continue;
                }
                if ('crosssell_ids' == $column || '_crosssell_ids' == $column || '_crosssell_skus' == $column) {
                    $this->item_data['cross_sell_ids'] = $this->wt_parse_product_ids_field($value);
                    continue;
                }
                if ('reviews_allowed' == $column || 'comment_status' == $column) {
                    $this->item_data['reviews_allowed'] = $this->wt_parse_string_to_bool_field($value);
                    continue;
                }
                if ('purchase_note' == $column || '_purchase_note' == $column) {
                    $this->item_data['purchase_note'] = ($value);
                    continue;
                }
                if ('menu_order' == $column || 'position' == $column) {
                    $this->item_data['menu_order'] = $this->wt_parse_int_field($value);
                    continue;
                }
                if ('post_password' == $column) {
                    $this->item_data['post_password'] = ($value);
                    continue;
                }
                if ('virtual' == $column || '_virtual' == $column) {
                    $this->item_data['virtual'] = $this->wt_parse_string_to_bool_field($value);
                    continue;
                }
                if ('downloadable' == $column || '_downloadable' == $column) {
                    $this->item_data['downloadable'] = $this->wt_parse_string_to_bool_field($value);
                    continue;
                }
                if ('category_ids' == $column || 'tax:product_cat' == $column) {
                    $this->item_data['category_ids'] = $this->wt_parse_categories_field($value);
                    continue;
                }
                if ('tag_ids' == $column || 'tax:product_tag' == $column) {
                    $this->item_data['tag_ids'] = $this->wt_parse_tags_field($value);
                    continue;
                }
                if ('shipping_class_id' == $column || 'tax:product_shipping_class' == $column) {
                    $this->item_data['shipping_class_id'] = $this->wt_parse_shipping_class_field($value);
                    continue;
                }
                if ('downloads' == $column || '_downloadable_files' == $column || 'downloadable_files' == $column) {
                    $this->item_data['downloads'] = $this->wt_parse_downloads_field($value);
                    continue;
                }
                if ('download_limit' == $column || '_download_limit' == $column) {
                    $value = trim($value,'\'');
                    $this->item_data['download_limit'] = $this->wt_parse_int_field($value);
                    continue;
                }
                if ('download_expiry' == $column || '_download_expiry' == $column) {
                    $value = trim($value,'\'');                    
                    $this->item_data['download_expiry'] = $this->wt_parse_int_field($value);
                    continue;
                }
                if ('rating_counts' == $column) {
                    $this->item_data['rating_counts'] = ($value);
                    continue;
                }
                if ('average_rating' == $column) {
                    $this->item_data['average_rating'] = $this->wt_parse_int_field($value);
                    continue;
                }
                if ('review_count' == $column) {
                    $this->item_data['review_count'] = $this->wt_parse_int_field($value);
                    continue;
                }
                if ('Grouped products' == $column || 'children' == $column || '_children' == $column) {
                    $this->item_data['children'] = $this->wt_parse_product_ids_field($value);
                    continue;
                }                                               
                if ('images' == $column) {
                    $images = $this->wt_parse_images_field($value);
                    $this->item_data['raw_image'] = array_shift($images);
                    if (!empty($images)) {
                        $this->item_data['raw_gallery_image'] = $images;
                    }                    
                    unset($images);
                    continue;
                }
                    
                if (strstr($column, 'attribute:')) {
                    $this->wt_parse_attribute_field($value, $column);
                    continue;
                }

                if (strstr($column, 'attribute_data:')) {
                    $this->wt_parse_attribute_data_field($value, $column);
                    continue;
                }
                if (strstr($column, 'attribute_default:')) {
                    $this->wt_parse_attribute_default_field($value, $column);
                    continue;
                }

                if (strstr($column, 'meta:')) {
                    $this->wt_parse_meta_field($value, $column);
                    continue;
                }
                if (strstr($column, 'tax:')) {
                    $this->wt_parse_other_taxomomy_field($value, $column);
                    continue;
                }
                /**
                 * WPML
                 */
                if ($column == 'wpml:original_product_id' || $column == 'wpml:original_product_sku' || $column == 'wpml:language_code') {
                    $column = trim(str_replace('wpml:', '', $column));
                    $this->item_data['wpml'][] = array('key' => esc_attr($column), 'value' => $value);
                }
                
            }   
            

            if(empty($this->item_data['id'])){                                 
                $this->item_data['id'] = $this->wt_parse_id_field($mapped_data);
            } 

            return $this->item_data;
        } catch (Exception $e) {            
            return new WP_Error('woocommerce_product_importer_error', $e->getMessage(), array('status' => $e->getCode()));
        }
    }

    /**
	 * Explode CSV cell values using commas by default, and handling escaped
	 * separators.
	 *
	 * @since  3.2.0
	 * @param  string $value     Value to explode.
	 * @param  string $separator Separator separating each value. Defaults to comma.
	 * @return array
	 */
	protected function wt_explode_values( $value, $separator = ',' ) {
		$value  = str_replace( '\\,', '::separator::', $value );
		$values = explode( $separator, $value );
		$values = array_map( array( $this, 'wt_explode_values_formatter' ), $values );

		return $values;
	}

	/**
	 * Remove formatting and trim each value.
	 *
	 * @since  3.2.0
	 * @param  string $value Value to format.
	 * @return string
	 */ 
	protected function wt_explode_values_formatter( $value ) {
		return trim( str_replace( '::separator::', ',', $value ) );
	}
        
        public function wt_parse_product_ids_field($value){
            
            if('' == $value){
                return array();
            }
            if($this->use_sku_upsell_crosssell){
                $value = array_map('wc_get_product_id_by_sku',$this->wt_parse_seperation_field($value,'|')); 
            }else{
                $value = $this->wt_parse_seperation_field($value,'|');
            }
                        
            return $value;
        }
        
        public function wt_parse_featured_field($value){
            
            $value = strtolower(trim($value));
                       
            if ( isset( $value ) ) {
                if ( 'featured' == $value ) { 
                    return 1;
                }                
                if ( '' === $value ) {
                    return  0;
                } else {
                    
                    return  wc_string_to_bool($value);
                }
            }
            return  0;
            
            
        }

        

        public function wt_parse_catalog_visibility_field( $value ) {
            if (strstr($value, '|')) {
                $visibilities = $this->wt_parse_seperation_field($value,'|'); 
                $visibility = '';           

                if(in_array('exclude-from-search', $visibilities)){      // Search result only           
                   $visibility = 'catalog';  
                }

                if(in_array('exclude-from-catalog', $visibilities)){     // Shop only            
                    $visibility ='search';
                }
                
                if(in_array('exclude-from-catalog', $visibilities) && in_array('exclude-from-search', $visibilities)){       // Hidden         
                    $visibility ='hidden';
                }
            }else{
                
                $visibility = $value = strtolower($value);                

                if('exclude-from-search' == $value){      // Search result only           
                   $visibility = 'catalog';  
                }

                if('exclude-from-catalog' == $value){     // Shop only            
                    $visibility ='search';
                }                                
            }
            
            $options = array_keys( wc_get_product_visibility_options() );
            if ( ! in_array( $visibility, $options ) ) {                
                $visibility = 'visible';
            }
            return $visibility;
	}
        
        /**
	 * Parse backorders from a CSV.
	 *
	 * @param string $value Field value.
	 *
	 * @return string
	 */
	public function wt_parse_backorders_field( $value ) {
                        
            $value = strtolower($value);
            if ( empty( $value ) ) {
			return 'no';
            }
            return $value;
	}
        
        
        
        
        public function wt_parse_stock_quantity_field($value) {

            if ('' === $value) {
                return $value;
            }

            return wc_stock_amount($value);

        }
        
        public function wt_parse_stock_status_field($value) {
            $stock_status = $value;
                                   
            //Handle stock status if it is given like (Instock, In stock , out of stock, Out of  Stock), make them as (instock, outofstock)            
            $value = strtolower($value);
            if (in_array($value, array(1, '1', TRUE, 'true', 'instock', 'in stock'), TRUE)) {
                $stock_status = 'instock';
            } elseif (in_array($value, array(0, '0', FALSE, 'false', 'outofstock', 'out of stock', 'out stock', 'outstock'), TRUE)) {
                $stock_status = 'outofstock';
            } elseif(strpos($value, 'backorder')!== false) {
                $stock_status = 'onbackorder';
            }else{
                $stock_status = strtolower(preg_replace('/\s+/', '', $value));
            }
            
            return $stock_status;
            
        }
        
        public function wt_parse_string_to_bool_field($value) {
            $value = strtolower(trim($value));
                       
            if ( isset( $value ) ) {
                if ( 'open' == $value ) { // comment_status
                    return true;
                }                
                if ( '' === $value ) {
                    return  false;
                } else {
                    
                    return  wc_string_to_bool($value);
                }
            }
            return  false;
              
            
        }
        
        public function wt_parse_parent_field($value,$column){            
            if(!empty($this->item_data['parent_id'])){
                return $this->item_data['parent_id'];
            }
            global $wpdb;

            if (  '' == $value  ) {
                    return '';
            }

            $parent_id = '';
            // ID
            if ( 'parent_sku' != $column ) {
                    $id = intval( $value );

                    // See if the given ID maps to a valid product allready.
                    $existing_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ( 'product' ) AND ID = %d;", $id ) ); // WPCS: db call ok, cache ok.
                    if ( $existing_id ) {
                            $parent_id = absint( $existing_id );
                    }

            }else{

                $value = trim($value);

                $db_query = $wpdb->prepare("SELECT $wpdb->posts.ID
                                            FROM $wpdb->posts
                                            LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
                                            WHERE $wpdb->posts.post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
                                            AND $wpdb->posts.post_type IN ( 'product' )
                                            AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = '%s'
                                            ", $value);
                $id = $wpdb->get_var($db_query);

                if ( $id ) {
                        $parent_id = $id;
                }

            }
            
            
            if($parent_id && empty($this->item_data['type'])){
                $this->item_data['type'] = 'variation';
            }
            
            return $parent_id;
 
        }

        public function wt_parse_type_field($value) {
			
            if(!empty($this->item_data['type'])){
                return $this->item_data['type'];
            }
                        
            $value = strtolower(trim($value));                        
            $type = $value;
            
            if ( !isset($value) || $value == '') {
                    return 'simple';
            }
            
            if('' === $value && !empty($this->item_data['parent_id'])){
                return 'variation';
            }
                    
            // Type is the most important part here because we need to be using the correct class and methods.
            elseif ( isset( $value ) ) {
                    $types   = array_keys( wc_get_product_types() );
                    $types[] = 'variation';
                    if ( ! in_array( $type, $types, true ) ) {                        
                        throw new Exception(sprintf('Invalid product type %s',$value ));
                    } 
            }   
            return $type;
        }
        
        public function wt_parse_meta_field($value,$column) {
            $meta_key = trim(str_replace('meta:', '', $column));
                       
            /**
             * Handle meta: columns for variation attributes
             */
            if (strstr($column, 'meta:attribute_')) {                
                                
                $attribute_key = (trim(str_replace('meta:attribute_', '', $column)));
                
                if (!$attribute_key )
                    return;

                if(isset($this->item_data['type']) && 'variation' ==  $this->item_data['type']){
                    $this->item_data['raw_attributes'][$attribute_key]['value'] = $this->wt_parse_seperation_field($value,'|');
                }
                return;
                
            }
            if($value == '' && !$this->merge_empty_cells){
                return;
            }
            if (is_array(json_decode($value, true))) { //PIEPFW-512, PIEPFW-1046
                $value = json_decode($value, TRUE);
            }

        $this->item_data['meta_data'][] = array('key'=>$meta_key,'value'=>$value);             
        }
        
        /**
	 * handle extra/other (custom taxonomy by other plugin) taxonomies.
	 */ 
        public function wt_parse_other_taxomomy_field($value, $column) {
            // Get taxonomy
            $taxonomy = trim(str_replace('tax:', '', $column));

            // Exists?
            if (!taxonomy_exists($taxonomy)) {
                return;
            }

            if (empty($value)) {
                return array();
            }

            $names = $this->wt_explode_values($value, '|');
            $taxs = array();

            foreach ($names as $name) {
                $parent = null;
                $other_terms = array_map( 'trim', explode( '>', $name ) );
                $total  = count( $other_terms );

                foreach ( $other_terms as $index => $_term ) {
                        $term = term_exists( $_term, $taxonomy, $parent );
                        if ( is_array( $term ) ) {
                                $term_id = $term['term_id'];                             
                        }else {
                                $term = wp_insert_term( $_term, $taxonomy, array( 'parent' => intval( $parent ) ) );
                                if ( is_wp_error( $term ) ) {
                                        break; 
                                }
                                $term_id = $term['term_id'];
                        }
                        if ( ( 1 + $index ) === $total ) {
                                $taxs[] = $term_id;
                        } else {
                                $parent = $term_id;
                        }
                }
            }
            $this->item_data['other_taxomomy_data'][] = array('taxonomy' => $taxonomy, 'terms' => $taxs);
        }

        public function wt_parse_taxonomy_field($value) {
            return $value;
        }
        
        /**
        * Handle Attributes
        */
        public function wt_parse_attribute_field($value,$column) {

            $attribute_key = trim(str_replace('attribute:', '', $column));
            if (substr($attribute_key, 0, 3) == 'pa_') {  
                $attribute_key = sanitize_title( $attribute_key );
            } 

            $attribute_args = array('name'=>'',
                'value'=>array(),
                'visible'=>1,
                'taxonomy'=>0,
                'default'=>'',
                'position' =>0,
                );

            if (!$attribute_key)
                return;

            // Taxonomy
            if (substr($attribute_key, 0, 3) == 'pa_') {                    
                $this->item_data['raw_attributes'][$attribute_key]['name'] = trim(str_replace('attribute:pa_', '', $column));                     
                $this->item_data['raw_attributes'][$attribute_key]['taxonomy'] = 1;
            }else{                    
                $this->item_data['raw_attributes'][$attribute_key]['name'] = trim(str_replace('attribute:', '', $column));
                $this->item_data['raw_attributes'][$attribute_key]['taxonomy'] = 0; 
            } 
            if(isset($this->item_data['type']) && ('variation' !=  $this->item_data['type'])){
                $this->item_data['raw_attributes'][$attribute_key]['value'] = $this->wt_parse_seperation_field($value,'|'); 
            }            
            
            return;

        }
        
        /**
        * Handle Attributes Data - position|is_visible|is_variation
        */
        public function wt_parse_attribute_data_field($value,$column) {
             
            $attribute_key = trim(str_replace('attribute_data:', '', $column));
            if (substr($attribute_key, 0, 3) == 'pa_') {  
                $attribute_key = sanitize_title( $attribute_key );
            }
            if (!$attribute_key) {
                return;
            }

            $values = explode('|', $value);
            $position = isset($values[0]) ? (int) $values[0] : 0;
            $visible = isset($values[1]) ? (int) $values[1] : 1;
            $variation = isset($values[2]) ? (int) $values[2] : 0;

            $this->item_data['raw_attributes'][$attribute_key]['visible'] = $visible;
            $this->item_data['raw_attributes'][$attribute_key]['position'] = $position;                
            $this->item_data['raw_attributes'][$attribute_key]['variation'] = $variation;
            return;                
            
        }
        
        /**
         * Handle Attributes Default Values
         */
        public function wt_parse_attribute_default_field($value,$column) {
            
            $attribute_key = trim(str_replace('attribute_default:', '', $column));
            if (substr($attribute_key, 0, 3) == 'pa_') {  
                $attribute_key = sanitize_title( $attribute_key );
            }
            
            if (!$attribute_key || '' == $value )
                return;
            
            
            $this->item_data['raw_attributes'][$attribute_key]['default'] = $value;
            return;

        }
        
        
    
    public function wt_product_existance_check($data){ 
        global $wpdb;   
        $product_id = 0;
        $this->product_id = '';
        $this->is_product_exist = false;  
        
        $id = isset($data['ID']) && !empty($data['ID']) ? absint($data['ID']) : 0;         
        $id_found_with_id = '';
        $product_status = apply_filters('wt_ier_product_importer_product_status', array( 'publish', 'private', 'draft', 'pending', 'future'));
        if($id && 'id' == $this->merge_with){ 
            $id_found_with_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_status IN ( '". implode("','", $product_status)."' ) AND ID = %d;", $id)); // WPCS: db call ok, cache ok.
            if($id_found_with_id){
               if(in_array(get_post_type($id_found_with_id), array('product', 'product_variation'))){
                   $this->is_product_exist = true;
                   $product_id = $id_found_with_id;
               }
            }else{
                $product_id = $data['ID'];
            }            
        } 
                
        $sku = isset($data['_sku']) && '' != $data['_sku'] ? trim($data['_sku']) : '';
        if($sku == ''){
            $sku = isset($data['sku']) && '' != $data['sku'] ? trim($data['sku']) : '';
        }
        $id_found_with_sku = '';
        if(!empty($sku) && 'sku' == $this->merge_with){            
            $db_query = $wpdb->prepare("SELECT $wpdb->posts.ID,$wpdb->posts.post_type
                                        FROM $wpdb->posts
                                        LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
                                        WHERE $wpdb->posts.post_status IN ( '". implode("','", $product_status)."' )
                                        AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = '%s'
                                        ", $sku);
            $id_found_with_sku = $wpdb->get_row($db_query);
            if ($id_found_with_sku && (in_array($id_found_with_sku->post_type, array('product', 'product_variation')))) {
                $id_found_with_sku = $id_found_with_sku->ID;
                $this->is_product_exist = true; 
                $product_id = $id_found_with_sku;                
            }     
        }
        
		$allow_same_sku = apply_filters('wt_product_import_allow_same_sku',false);
            /*
             * WPML
             */

            if (apply_filters('wpml_setting', false, 'setup_complete') && (!empty($sku)) && ((!empty($data['wpml:original_product_id']) || !empty($data['wpml:original_product_sku'])) && !empty($data['wpml:language_code']))) {

                $db_query = $wpdb->prepare("
						SELECT $wpdb->posts.ID
						FROM $wpdb->posts
						LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
						WHERE $wpdb->posts.post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
						AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = '%s'
						", $sku);
                $found_product_ids = $wpdb->get_results($db_query);

                /*
                 * Finding product ID by sku (each translation may have the same sku if the translation created by duplicating original product)
                 */
                $found_product_id = '';
                $product_id = '';
                foreach ($found_product_ids as $value) {
                    $original_post_language_info = self::wt_get_wpml_original_post_language_info($value->ID);
                    if ($original_post_language_info->language_code == $data['wpml:language_code']) {
                        $found_product_id = $value->ID;
                    }
                }
                if ($found_product_id != '') {
                    $product_id = $found_product_id;
                }
				$allow_same_sku = true; // IER-604 - Allow same sku when importing WPML products with same SKU
				add_filter( 'wc_product_has_unique_sku', '__return_false' ); 
            }        
                  
            
        if($this->is_product_exist){
            if('skip' == $this->found_action){
                if($id && $id_found_with_id ){
					if($this->delete_existing){
						update_post_meta($id, '_wt_delete_existing', 1);
					}
                    throw new Exception(sprintf('Product with same ID already exists. ID: %d',$id ));
                }elseif($sku && $id_found_with_sku ){
                    if($this->delete_existing){
                            $prod_id = wc_get_product_id_by_sku($sku);
                            update_post_meta($prod_id, '_wt_delete_existing', 1);
                    }
                    
                     if(array_key_exists('wpml:original_product_sku', $data)){ // PIEPFW-1247:-WPML products imported again when choosing Skip option for existing products                         
                        $current_post_lang_code = '';
                        $language_information = apply_filters( 'wpml_post_language_details', NULL, $product_id ) ;
                         global $wpdb;
                        $post_id_by_sku = $wpdb->get_col($wpdb->prepare("
                                                                        SELECT $wpdb->posts.ID
                                                                        FROM $wpdb->posts
                                                                        LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
                                                                        WHERE $wpdb->posts.post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
                                                                        AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = '%s'
                                                                        ", $sku));
                        $current_post_lang_code_by_sku = array();
                        if($post_id_by_sku){
                            foreach ($post_id_by_sku as $key => $wpml_ids) {
                                    $lang_information = apply_filters( 'wpml_post_language_details', NULL, $wpml_ids ) ;
                                    if(is_array($lang_information)){
                                        $current_post_lang_code_by_sku[] = isset($lang_information['language_code']) ? $lang_information['language_code']:'';
                                    }
                            }
                        }
                       
                        if(is_array($language_information)){
                            $current_post_lang_code = isset($language_information['language_code']) ? $language_information['language_code']:'';
                        }
                        if(in_array($current_post_lang_code, array_unique(array_filter($current_post_lang_code_by_sku)))){
                            $allow_same_sku = false;
                        }                  
                    }
                    
                    if($allow_same_sku){ //allow  same sku case
                        return '';
                    }
                    throw new Exception(sprintf('Product with same SKU already exists. SKU: %s',$sku ));
                }else{
                    throw new Exception('Product already exists.');
                }                 
            }elseif('update' == $this->found_action){                                
                $this->merge = true; 
                $this->product_id = $product_id;
                return $product_id;
            }                            
        }
                
        if($this->skip_new){
            throw new Exception('Skipping new item' );
        }        
        
        if($id && is_string(get_post_status($id)) && !$this->is_product_exist && 'skip' == $this->id_conflict){
            throw new Exception(sprintf('Importing Product(ID) conflicts with an existing post. ID: %d',$id ));
        }
    }

        

        /**
     * Parse relative field and return ID.
     * 
     * Handles `id` and Product SKU.
     *
     * If we're not doing an update, create a prost and return ID
     * for rows following this one.
     *
     * @param array $data  mapped data.
     *
     * @return int|Exception
     */
    public function wt_parse_id_field($data ) {                             
        if(!empty($this->item_data['id'])){
            return $this->item_data['id'];
        }        
  
        $id = isset($data['ID']) && !empty($data['ID']) ? absint($data['ID']) : 0; 
        $found_id = $this->wt_product_existance_check($id);  
        if($found_id){
            return $found_id;
        }
            
	$this->item_data['type'] = isset($this->item_data['type']) ? $this->item_data['type'] : 'simple';
        $postdata = array( // if not specifiying id (id is empty) or if not found by given id or Product 
            'post_title'      =>  ($this->item_data['type'] == 'variation' ? 'product variation' : $this->item_data['name'] ),
            'post_status'    => 'importing',
            'post_type'      => $this->post_type,
            'post_content' => isset($this->item_data['description']) ? $this->item_data['description'] : '',
            'post_excerpt' => isset($this->item_data['short_description']) ? $this->item_data['short_description'] : ''
        );                 
        if(isset($id) && !empty($id)){
            $postdata['import_id'] = $id;
        }    
        
        $post_id = wp_insert_post( $postdata, true );                
        if($post_id && !is_wp_error($post_id)){
            Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', sprintf('Importing as new '. ($this->parent_module->module_base).' ID:%d',$post_id ));
            return $post_id;
        }else{
            throw new Exception($post_id->get_error_message());
        }

    }
    
    public function wt_parse_id_field_old($data ) {         
        if(!empty($this->item_data['id'])){
            return $this->item_data['id'];
        }
        
        global $wpdb;   
        
        $id = isset($data['ID']) && !empty($data['ID']) ? absint($data['ID']) : 0;         
        $id_found_with_id = '';
        if($id){                                      
            $id_found_with_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' ) AND ID = %d;", $id)); // WPCS: db call ok, cache ok.
            if($id_found_with_id){
               if(in_array(get_post_type($id_found_with_id), array('product', 'product_variation'))){
                   $this->is_product_exist = true;                   
               }
            }            
        }                

        $sku = isset($data['_sku']) && '' != $data['_sku'] ? $data['_sku'] : '';
        $id_found_with_sku = '';
        if(!empty($sku)){            
            $db_query = $wpdb->prepare("SELECT $wpdb->posts.ID,$wpdb->posts.post_type
                                        FROM $wpdb->posts
                                        LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
                                        WHERE $wpdb->posts.post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
                                        AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = '%s'
                                        ", $sku);
            $id_found_with_sku = $wpdb->get_row($db_query);
            if ($id_found_with_sku && (in_array($id_found_with_sku->post_type, array('product', 'product_variation')))) {
                $id_found_with_sku = $id_found_with_sku->ID;
                $this->is_product_exist = true;                
            }

        }

        if( !$this->merge ){
                                    
            if('skip' == $this->found_action){ // skip if found
                
                if($id && $id_found_with_id && $this->is_product_exist){
                    throw new Exception(sprintf('Product with same ID already exists. ID: %d',$id ));
                }elseif($id && $id_found_with_id && !$this->is_product_exist){
                    throw new Exception(sprintf('Importing Product(ID) conflicts with an existing post. ID: %d',$id ));
                }elseif($sku && $id_found_with_sku){
                    throw new Exception(sprintf('Product with same SKU already exists. SKU: %s',$sku ));
                } 
                
                if($this->skip_new){
                    throw new Exception('Skipping new item' );
                }
                
                $postdata = array( // if not specifiying id (id is empty) or if not found by given id or Product 
                    'post_title'      => $this->item_data['name'],
                    'post_status'    => 'importing',
                    'post_type'      => $this->post_type,
                );                
                if(isset($id) && !empty($id)){
                    $postdata['import_id'] = $id;
                }                   
                $post_id = wp_insert_post( $postdata, true );                
                if($post_id && !is_wp_error($post_id)){
                    $post = get_post($post_id);                    
                    return $post_id;
                }else{
                    throw new Exception($post_id->get_error_message());
                }
  
                throw new Exception('fasil !merge, found_action skip');
                
            }elseif('import' == $this->found_action){ // import if not found
                
                if($id && $id_found_with_id && $this->is_product_exist){
                    throw new Exception(sprintf('%s with same ID already exists. ID: %d',ucfirst($this->parent_module->module_base),$id ));
                }elseif($id && $id_found_with_id && !$this->is_product_exist && $this->use_same_id ){
                    throw new Exception(sprintf('Importing %s(ID) conflicts with an existing post. ID: %d',ucfirst($this->parent_module->module_base),$id ));
                }elseif($sku && $id_found_with_sku){
                    throw new Exception(sprintf('%s with same SKU already exists. SKU: %s',ucfirst($this->parent_module->module_base),$sku ));
                }
                
                if($this->skip_new){
                    throw new Exception('Skipping new item' );
                }
                
                $postdata = array(  // try to import
                    'post_title'      => $this->item_data['name'],
                    'post_status'    => 'importing',
                    'post_type'      => $this->post_type,
                );                
                if(isset($id) && !empty($id)){
                    $postdata['import_id'] = $id;
                }                   
                $post_id = wp_insert_post( $postdata, true );
                if($post_id && !is_wp_error($post_id)){
                    return $post_id;
                }else{
                    throw new Exception($post_id->get_error_message());
                }                            
            }
            
            
        }elseif($this->merge){
            
            if(empty($id) && empty($sku)){
                throw new Exception('Cannot update Product without ID or SKU');
            }  
            
            if('id' == $this->merge_with){
                
                if('skip' == $this->found_action){ // skip if not found or update 
                
                    if($id && $id_found_with_id && $this->is_product_exist){ //found Product by id 
                        return $id; // update
                    }elseif($id && $id_found_with_id && !$this->is_product_exist){ // found an item by id ,but not a Product
                        throw new Exception(sprintf('Importing %s(ID) conflicts with an existing post. ID: %d',ucfirst($this->parent_module->module_base),$id ));
                    }elseif(($id && !$id_found_with_id) || !$id){
                        throw new Exception(sprintf('Cannot find %s with given ID %d',ucfirst($this->parent_module->module_base),$id ));      
                    }elseif($sku && $id_found_with_sku){
                        throw new Exception(sprintf('%s with same SKU already exists. SKU: %s',ucfirst($this->parent_module->module_base),$sku ));
                    }
                                                            
                    if($this->skip_new){
                        throw new Exception('Skipping new item' );
                    }
                    $postdata = array(  
                        'post_title'      => $this->item_data['name'],
                        'post_status'    => 'importing',
                        'post_type'      => $this->post_type,
                    );                
                    if(isset($id) && !empty($id)){
                        $postdata['import_id'] = $id;
                    }                   
                    $post_id = wp_insert_post( $postdata, true );
                    if($post_id && !is_wp_error($post_id)){
                        return $post_id;
                    }else{
                        throw new Exception($post_id->get_error_message());
                    } 

                }elseif('import' == $this->found_action){  // import if not found                                     
                    if($id && $id_found_with_id && $this->is_product_exist){  //found Product by id 
                        return $id; // update
                    }elseif($id && $id_found_with_id && !$this->is_product_exist && $this->use_same_id ){ // found an item by id ,but not a Product, but should use the same id
                        throw new Exception(sprintf('Importing %s(ID) conflicts with an existing post. ID: %d',ucfirst($this->parent_module->module_base),$id ));
                    }elseif($sku && $id_found_with_sku){
                        throw new Exception(sprintf('%s with same SKU already exists. SKU: %s',ucfirst($this->parent_module->module_base),$sku ));
                    }
                    
                    if($this->skip_new){
                        throw new Exception('Skipping new item' );
                    }
                    $postdata = array(
                        'post_title'      => $this->item_data['name'],
                        'post_status'    => 'importing',
                        'post_type'      => $this->post_type,
                    );  
                    if(isset($id) && !empty($id)){
                        $postdata['import_id'] = $id;
                    }
                    $post_id = wp_insert_post( $postdata, true );
                    if($post_id && !is_wp_error($post_id)){
                        return $post_id;
                    }else{
                        throw new Exception($post_id->get_error_message());
                    }
                }

            }elseif('sku' == $this->merge_with){
                if(empty($sku)){
                    throw new Exception(sprintf('Cannot update without %s SKU',ucfirst($this->parent_module->module_base)) );
                }
                
                if('skip' == $this->found_action){ // skip if not found
                
                    if($sku && $id_found_with_sku ){ //  found Product by SKU
                        return $id_found_with_sku; // update
                    }elseif($sku && !$id_found_with_sku ){ // found an item by id ,but not a Product
                        throw new Exception(sprintf('Cannot find %s with given Product SKU %s',ucfirst($this->parent_module->module_base),$sku ));
                    }                                        
                    throw new Exception('fasil, merge, merge_with sku, found_action skip');

                }elseif('import' == $this->found_action){ // import as new if not found                                       
                    if($sku && $id_found_with_sku ){ //  found Product by SKU
                        return $id_found_with_sku; // update
                    }elseif($id && $id_found_with_id && !$this->is_product_exist && $this->use_same_id ){ // the given id is already used by other post
                        throw new Exception(sprintf('Importing %s(ID) conflicts with an existing post. ID: %d',ucfirst($this->parent_module->module_base),$id ));
                    }elseif($id && $id_found_with_id && $this->is_product_exist && $this->use_same_id ){ // the given id is already used by othere Product
                        throw new Exception(sprintf('%s with same ID already exists. ID: %d',ucfirst($this->parent_module->module_base),$id ));
                    }
                    
                    if($this->skip_new){
                        throw new Exception('Skipping new item' );
                    }
                    $postdata = array(
                        'post_title'      => $this->item_data['name'],
                        'post_status'    => 'importing',
                        'post_type'      => $this->post_type,
                    );   
                    if(isset($id) && !empty($id)){
                        $postdata['import_id'] = $id;
                    }
                    $post_id = wp_insert_post( $postdata, true );
                    if($post_id && !is_wp_error($post_id)){
                        return $post_id;
                    }else{
                        throw new Exception($post_id->get_error_message());
                    }  
                }                                                
            }
        } 
       
    }
    
    /**
     * Parse a comma-delineated field from a CSV.
     *
     * @param string $value Field value.
     *
     * @return array
     */
    public function wt_parse_seperation_field( $value, $separator = ',' ) {
        if ( empty( $value ) && '0' !== $value ) {
                return array();
        }

		$wt_clean_options = apply_filters('wt_clean_product_options', true);
		if ($wt_clean_options) {
			return array_map('wc_clean', $this->wt_explode_values($value, $separator));
		} else {
			return $this->wt_explode_values($value, $separator);
		}
    }
   
   /**
    * Parse a field that is generally '1' or '0' but can be something else.
    *
    * @param string $value Field value.
    *
    * @return bool|string
    */
   public function wt_parse_bool_field( $value ) {
           if ( '0' === $value ) {
                   return false;
           }

           if ( '1' === $value ) {
                   return true;
           }

           // Don't return explicit true or false for empty fields or values like 'notify'.
           return wc_clean( $value );
   }
        
        
    /**
     * Parse the tax status field.
     *
     * @param string $value Field value.
     *
     * @return string
     */
    public function wt_parse_tax_status_field( $value ) {
            if ( '' === $value ) {
                    return $value;
            }

            $tax_status = strtolower($value);
            $value = ('taxable' == $tax_status || 'shipping' == $tax_status) ? $tax_status : 'none';

            return wc_clean( $value );
    }

    /**
     * Parse a category field from a CSV.
     * Categories are separated by commas and subcategories are "parent > subcategory".
     *
     * @param string $value Field value.
     *
     * @return array of arrays with "parent" and "name" keys.
     */
    public function wt_parse_categories_field( $value ) {
        if ( empty( $value ) ) {
                return array();
        }
        $row_terms  = $this->wt_explode_values( $value ,'|' );
        $categories = array();

        foreach ( $row_terms as $row_term ) {
                $parent = 0;
                $_terms = array_map( 'trim', explode( '>', $row_term ) );
                $total  = count( $_terms );
                $wt_is_cron = apply_filters('wt_ier_product_import_categories', wp_doing_cron());

                foreach ( $_terms as $index => $_term ) {
                        // Check if category exists. Parent must be empty string or null if doesn't exists.
                        $term = term_exists( htmlspecialchars($_term), 'product_cat', $parent );
                        $term = apply_filters('wt_iew_importer_category_existance_check', $term, $parent, $_term);
                        if ( is_array( $term ) ) {
                                $term_id = $term['term_id'];
                                // Don't allow users without capabilities to create new categories.
                        } elseif ( ! $wt_is_cron && ! current_user_can( 'manage_product_terms' ) ) {
                                break;
                        } else {
                                $term = wp_insert_term( $_term, 'product_cat', array( 'parent' => intval( $parent ) ) );

                                if ( is_wp_error( $term ) ) {
                                        break; // We cannot continue if the term cannot be inserted.
                                }

                                $term_id = $term['term_id'];
                        }

                        // Only requires assign the last category.
                        if ( ( 1 + $index ) === $total ) {
                                $categories[] = $term_id;
                        } else {
                                // Store parent to be able to insert or query categories based in parent ID.
                                $parent = $term_id;
                        }
                }
        }

        return $categories;
    }

    /**
     * Parse a tag field from a CSV.
     *
     * @param string $value Field value.
     *
     * @return array
     */
    public function wt_parse_tags_field( $value ) {
        if ( empty( $value ) ) {
                return array();
        }

        $names = $this->wt_explode_values( $value, '|' );
        $tags  = array();

        foreach ( $names as $name ) {
                $term = get_term_by( 'name', $name, 'product_tag' );

                if ( ! $term || is_wp_error( $term ) ) {
                        $term = (object) wp_insert_term( $name, 'product_tag' );
                }

                if ( ! is_wp_error( $term ) ) {
                        $tags[] = $term->term_id;
                }
        }

        return $tags;
    }

    /**
     * Parse a tag field from a CSV with space separators.
     *
     * @param string $value Field value.
     *
     * @return array
     */
    public function wt_parse_tags_spaces_field( $value ) {
        if ( empty( $value ) ) {
                return array();
        }

        $names = $this->wt_explode_values( $value, ' ' );
        $tags  = array();

        foreach ( $names as $name ) {
                $term = get_term_by( 'name', $name, 'product_tag' );

                if ( ! $term || is_wp_error( $term ) ) {
                        $term = (object) wp_insert_term( $name, 'product_tag' );
                }

                if ( ! is_wp_error( $term ) ) {
                        $tags[] = $term->term_id;
                }
        }

        return $tags;
    }

    /**
     * Parse a shipping class field from a CSV.
     *
     * @param string $value Field value.
     *
     * @return int
     */
    public function wt_parse_shipping_class_field( $value ) {
            if ( empty( $value ) ) {
                    return 0;
            }

            $term = get_term_by( 'name', $value, 'product_shipping_class' );

            if ( ! $term || is_wp_error( $term ) ) {
                    $term = (object) wp_insert_term( $value, 'product_shipping_class' );
            }

            if ( is_wp_error( $term ) ) {
                    return 0;
            }

            return $term->term_id;
    }

    /**
     * Parse images list from a CSV. Images can be filenames or URLs.
     *
     * @param string $value Field value.
     *
     * @return array
     */
    public function wt_parse_images_field( $value ) {
            if ( empty( $value ) ) {
                    return array();
            }

            $images    = array();
			$default_separator = '|';
			if (strpos($value, ',') !== false) {
				$default_separator = ',';
			}
            $separator = apply_filters( 'wt_woocommerce_product_import_image_separator', $default_separator );
            foreach ( $this->wt_explode_values( $value, $separator ) as $image_data ) {
                $images[] = $this->arrange_product_images($image_data);
            }
			
            return $images;
    }
        
    /**
    * Arrange product images metadata
    */
    public function arrange_product_images($image_data)
    {
        if (!empty($image_data)) {
            $image_details[] = explode('!', $image_data);
            foreach ($image_details as $image_detail) {
                $j = 0;
                foreach ($image_detail as $current_image_detail) {
                    if ($j == 0) {
                        $images['url'] = trim($current_image_detail);
                        $j++;
                        continue;
                    }
                    $image_meta_data = array('alt :', 'title :', 'desc :', 'caption :');
                    foreach ($image_meta_data as $value) {
                        if (strpos($current_image_detail, $value) !== false) {
                            $image['key'] = str_replace(' :', '', $value);
                            $image['data'] = str_replace($value, '', $current_image_detail);
                        }
                        $images[trim(strtolower($image['key']))] = trim($image['data']);
                    }
                }
            }
            unset($image_details, $image_detail, $current_image_detail, $image, $image_data, $j);
        }
        return $images;
    }

	
    /**
     * Parse download file urls, we should allow shortskus here.
     *
     * Allow shortskus if present, othersiwe esc_url the value.
     *
     * @param string $value Field value.
     *
     * @return string
     */
    public function wt_parse_download_file_field( $value ) {
        // Absolute file paths.
        if ( 0 === strpos( $value, 'http' ) ) {
                return esc_url_raw( $value );
        }
        // Relative and shortsku paths.
        return wc_clean( $value );
    }

    /**
     * Parse an int value field
     *
     * @param int $value field value.
     *
     * @return int
     */
    public function wt_parse_int_field( $value ) {
        // Remove the ' prepended to fields that start with - if needed.

        return intval( $value );
    }
        
    /**
     * Parse the published field. 1 is published, 0 is private, -1 is draft.
     * Alternatively, 'true' can be used for published and 'false' for draft.
     *
     * @param string $value Field value.
     *
     * @return float|string
     */
    public function wt_parse_published_field( $value ) {	

        $product_status = strtolower($value);

        // Status is mapped from a special published field.
        if (in_array($product_status, array(1, '1', TRUE, 'true', 'publish'), TRUE)) {
            $product_status = 'publish';
        } elseif (in_array($product_status, array(0, '0', FALSE, 'false', 'draft'), TRUE)) {
            $product_status = 'draft';
        } 

        if (!in_array($product_status, array('publish', 'private', 'draft', 'pending', 'future', 'inherit', 'trash'))) {
            $product_status = 'publish';
        }

        return $product_status;

    }
    
    public function wt_parse_downloads_field($value){   
        if ( empty( $value ) ) {
            return array();
        }
        $download = array();
        foreach ( $this->wt_explode_values( $value, '|' ) as $key => $download_data ) {                
            @list($download[$key]['name'], $download[$key]['file']) = explode('::', $download_data);                 
        }  
        return $download;
        
    }
    
    
    /**
    * Parse a description value field
    *
    * @param string $description field value.
    *
    * @return string
    */
   public function wt_parse_description_field( $description ) {
           $parts = explode( "\\\\n", $description );
           foreach ( $parts as $key => $part ) {
                   $parts[ $key ] = str_replace( '\n', "\n", $part );
           }

           return implode( '\\\n', $parts );
   }

    

    public function get_default_data(){
        return array(
//                'id'                 => 0,
		'name'               => '',
		'slug'               => '',
		'date_created'       => null,
		'date_modified'      => null,
		'status'             => false,
		'featured'           => false,
		'catalog_visibility' => 'visible',
		'description'        => '',
		'short_description'  => '',
		'sku'                => '',
		'price'              => '',
		'regular_price'      => '',
		'sale_price'         => '',
		'date_on_sale_from'  => null,
		'date_on_sale_to'    => null,
		'total_sales'        => '0',
		'tax_status'         => 'taxable',
		'tax_class'          => '',
		'manage_stock'       => false,
		'stock_quantity'     => null,
		'stock_status'       => 'instock',
		'backorders'         => 'no',
		'low_stock_amount'   => '',
		'sold_individually'  => false,
		'weight'             => '',
		'length'             => '',
		'width'              => '',
		'height'             => '',
		'upsell_ids'         => array(),
		'cross_sell_ids'     => array(),
		'parent_id'          => 0,
		'reviews_allowed'    => true,
		'purchase_note'      => '',
		'attributes'         => array(),
		'default_attributes' => array(),
		'menu_order'         => 0,
		'post_password'      => '',
		'virtual'            => false,
		'downloadable'       => false,
		'category_ids'       => array(),
		'tag_ids'            => array(),
		'shipping_class_id'  => 0,
		'downloads'          => array(),
		'image_id'           => '',
		'gallery_image_ids'  => array(),
		'download_limit'     => -1,
		'download_expiry'    => -1,
		'rating_counts'      => array(),
		'average_rating'     => 0,
		'review_count'       => 0,
            // Grouped product
                'children'           => array()
	);
    }
       
    public function process_item($data) {

        try {
            do_action('wt_woocommerce_product_import_before_process_item', $data);
            $data = apply_filters('wt_woocommerce_product_import_process_item_data', $data);
                                    
            // Get product ID from SKU if created during the importation.
            if (empty($data['id']) && !empty($data['sku'])) {
                $product_id = wc_get_product_id_by_sku($data['sku']);

                if ($product_id) {
                    $data['id'] = $product_id;
                }
            }
            
            $short_circuit_import = apply_filters('wt_ier_short_circuit_product_import', false, $data);
            if ($short_circuit_import) {
                return array(
                    'id' => $data['id'],
                    'updated' => $this->merge,
                );
            }

            $object = $this->get_product_object($data); 
                        
            if (is_wp_error($object)) {
                return $object;
            }
            
            Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', "Found ".$object->get_type()." product object. ID:".$object->get_id());            
            
            if ('external' === $object->get_type()) {
                unset($data['manage_stock'], $data['stock_status'], $data['backorders'], $data['low_stock_amount']);
            }

            if ('variation' === $object->get_type()) {
                unset($data['date_created'],$data['date_modified']); // date cannot be in future date for a variation                
                if (isset($data['status']) && 'draft' === $data['status']) {
                    $data['status'] = 'private'; // Variations cannot be drafts - set to private.
                }
            }
                        
            if ('importing' === $object->get_status()) {
                $object->set_status($data['status']);
            }

            $result = $object->set_props(array_diff_key($data, array_flip(array('meta_data', 'raw_image_id', 'raw_gallery_image_ids', 'raw_attributes','attributes','default_attributes','images'))));
         
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }
            
            /* Updating wp_post data */
            if(isset($data['post_author']) && !empty($data['post_author'])){                
                $update_post_data = array(
                    'ID'           => $object->get_id(),
                    'post_author'   => $data['post_author'],
                );
                wp_update_post( $update_post_data );
                unset($data['post_author']);                
            }            
  
            if(isset($data['raw_attributes']) && is_array($data['raw_attributes'])){
                $data['raw_attributes'] = array_filter($data['raw_attributes'],array( $this, 'wt_remove_empty_attributes' ));

                // Sort attribute positions
                if (!function_exists('attributes_cmp')) {

                    function attributes_cmp($a, $b) {
                        if(!isset($a['position']) && !isset($b['position']))
                            return 0;
                        if ($a['position'] == $b['position'])
                            return 0;
                        return ( $a['position'] < $b['position'] ) ? -1 : 1;
                    }

                }
                uasort($data['raw_attributes'], 'attributes_cmp');
            }else{
                $data['raw_attributes'] = array();
            }
            
            

            if (in_array($object->get_type(),array('variation','subscription_variation'))) {
                $this->set_variation_data($object, $data);
            } else {
                $this->set_product_data($object, $data);
            }  
        
            $this->set_image_data($object, $data);
            
            $this->set_other_taxonomy_data($object, $data);
            
            $object = apply_filters('wt_woocommerce_product_import_pre_insert_product_object', $object, $data);
                                    
            $object->save();
            
            $this->set_meta_data($object, $data);
            
            
                /*
                 * WPML         
                 */
                $translation_post_details = array();
                if (isset($data['wpml']) && !empty($data['wpml'])) {
                    foreach ($data['wpml'] as $meta) {
                        $key = apply_filters('wt_wpml_import_post_meta_key', $meta['key']);
                        if ($key == 'original_product_id' || $key == 'original_product_sku' || $key == 'language_code') {
                            $translation_post_details[$key] = $meta['value'];
                        }
                    }
                    $translation_post_details['current_post_id'] = $object->get_id();
                }
                if ((!empty($translation_post_details['original_product_id']) || !empty($translation_post_details['original_product_sku'])) && !empty($translation_post_details['language_code'])) {
                    $this->wt_wpml_element_connect_on_insert($translation_post_details, $object->get_type());
                }            
            
            if($this->delete_existing){
                update_post_meta($object->get_id(), '_wt_delete_existing', 1);
            }
            
            $data['is_product_exist'] = $this->is_product_exist;
            do_action('wt_woocommerce_product_import_inserted_product_object', $object, $data);

            return $result =  array(
                'id' => $object->get_id(),
                'updated' => $this->merge,
            );
        } catch (Exception $e) {
            return new WP_Error('woocommerce_product_importer_error', $e->getMessage(), array('status' => $e->getCode()));
        }
    }
    
    public function wt_remove_empty_attributes($attribute) {
        if($this->merge_empty_cells){
            return $attribute;
        }
        if(!empty($attribute['value'])){
            return $attribute;
        }        
    }

    function get_product_object($data) {     
        $id = isset($data['id']) ? absint($data['id']) : 0;

        // Type is the most important part here because we need to be using the correct class and methods.
        if (isset($data['type'])) {
            $types = array_keys(wc_get_product_types());
            $types[] = 'variation';

            if (!in_array($data['type'], $types, true)) {
                return new WP_Error('woocommerce_product_importer_invalid_type', __('Invalid product type.', 'woocommerce'), array('status' => 401));
            }

            try {
                // Prevent getting "variation_invalid_id" error message from Variation Data Store.
                if ('variation' === $data['type']) {
                    $id = wp_update_post(
                            array(
                                'ID' => $id,
                                'post_type' => 'product_variation',
                            )
                    );
                }

                $product = wt_wc_get_product_object($data['type'], $id);
            } catch (WC_Data_Exception $e) {
                return new WP_Error('woocommerce_product_csv_importer_' . $e->getErrorCode(), $e->getMessage(), array('status' => 401));
            }
        } elseif (!empty($data['id'])) {
            $product = wc_get_product($id);
            if (!$product) {
                return new WP_Error(
                        'woocommerce_product_csv_importer_invalid_id',
                        /* translators: %d: product ID */ sprintf(__('Invalid product ID %d.', 'woocommerce'), $id), array(
                    'id' => $id,
                    'status' => 401,
                        )
                );
            }
        } else {
            $product = wt_wc_get_product_object('simple', $id);
        }

        return apply_filters('wt_woocommerce_product_import_get_product_object', $product, $data);
    }

    function set_variation_data(&$variation, $data) {
        $parent = false;

        // Check if parent exist.
        if (isset($data['parent_id'])) {
            $parent = wc_get_product($data['parent_id']);

            if ($parent) {
                $variation->set_parent_id($parent->get_id());
            }
        }
		if(isset($data['type']) && 'variation' == $data['type']){
			$parent = wc_get_product($variation->get_parent_id());
		}

        // Stop if parent does not exists.
        if (!$parent) {
            return new WP_Error('woocommerce_product_importer_missing_variation_parent_id', 'Variation cannot be imported: Missing parent ID or parent does not exist yet.', array('status' => 401));
        }

        // Stop if parent is a product variation.
        if ($parent->is_type('variation')) {
            return new WP_Error('woocommerce_product_importer_parent_set_as_variation', 'Variation cannot be imported: Parent product cannot be a product variation', array('status' => 401));
        }

        if (isset($data['raw_attributes']) && !empty($data['raw_attributes'])) {                                   
            
            $parent_attributes = $this->get_variation_parent_attributes($data['raw_attributes'], $parent);
            $attributes = is_array( $variation->get_attributes() ) ? $variation->get_attributes() : array();
            foreach ($data['raw_attributes'] as $attr => $attribute) {
                $attribute_id = 0;

                // Get ID if is a global attribute.
                if (!empty($attribute['taxonomy'])) {
                    $attr_name = isset($attribute['name']) ? $attribute['name'] : ($attr) ;
                    $attribute_id = $this->get_attribute_taxonomy_id($attr_name);
                }

                if ($attribute_id) {

                    $attribute_name = wc_attribute_taxonomy_name_by_id($attribute_id);
                } else {

                    $attr_name = isset($attribute['name']) ? $attribute['name'] : ($attr) ;
                    $attribute_name = sanitize_title($attr_name);
                }

                if (!isset($parent_attributes[$attribute_name]) || !$parent_attributes[$attribute_name]->get_variation()) {
                    continue;
                }

                $attribute_key = sanitize_title($parent_attributes[$attribute_name]->get_name());
                $attribute_value = isset($attribute['value']) ? current($attribute['value']) : '';

                if ($parent_attributes[$attribute_name]->is_taxonomy()) {
                    // If dealing with a taxonomy, we need to get the slug from the name posted to the API.
                    $term = get_term_by('name', $attribute_value, $attribute_name);

                    if ($term && !is_wp_error($term)) {
                        $attribute_value = $term->slug;
                    } else {
                        $attribute_value = sanitize_title($attribute_value);
                    }
                }

                $attributes[$attribute_key] = $attribute_value;
            }            
            $variation->set_attributes($attributes);
        }
    }

    function get_attribute_taxonomy_id($raw_name) {
        global $wpdb, $wc_product_attributes;

        // These are exported as labels, so convert the label to a name if possible first.
        $attribute_labels = wp_list_pluck(wc_get_attribute_taxonomies(), 'attribute_label', 'attribute_name');
        $attribute_name = array_search($raw_name, $attribute_labels, true);

        if (!$attribute_name) {
            $attribute_name = wc_sanitize_taxonomy_name($raw_name);
        }

        $attribute_id = wc_attribute_taxonomy_id_by_name($attribute_name);

        // Get the ID from the name.
        if ($attribute_id) {
            return $attribute_id;
        }

        $beautify_attr_name = ucfirst(str_replace('-', ' ', $raw_name)); //PIEPFW-1004
        // If the attribute does not exist, create it.
        $attribute_id = wc_create_attribute(
                array(
                    'name' => $beautify_attr_name,
                    'slug' => $attribute_name,
                    'type' => 'select',
                    'order_by' => 'menu_order',
                    'has_archives' => false,
                )
        );

        if (is_wp_error($attribute_id)) {
            throw new Exception($attribute_id->get_error_message(), 400);
        }

        // Register as taxonomy while importing.
        $taxonomy_name = wc_attribute_taxonomy_name($attribute_name);
        register_taxonomy(
                $taxonomy_name, apply_filters('woocommerce_taxonomy_objects_' . $taxonomy_name, array('product')), apply_filters(
                        'woocommerce_taxonomy_args_' . $taxonomy_name, array(
            'labels' => array(
                'name' => $raw_name,
            ),
            'hierarchical' => true,
            'show_ui' => false,
            'query_var' => true,
            'rewrite' => false,
                        )
                )
        );

        // Set product attributes global.
        $wc_product_attributes = array();

        foreach (wc_get_attribute_taxonomies() as $taxonomy) {
            $wc_product_attributes[wc_attribute_taxonomy_name($taxonomy->attribute_name)] = $taxonomy;
        }

        return $attribute_id;
    }

    function get_variation_parent_attributes($attributes, $parent) {
        $parent_attributes = $parent->get_attributes();
        $require_save = false;

        foreach ($attributes as $attr => $attribute) {
            $attribute_id = 0;

            // Get ID if is a global attribute.
            if (!empty($attribute['taxonomy'])) {
                $attr_name = isset($attribute['name']) ? $attribute['name'] : ($attr) ;
                $attribute_id = $this->get_attribute_taxonomy_id($attr_name);
            }

            if ($attribute_id) {
                $attribute_name = wc_attribute_taxonomy_name_by_id($attribute_id);
            } else {
                $attr_name = isset($attribute['name']) ? $attribute['name'] : ($attr) ;
                $attribute_name = sanitize_title($attr_name);
            }

            // Check if attribute handle variations.
            if (isset($parent_attributes[$attribute_name]) ) {//&& !$parent_attributes[$attribute_name]->get_variation()
                // Re-create the attribute to CRUD save and generate again.
                $parent_attributes[$attribute_name] = clone $parent_attributes[$attribute_name];
                $parent_attributes[$attribute_name]->set_variation($parent_attributes[$attribute_name]->get_variation());

                $require_save = true;
            }
        }

        // Save variation attributes.
        if ($require_save) {
            $parent->set_attributes(array_values($parent_attributes));
            $parent->save();
        }

        return $parent_attributes;
    }

    function set_product_data(&$product, $data) {
        if (isset($data['raw_attributes']) && !empty($data['raw_attributes'])) {
            $attributes = array();
            $default_attributes = array();
            $existing_attributes = $product->get_attributes();
            
            if(isset($existing_attributes)){
                foreach ($existing_attributes as $key => $existing_attribute) {
                    $attributes[] = $existing_attribute;
                }
            }
            
            foreach ($data['raw_attributes'] as $position => $attribute) {
                $attribute_id = 0;

                // Get ID if is a global attribute.
                if (!empty($attribute['taxonomy'])) {
                    $attribute_id = $this->get_attribute_taxonomy_id($attribute['name']);
                }

                // Set attribute visibility.
                if (isset($attribute['visible'])) {
                    $is_visible = $attribute['visible'];
                } else {
                    $is_visible = 1;
                }

                // Get name.
                $attribute_name = $attribute_id ? wc_attribute_taxonomy_name_by_id($attribute_id) : $attribute['name'];

                // Set if is a variation attribute based on existing attributes if possible so updates via CSV do not change this.
                $is_variation = 0;

                if ($existing_attributes) {
                    foreach ($existing_attributes as $existing_attribute) {  
                        if (method_exists($existing_attribute,'get_name') && $existing_attribute->get_name() === $attribute_name) {
                            $is_variation = $existing_attribute->get_variation();
                            break;
                        }
                    }
                }

                if ($attribute_id) {
                    if (isset($attribute['value'])) {
                        $options = array_map('wc_sanitize_term_text_based', $attribute['value']);
                        $options = array_filter($options, 'strlen');
                    } else {
                        $options = array();
                    }

                    // Check for default attributes and set "is_variation".
                    if (!empty($attribute['default']) && in_array($attribute['default'], $options, true)) {
                        $default_term = get_term_by('name', $attribute['default'], $attribute_name);

                        if ($default_term && !is_wp_error($default_term)) {
                            $default = $default_term->slug;
                        } else {
                            $default = sanitize_title($attribute['default']);
                        }

                        $default_attributes[$attribute_name] = $default;
                        $is_variation = 1;
                    }
                      // Set attribute variation.
                    if (isset($attribute['variation'])) {
                        $is_variation = $attribute['variation'];
                    } 
                    if (!empty($options)) {
                        $attribute_object = new WC_Product_Attribute();
                        $attribute_object->set_id($attribute_id);
                        $attribute_object->set_name($attribute_name);
                        $attribute_object->set_options($options);
                        $attribute_object->set_position($attribute['position']);
                        $attribute_object->set_visible($is_visible);
                        $attribute_object->set_variation($is_variation);
                        $attributes[] = $attribute_object;
                    }
                } elseif (isset($attribute['value'])) {
                    // Check for default attributes and set "is_variation".
                    if (!empty($attribute['default']) && in_array($attribute['default'], $attribute['value'], true)) {
                        $default_attributes[sanitize_title($attribute['name'])] = $attribute['default'];
                        $is_variation = 1;
                    }
                    // Set attribute variation.
                    if (isset($attribute['variation'])) {
                        $is_variation = $attribute['variation'];
                    } 
                    $attribute_object = new WC_Product_Attribute();
                    $attribute_object->set_name($attribute['name']);
                    $attribute_object->set_options($attribute['value']);
                    $attribute_object->set_position($attribute['position']);
                    $attribute_object->set_visible($is_visible);
                    $attribute_object->set_variation($is_variation);
                    $attributes[] = $attribute_object;
                }
                
                if (empty($attribute['value']) && $this->merge_empty_cells) {
                    foreach ($attributes as $key => $attribute) {
                        if (method_exists($attribute, 'get_name') && $attribute->get_name() === $attribute_name) {
                            unset($attributes[$key]) ;
                        }
                    }
                }
                
            }

            $product->set_attributes($attributes);

            // Set variable default attributes.
            if ($product->is_type('variable')) {
                $product->set_default_attributes($default_attributes);
            }
        }
    }

    function set_image_data(&$product, $data) {
        $upload_dir = wp_upload_dir();
        // Image URLs need converting to IDs before inserting.
        if (isset($data['raw_image'])) {
            Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', 'Setting Featured Image. URL:'.$data['raw_image']['url']);
            if (!strstr($data['raw_image']['url'], 'http')) {
                $attachment_file = $this->recursive_file_search($upload_dir['basedir'], $data['raw_image']['url']);
                if (file_exists($attachment_file)) {
                    $attachment_url = str_replace(trailingslashit(ABSPATH), trailingslashit(site_url()), $attachment_file);
                    if ($info = wp_check_filetype($attachment_file)){
                        $data['raw_image']['url'] = $attachment_url;
                    }

                }
            }
            $image_id = $this->get_attachment_id_from_url($data['raw_image']['url'], $product->get_id());
            if($image_id){             
                $this->wt_set_attachment_data($image_id,$data['raw_image']);
                $product->set_image_id($image_id);
                Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', 'Attachment ID:'.$image_id);
            }                        
        }

        // Gallery image URLs need converting to IDs before inserting.
        if (isset($data['raw_gallery_image'])) {
            $gallery_image_ids = array();
            Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', 'Setting Gallery Images.');
            foreach ($data['raw_gallery_image'] as $image_id) {
                if(isset($image_id) && (!empty($image_id))) {
                    Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', 'Image URL:'.$image_id['url']);
                    if (!strstr($image_id['url'], 'http')) {
                        $attachment_file = $this->recursive_file_search($upload_dir['basedir'], $image_id['url']);
                        if (file_exists($attachment_file)) {
                            $attachment_url = str_replace(trailingslashit(ABSPATH), trailingslashit(site_url()), $attachment_file);
                            if ($info = wp_check_filetype($attachment_file)){
                                $image_id['url'] = $attachment_url;
                            }

                        }
                    }
                    $gallery_image_id = $this->get_attachment_id_from_url($image_id['url'], $product->get_id());
                    if($gallery_image_id){
                        $this->wt_set_attachment_data($gallery_image_id, $image_id);
                        $gallery_image_ids[] = $gallery_image_id;
                        Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', 'Attachment ID:'.$gallery_image_id);
                    }
                }
            }
            $product->set_gallery_image_ids(array_filter($gallery_image_ids));
        }
    }
    

	/**
     * If fetching attachments is enabled then attempt to create a new attachment
	 * This function is used in different code snippets - do not remove
     *
     * @param array $post Attachment post details from WXR
     * @param string $url URL to fetch attachment from
     * @return int|WP_Error Post ID on success, WP_Error otherwise
     */
    public function process_attachment($post, $url, $post_id) {
        $attachment_id = '';
        $attachment_url = '';
        $attachment_file = '';
        $upload_dir = wp_upload_dir();


        if (strstr($url, site_url())) {
            
            $image_id = $this->wt_get_image_id_by_url($url);
            if($image_id){
                $attachment_id = $image_id;
                return $attachment_id;
            }
            
            $abs_url = str_replace(trailingslashit(site_url()), trailingslashit(ABSPATH), urldecode($url));
            $new_name = wp_unique_filename($upload_dir['path'], basename(urldecode($url)));
            $new_url = trailingslashit($upload_dir['path']) . $new_name;

            if (copy($abs_url, $new_url)) {
                $url = basename($new_url);
            }
        }

        if (!strstr($url, 'http')) { // if not a url 
            // Local file. 
            // We have the path, check it exists, check in /wp-content/uploads/product_images/
            $attachment_file = trailingslashit($upload_dir['basedir']) . 'product_images/' . $url;
            
            // We have the path, check it exists, check in current month dir
            if (!file_exists($attachment_file))
                $attachment_file = trailingslashit($upload_dir['path']) . $url;
            
            // We have the path, check it exists, check in /wp-content/uploads/ and its sub folders(Recursive)
             if (!file_exists($attachment_file)){   
                $attachment_file = $this->recursive_file_search($upload_dir['basedir'],$url); 
             }            

            // We have the path, check it exists
            if (file_exists($attachment_file)) {

                $attachment_url = str_replace(trailingslashit(ABSPATH), trailingslashit(site_url()), $attachment_file);

                if ($info = wp_check_filetype($attachment_file))
                    $post['post_mime_type'] = $info['type'];
                else
                    return new WP_Error('attachment_processing_error', __('Invalid file type', 'wordpress-importer'));
                
                
                $image_id = $this->wt_get_image_id_by_url($attachment_url);
                if($image_id){
                    $attachment_id = $image_id;
                    return $attachment_id;
                }

                $post['guid'] = $attachment_url;
 
                $attachment_id = wp_insert_attachment($post, $attachment_file, $post_id);
                
            } else  {                                               
                return new WP_Error('attachment_processing_error', __('Local image did not exist!', 'wordpress-importer'));
            }
        } else {

            // if the URL is absolute, but does not contain address, then upload it assuming base_site_url
            if (preg_match('|^/[\w\W]+$|', $url))
                $url = rtrim(site_url(), '/') . $url;

            $upload = $this->fetch_remote_file($url, $post); 
            if (is_wp_error($upload))
                return $upload;

            if ($info = wp_check_filetype($upload['file']))
                $post['post_mime_type'] = $info['type'];
            else
                return new WP_Error('attachment_processing_error', __('Invalid file type', 'wordpress-importer'));

            $post['guid'] = $upload['url'];
            $attachment_file = $upload['file'];
            $attachment_url = $upload['url'];
            
            // as per wp-admin/includes/upload.php
            $attachment_id = wp_insert_attachment($post, $upload['file'], $post_id);
            
            unset($upload);
        }

        if (!is_wp_error($attachment_id) && $attachment_id > 0) {

        }
        $this->regenerate_thumbnail($attachment_id,$attachment_file);
        return $attachment_id;
    }
    
    public function wt_get_image_id_by_url($image_url) {
        
        if (empty($image_url)) {
            return 0;
        }

        $id = 0;
        $upload_dir = wp_upload_dir(null, false);
        $base_url = $upload_dir['baseurl'] . '/';
        $remove_protocol = array("http://","https://");
        
        // Check first if attachment is inside the WordPress uploads directory, or we're given a filename only.
        if (false !== strpos(str_replace($remove_protocol,"",$image_url), str_replace($remove_protocol,"",$base_url)) || false === strpos($image_url, '://')) {
            // Search for yyyy/mm/slug.extension or slug.extension - remove the base URL.
            $file = str_replace(str_replace($remove_protocol,"",$base_url), '', str_replace($remove_protocol,"",$image_url));
            $args = array(
                'post_type' => 'attachment',
                'post_status' => 'any',
                'fields' => 'ids',
                'meta_query' => array(// @codingStandardsIgnoreLine.
                    'relation' => 'OR',
                    array(
                        'key' => '_wp_attached_file',
                        'value' => '^' . $file,
                        'compare' => 'REGEXP',
                    ),
                    array(
                        'key' => '_wp_attached_file',
                        'value' => '/' . $file,
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key' => '_wt_attachment_source',
                        'value' => '/' . $file,
                        'compare' => 'LIKE',
                    ),
                ),
            );
        } else {
            // This is an external URL, so compare to source.
            $args = array(
                'post_type' => 'attachment',
                'post_status' => 'any',
                'fields' => 'ids',
                'meta_query' => array(// @codingStandardsIgnoreLine.
                    array(
                        'value' => $image_url,
                        'key' => '_wt_attachment_source',
                    ),
                ),
            );
        }
	
        $ids = get_posts($args); // @codingStandardsIgnoreLine.
        $ids = apply_filters('wt_ier_product_image_guid', $ids, $args); 

        if ($ids) {
            $id = current($ids);
        }
        return $id;
    }

	
    public function recursive_file_search($directory,$file_name){
        $it = new RecursiveDirectoryIterator($directory);
                //$display = Array ( 'jpeg', 'jpg' );
                foreach (new RecursiveIteratorIterator($it) as $file) {
                    //if (in_array(strtolower(array_pop(explode('.', $file))), $display)) {
                        $file = str_replace('\\', '/', $file);
                        if (substr(strrchr($file, '/'), 1) == $file_name) {
                            return $file;
                        }
                    //}
                }
    }

    /**
     * Attempt to download a remote file attachment
     */
    public function fetch_remote_file($url, $post) {

        // extract the file name and extension from the url
        $file_name = basename(current(explode('?', $url)));
        $wp_filetype = wp_check_filetype($file_name, null);
        $parsed_url = @parse_url($url);

        // Check parsed URL
        if (!$parsed_url || !is_array($parsed_url))
            return new WP_Error('import_file_error', 'Invalid URL');

        // Ensure url is valid
        $url = str_replace(" ", '%20', $url);
        // Get the file
        $response = wp_remote_get($url, array(
                'timeout' => 60,
                "user-agent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:56.0) Gecko/20100101 Firefox/56.0",
                'sslverify' => FALSE
        ));
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200)
            return new WP_Error('import_file_error', 'Error getting remote image');

        // Ensure we have a file name and type
        if (!$wp_filetype['type']) {

            $headers = wp_remote_retrieve_headers($response);

            if (isset($headers['content-disposition']) && strstr($headers['content-disposition'], 'filename=')) {

                $disposition = end(explode('filename=', $headers['content-disposition']));
                $disposition = sanitize_file_name($disposition);
                $file_name = $disposition;
                if (isset($headers['content-type']) && strstr($headers['content-type'], 'image/')) {
                    $supported_image = array(
                        'gif',
                        'jpg',
                        'jpeg',
                        'png',
                        'pdf'
                    );
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); // Using strtolower to overcome case sensitive
                    if (!in_array($ext, $supported_image)) {
                        $file_name = $file_name . '.' . str_replace('image/', '', $headers['content-type']);
                    }
                }
            } elseif (isset($headers['content-type']) && strstr($headers['content-type'], 'image/')) {

                $file_name = 'image.' . str_replace('image/', '', $headers['content-type']);
                $file_name = preg_replace('/^([^;]*).*$/', '$1', $file_name);

            }

            unset($headers);
        }

        // Upload the file
        $upload = wp_upload_bits($file_name, '', wp_remote_retrieve_body($response));

        if ($upload['error'])
            return new WP_Error('upload_dir_error', $upload['error']);

        // Get filesize
        $filesize = filesize($upload['file']);

        if (0 == $filesize) {
            @unlink($upload['file']);
            unset($upload);
            return new WP_Error('import_file_error', __('Zero size file downloaded', 'wf_csv_import_export'));
        }

        unset($response);

        return $upload;
    }
    
    public function regenerate_thumbnail($id,$fullsizepath) {
        $satrt_time = microtime(TRUE);
            
        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            include_once ABSPATH . 'wp-admin/includes/image.php';
	}

        $metadata = wp_generate_attachment_metadata($id, $fullsizepath);
        // If this fails, then it just means that nothing was changed (old value == new value)
        wp_update_attachment_metadata($id, $metadata);

    }
    
    
    
    function get_attachment_id_from_url($url, $product_id) {

        if (empty($url)) {
            return 0;
        }

        $id = 0;
        $upload_dir = wp_upload_dir(null, false);
        $base_url = $upload_dir['baseurl'] . '/';
        $remove_protocol = array("http://","https://");
        
        // Check first if attachment is inside the WordPress uploads directory, or we're given a filename only.
        if (false !== strpos(str_replace($remove_protocol,"",$url), str_replace($remove_protocol,"",$base_url)) || false === strpos($url, '://')) {
            // Search for yyyy/mm/slug.extension or slug.extension - remove the base URL.
            $file = str_replace(str_replace($remove_protocol,"",$base_url), '', str_replace($remove_protocol,"",$url));
            $args = array(
                'post_type' => 'attachment',
                'post_status' => 'any',
                'fields' => 'ids',
                'meta_query' => array(// @codingStandardsIgnoreLine.
                    'relation' => 'OR',
                    array(
                        'key' => '_wp_attached_file',
                        'value' => '^' . $file,
                        'compare' => 'REGEXP',
                    ),
                    array(
                        'key' => '_wp_attached_file',
                        'value' => '/' . $file,
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key' => '_wt_attachment_source',
                        'value' => '/' . $file,
                        'compare' => 'LIKE',
                    ),
                ),
            );
        } else {
            // This is an external URL, so compare to source.
            $args = array(
                'post_type' => 'attachment',
                'post_status' => 'any',
                'fields' => 'ids',
                'meta_query' => array(// @codingStandardsIgnoreLine.
                    array(
                        'value' => $url,
                        'key' => '_wt_attachment_source',
                    ),
                ),
            );
        }

		
        $ids = get_posts($args); // @codingStandardsIgnoreLine.
		$ids = apply_filters('wt_ier_image_force_update', $ids,array('url'=>$url,'product_id'=>$product_id)); // IER-355 - add_filter( 'wt_ier_image_force_update', '__return_false' );

        if ($ids) {
            $id = current($ids);
        }

        // Upload if attachment does not exists.
        if (!$id && stristr($url, '://')) {
            add_filter( 'https_ssl_verify', '__return_false' );
            // Bypass image thumbnail generation - it will be in background after the last batch.
            add_filter( 'intermediate_image_sizes', '__return_empty_array' );			
            $upload = wc_rest_upload_image_from_url($url);            

            if (is_wp_error($upload)) {
                $allow_custom_rendered_image = apply_filters('wt_allow_custom_rendered_image', true);
                if($allow_custom_rendered_image){
                    $upload = $this->fetch_remote_file($url, array()); 
                    $wc_upload = false;
                    if (is_wp_error($upload)) {
                        Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', 'URL:'.$url.' Reason:'.$upload->get_error_message());
                        return;
                    }
                }else{
                    Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', 'URL:'.$url.' Reason:'.$upload->get_error_message());
                        return;
                }
                
            }
			 
            if (apply_filters('wt_use_wc_rest_upload', true) && $wc_upload ) {
				$id = wc_rest_set_uploaded_image_as_attachment( $upload, $product_id );
			} else {
				$info = wp_check_filetype($upload['file']);
				$post = array();
				$post['post_mime_type'] = $info['type'];
				$post['guid'] = $upload['url'];
				$post['post_title'] = isset($upload['name']) ? $upload['name'] : '';
				$id = wp_insert_attachment($post, $upload['file'], $product_id);
			}

			if (!wp_attachment_is_image($id)) {
                
                if(!$wc_upload && 'application/pdf' == $post['post_mime_type']) {
					$file_url = $post['guid'];

                    // Use wp_upload_dir to get the upload directory information.
                    $upload_dir = wp_upload_dir();

					$base_path = $upload_dir['basedir'];
					$file_path = str_replace($base_path, '', $file_url);
					$file_path = explode('uploads/',$file_path );
					
					$full_file_path = $base_path.'/'. $file_path[1];
					
								  
					add_filter('intermediate_image_sizes', function($sizes) { return array('thumbnail', 'medium', 'medium_large', 'large'); });

					wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $full_file_path) );
					set_post_thumbnail($product_id,$id);
				}

                /* translators: %s: image URL */
                Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', sprintf(__('Not able to attach "%s".'), $url));
                return;
            }

            // Save attachment source for future reference.
            update_post_meta($id, '_wt_attachment_source', $url);
        }

        if (!$id) {
            /* translators: %s: image URL */
            Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', sprintf(__('Unable to use image "%s".'), $url));
            return;
        }

        return $id;
    }

    function set_meta_data(&$product, $data) {
        if (isset($data['meta_data'])) {
            foreach ($data['meta_data'] as $meta) {
                

                if ('' == $meta)
                    continue;

                if ('_variation_description' == $meta['key']) { //updating variation description(IER-331)
					update_post_meta($product->get_id(), 'description', $meta['value']);
                }
                update_post_meta($product->get_id(), $meta['key'], $meta['value']);
            }
        }
    }

    function set_other_taxonomy_data(&$product, $data) {
        
        if (isset($data['other_taxomomy_data']) && !empty($data['other_taxomomy_data'])) {
             $terms_to_set = array();

            foreach ($data['other_taxomomy_data'] as $term_group) {

                $taxonomy = $term_group['taxonomy'];
                $terms = $term_group['terms'];

                if (!$taxonomy || !taxonomy_exists($taxonomy)) {
                    continue;
                }

                if (!is_array($terms)) {
                    $terms = array($terms);
                }

                $terms_to_set[$taxonomy] = array();

                foreach ($terms as $term_id) {

                    $terms_to_set[$taxonomy][] = intval($term_id);
                }
            }
            foreach ($terms_to_set as $tax => $ids) {
                if(isset($data['id'])&& !empty($data['id'])){
                    wp_set_post_terms($data['id'], $ids, $tax, false);
                }
            }

            unset($data['other_taxomomy_data'], $terms_to_set);

        }
    }
    
    function wt_set_attachment_data($attachment_id, $attachment_data) {
        $attachment = array(
            'ID' => $attachment_id,
            'post_content' => isset($attachment_data['desc']) ? $attachment_data['desc'] : '',
            'post_excerpt' => isset($attachment_data['caption']) ? $attachment_data['caption'] : '',
        );
        if (isset($attachment_data['title']) && !empty($attachment_data['title'])) {
            $attachment['post_title'] = $attachment_data['title'];
        }
        wp_update_post($attachment);
        if (isset($attachment_data['alt']) && !empty($attachment_data['alt'])) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $attachment_data['alt']);
        }
    }
    
    // Used this function to bypass - Non-static method WC_Product_Data_Store_CPT::get_product_type() cannot be called statically
    public static function wt_get_product_type($product_id) {
        
        $cache_key = WC_Cache_Helper::get_cache_prefix('product_' . $product_id) . '_type_' . $product_id;
        $product_type = wp_cache_get($cache_key, 'products');

        if ($product_type) {
            return $product_type;
        }

        $post_type = get_post_type($product_id);

        if ('product_variation' === $post_type) {
            $product_type = 'variation';
        } elseif ('product' === $post_type) {
            $terms = get_the_terms($product_id, 'product_type');
            $product_type = !empty($terms) && !is_wp_error($terms) ? sanitize_title(current($terms)->name) : 'simple';
        } else {
            $product_type = false;
        }

        wp_cache_set($cache_key, $product_type, 'products');

        return $product_type;
    }

    
    /**
     * WPML compatibility
     */
    public function wt_wpml_element_connect_on_insert($translation_post_details, $get_type) {
        if ($translation_post_details) {
            $post_product = ( isset($get_type) && $get_type == 'variation' ) ? 'post_product_variation' : 'post_product';
            // https://wpml.org/wpml-hook/wpml_element_type/
            $wpml_element_type = apply_filters('wpml_element_type', $post_product);

            // get the language info of the original post
            // https://wpml.org/wpml-hook/wpml_element_language_details/

            if (isset($translation_post_details['original_product_id']) && !empty($translation_post_details['original_product_id']))
                $original_product = wc_get_product($translation_post_details['original_product_id']);
            $original_product_id = '';
            if (!empty($original_product) && is_object($original_product)) {
                $original_product_id = $translation_post_details['original_product_id'];
            }
            if (!$original_product_id && isset($translation_post_details['original_product_sku']) && !empty($translation_post_details['original_product_sku'])) {
                $original_product_id = wc_get_product_id_by_sku($translation_post_details['original_product_sku']);
            }
            if ($original_product_id) {

                $original_post_language_info = self::wt_get_wpml_original_post_language_info($original_product_id);

                $set_language_args = array(
                    'element_id' => $translation_post_details['current_post_id'],
                    'element_type' => $wpml_element_type,
                    'trid' => $original_post_language_info->trid,
                    'language_code' => $translation_post_details['language_code'],
                    'source_language_code' => $original_post_language_info->language_code
                );

                do_action('wpml_set_element_language_details', $set_language_args);
            }
        }
    }
        /**
         * Get info like language code, parent product ID etc by product id.
         * @param int Product ID.
         * @return array/false.
         */
        public static function wt_get_wpml_original_post_language_info($element_id) {
            $get_language_args = array('element_id' => $element_id, 'element_type' => 'post_product');
            $original_post_language_info = apply_filters('wpml_element_language_details', null, $get_language_args);
            return $original_post_language_info;
        }
    
        
        public function wt_relative_product_field_update($field, $amount, $mode, $product_id) {

                    $old_field_value = intval(get_post_meta($product_id, $field, true));
                    if ($mode == 'increase') {                        
                        $new_field_value = $old_field_value + $amount;
                    }

                    if ($mode == 'decrease') {
                        $new_field_value = $old_field_value - $amount;

                    }
                    return $new_field_value;
        }
              /**
         * Generate product description using ChatGPT.
         * 
         * @since 2.3.1
         * 
         * @param string $product_description
         * @param string $api_key
         * @return string 
         */
        
         public function generate_product_description_using_openai($product_description, $api_key) {
            
            $api_url = 'https://api.openai.com/v1/chat/completions';            
            $tone = Wt_Import_Export_For_Woo_Common_Helper::get_advanced_settings('chatgpt_tone');
            // casual - Relaxed, informal, conversational tone. Like chatting with a friend.
            // formal - A polished, well-structured product description, adhering to professional standards.
            // flowery - Ornate, elaborate, poetic tone. Rich in descriptive language.
            // convincing - A persuasive, compelling product description, designed to influence decisions and drive action.
            $messages = array(
                array(
                    'role' => 'user',
                    'content' => "Write a product description with $tone tone, from the following features: '$product_description'.",
                ),
            );
            // Configure curl request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    array(
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $api_key,
                    )
            );
            $post_data = array(
                'messages' => $messages,
                'model' => 'gpt-3.5-turbo',
                'temperature' => 1,
            );
            $post_data_json = json_encode($post_data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_json);
            $response = curl_exec($ch);
            $generated_text = $product_description;
            if (curl_error($ch)) {
                Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', "ChatGPT API error response : " . curl_error($ch));  
              
            } else {
                $response_data = json_decode($response, true);
                if (isset($response_data['error']) && !empty($response_data['error'])) {
                    Wt_Import_Export_For_Woo_Logwriter::write_log($this->parent_module->module_base, 'import', "ChatGPT API error response : " . $response_data['error']['message']);
                
                } else {
                    $generated_text = $response_data['choices'][0]['message']['content'];
                    // If cannot find proper description, return the input
                    if (strpos($generated_text, 'We apologize') !== false) {
                        $generated_text = $product_description;
                    }
                }
            }
            curl_close($ch);
            return $generated_text;
        }
}
