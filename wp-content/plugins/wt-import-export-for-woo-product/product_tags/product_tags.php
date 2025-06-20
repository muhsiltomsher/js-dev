<?php
/**
 * Product tags section of the plugin
 *
 * @link           
 *
 * @package  Wt_Import_Export_For_Woo 
 */
if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('Wt_Import_Export_For_Woo_Product_Tags')){
class Wt_Import_Export_For_Woo_Product_Tags {

    public $module_id = '';
    public static $module_id_static = '';
    public $module_base = 'product_tags';
    public $module_name = 'Tags Import Export for WooCommerce';
    public $min_base_version= '1.0.0'; /* Minimum `Import export plugin` required to run this add on plugin */

    private $all_meta_keys = array();
    private $found_product_tag_meta = array();
    private $selected_column_names = null;

    public function __construct()
    {
        /**
        *   Checking the minimum required version of `Import export plugin` plugin available
        */
        if(!Wt_Import_Export_For_Woo_Common_Helper::check_base_version($this->module_base, $this->module_name, $this->min_base_version))
        {
            return;
        }
        if(!function_exists('is_plugin_active'))
        {
            include_once(ABSPATH.'wp-admin/includes/plugin.php');
        }
        if(!is_plugin_active('woocommerce/woocommerce.php'))
        {
            return;
        }
        
        $this->module_id = Wt_Import_Export_For_Woo::get_module_id($this->module_base);

        self::$module_id_static = $this->module_id;
                       
        add_filter('wt_iew_exporter_post_types', array($this, 'wt_iew_exporter_post_types'), 10, 1);
        add_filter('wt_iew_importer_post_types', array($this, 'wt_iew_exporter_post_types'), 10, 1);

        add_filter('wt_iew_exporter_alter_filter_fields', array($this, 'exporter_alter_filter_fields'), 10, 3);
        
        add_filter('wt_iew_exporter_alter_mapping_fields', array($this, 'exporter_alter_mapping_fields'), 10, 3);        
        add_filter('wt_iew_importer_alter_mapping_fields', array($this, 'get_importer_post_columns'), 10, 3);
        
        add_filter('wt_iew_exporter_alter_advanced_fields', array($this, 'exporter_alter_advanced_fields'), 10, 3);
        add_filter('wt_iew_importer_alter_advanced_fields', array($this, 'importer_alter_advanced_fields'), 10, 3);        
        
        add_filter('wt_iew_exporter_alter_meta_mapping_fields', array($this, 'exporter_alter_meta_mapping_fields'), 10, 3);
        add_filter('wt_iew_importer_alter_meta_mapping_fields', array($this, 'importer_alter_meta_mapping_fields'), 10, 3);

        add_filter('wt_iew_exporter_alter_mapping_enabled_fields', array($this, 'exporter_alter_mapping_enabled_fields'), 10, 3);
        add_filter('wt_iew_importer_alter_mapping_enabled_fields', array($this, 'exporter_alter_mapping_enabled_fields'), 10, 3);

        add_filter('wt_iew_exporter_do_export', array($this, 'exporter_do_export'), 10, 7);
        add_filter('wt_iew_importer_do_import', array($this, 'importer_do_import'), 10, 8);

        add_filter('wt_iew_importer_steps', array($this, 'importer_steps'), 10, 2);
    }


    /**
    *   Altering advanced step description
    */
    public function importer_steps($steps, $base)
    {
        if($this->module_base==$base)
        {
            $steps['advanced']['description']=__('Use options from below to decide updates to existing tags, batch import count or schedule an import. You can also save the template file for future imports.', 'wt-import-export-for-woo');
        }
        return $steps;
    }
    
    public function importer_do_import($import_data, $base, $step, $form_data, $selected_template_data, $method_import, $batch_offset, $is_last_batch) {        
        if ($this->module_base != $base) {
            return $import_data;
        }
        
        if(0 == $batch_offset){                        
            $memory = size_format(self::wt_let_to_num(ini_get('memory_limit')));
            $wp_memory = size_format(self::wt_let_to_num(WP_MEMORY_LIMIT));                      
            Wt_Import_Export_For_Woo_Logwriter::write_log($this->module_base, 'import', '---[ New import started at '.date('Y-m-d H:i:s').' ] PHP Memory: ' . $memory . ', WP Memory: ' . $wp_memory);
        }
        
        include plugin_dir_path(__FILE__) . 'import/import.php';
        $import = new Wt_Import_Export_For_Woo_Tags_Import($this);
        
        $response = $import->prepare_data_to_import($import_data,$form_data,$batch_offset,$is_last_batch);
        
        if($is_last_batch){
            Wt_Import_Export_For_Woo_Logwriter::write_log($this->module_base, 'import', '---[ Import ended at '.date('Y-m-d H:i:s').']---');
        }
        
        return $response;
    }
    
    public static function wt_let_to_num( $size ) {
            $l   = substr( $size, -1 );
            $ret = (int) substr( $size, 0, -1 );
            switch ( strtoupper( $l ) ) {
                    case 'P':
                            $ret *= 1024;
                            // No break.
                    case 'T':
                            $ret *= 1024;
                            // No break.
                    case 'G':
                            $ret *= 1024;
                            // No break.
                    case 'M':
                            $ret *= 1024;
                            // No break.
                    case 'K':
                            $ret *= 1024;
                            // No break.
            }
            return $ret;
    }

    public function exporter_do_export($export_data, $base, $step, $form_data, $selected_template_data, $method_export, $batch_offset) {
        if ($this->module_base != $base) {
            return $export_data;
        }
        
        
        switch ($method_export) {
            case 'quick':
                $this->set_export_columns_for_quick_export($form_data);  
                break;

            case 'template':
            case 'new':
                $this->set_selected_column_names($form_data);
                break;
            
            default:
                break;
        }

        include plugin_dir_path(__FILE__) . 'export/export.php';
        $export = new Wt_Import_Export_For_Woo_Tags_Export($this);

        $header_row = $export->prepare_header();

        $data_row = $export->prepare_data_to_export($form_data, $batch_offset);

        $export_data = array(
            'head_data' => $header_row,
            'body_data' => $data_row['data'],
        );
        
        if(isset($data_row['total']) && !empty($data_row['total'])){
            $export_data['total'] = $data_row['total'];
        }

        return $export_data;
    }

    /**
     * Adding current post type to export list
     *
     */
    public function wt_iew_exporter_post_types($arr) {
        $arr['product_tags'] = __('Product Tags', 'wt-import-export-for-woo');
        return $arr;
    }
    
    
    
    /*
     * Setting default export columns for quick export
     */
    
    public function set_export_columns_for_quick_export($form_data) {

        $post_columns = self::get_tags_post_columns();

        $this->selected_column_names = array_combine(array_keys($post_columns), array_keys($post_columns));
        
        if (isset($form_data['method_export_form_data']['mapping_enabled_fields']) && !empty($form_data['method_export_form_data']['mapping_enabled_fields'])) {
            foreach ($form_data['method_export_form_data']['mapping_enabled_fields'] as $value) {
                $additional_quick_export_fields[$value] = array('fields' => array());
            }

            $export_additional_columns = $this->exporter_alter_meta_mapping_fields($additional_quick_export_fields, $this->module_base, array());
            foreach ($export_additional_columns as $value) {
                $this->selected_column_names = array_merge($this->selected_column_names, $value['fields']);
            }
        }
    }
    

    public static function get_product_review_statuses() {
        $product_statuses = array('publish', 'private', 'draft', 'pending', 'future');
        return apply_filters('wt_iew_allowed_product_review_statuses', array_combine($product_statuses, $product_statuses));
    }

    public static function get_tags_sort_columns() {
            $sort_columns = array(
                'id' => __('Tag ID', 'wt-import-export-for-woo'),
                'name' => __('Tag name', 'wt-import-export-for-woo'),
                'slug' => __('Tag slug', 'wt-import-export-for-woo'),
            );
            return apply_filters('wt_iew_allowed_tags_sort_columns', $sort_columns);
    }

    public static function get_tags_post_columns() {

        return include plugin_dir_path(__FILE__) . 'data/data-product-review-columns.php';
    }
    
    public function get_importer_post_columns($fields, $base, $step_page_form_data) {

        if ($base != $this->module_base) {
            return $fields;
        }
        $colunm = include plugin_dir_path(__FILE__) . 'data/data/data-wf-reserved-fields-pair.php';
//        $colunm = array_map(function($vl){ return array('title'=>$vl, 'description'=>$vl); }, $arr); 
        return $colunm;
    }


    public function exporter_alter_mapping_enabled_fields($mapping_enabled_fields, $base, $form_data_mapping_enabled_fields) {
        if ($base == $this->module_base) {
            unset($mapping_enabled_fields['hidden_meta']);
        }

        return $mapping_enabled_fields;
    }

    
    public function exporter_alter_meta_mapping_fields($fields, $base, $step_page_form_data) {
        if ($base != $this->module_base) {
            return $fields;
        }
        foreach ($fields as $key => $value) {
            switch ($key) {
                case 'meta':
                    $meta_attributes = array();
                    $found_product_tag_meta = $this->wt_get_found_product_tag_meta();
         
                    foreach ($found_product_tag_meta as $product_meta) {
                        $fields[$key]['fields']['meta:' . $product_meta] = 'meta:' . $product_meta;
                    }
                    break;
                
                default:
                    break;
            }
        }

        return $fields;
    }

    
    public function wt_get_product_tags() {

        if (!empty($this->product_taxonomies)) {
            return $this->product_taxonomies;
        }
        $product_ptaxonomies = get_object_taxonomies('product', 'name');
        $product_vtaxonomies = get_object_taxonomies('product_variation', 'name');
        $product_taxonomies = array_merge($product_ptaxonomies, $product_vtaxonomies);

        $this->product_taxonomies = $product_taxonomies;
        return $this->product_taxonomies;
    }
    
    
    public function importer_alter_meta_mapping_fields($fields, $base, $step_page_form_data) {
        if ($base != $this->module_base) {
            return $fields;
        }
        $fields=$this->exporter_alter_meta_mapping_fields($fields, $base, $step_page_form_data);
        $out=array();
        foreach ($fields as $key => $value) 
        {
            $value['fields']=array_map(function($vl){ return array('title'=>$vl, 'description'=>$vl); }, $value['fields']);
            $out[$key]=$value;
        }
        return $out;
    }

    public function wt_get_found_product_tag_meta() {

            if (!empty($this->found_product_tag_meta)) {
                return $this->found_product_tag_meta;
            }

            $term_args = array('taxonomy' => 'product_tag', 'hide_empty' => false);
            $terms = get_terms($term_args);

            $term_keys = array();
            $i = 0;
            foreach ($terms as $term) {
                $keys = get_term_meta($term->term_id);
                foreach ($keys as $key => $val) {
                    $term_keys[$i] = $key;
                    $i++;
                }
                
            }
            $tag_meta_keys = array_diff(array_unique($term_keys), array('product_count_product_tag', 'order'));

            $this->found_product_tag_meta = $tag_meta_keys;
            return $this->found_product_tag_meta;
    }


    public function set_selected_column_names($full_form_data) {

        if (is_null($this->selected_column_names)) {

            if (isset($full_form_data['mapping_form_data']['mapping_selected_fields']) && !empty($full_form_data['mapping_form_data']['mapping_selected_fields'])) {
                $this->selected_column_names = $full_form_data['mapping_form_data']['mapping_selected_fields'];
            }
            if (isset($full_form_data['meta_step_form_data']['mapping_selected_fields']) && !empty($full_form_data['meta_step_form_data']['mapping_selected_fields'])) {
                $export_additional_columns = $full_form_data['meta_step_form_data']['mapping_selected_fields'];
                foreach ($export_additional_columns as $value) {
                    $this->selected_column_names = array_merge($this->selected_column_names, $value);
                }
            }
        }

        return $full_form_data;
    }

    public function get_selected_column_names() {
        return $this->selected_column_names;
    }

    public function exporter_alter_mapping_fields($fields, $base, $mapping_form_data) {
        if ($base != $this->module_base) {
            return $fields;
        }

        $fields = self::get_tags_post_columns();

        return $fields;
    }


    /**
     *  Customize the items in filter export page
     */
    public function exporter_alter_filter_fields($fields, $base, $filter_form_data) {
        if ($this->module_base != $base) {
            return $fields;
        }

        $fields = array();


        $sort_columns = self::get_tags_sort_columns();
        $fields['sort_columns'] = array(
            'label' => __('Sort Columns', 'wt-import-export-for-woo'),
            'placeholder' => __('comment_ID'),
            'field_name' => 'sort_columns',
            'sele_vals' => $sort_columns,
            'help_text' => __('Sort the exported data based on the selected column in the order specified. Defaulted to ascending order.', 'wt-import-export-for-woo'),
            'type' => 'select',
        );
        
        $fields['order_by'] = array(
                'label' => __('Sort', 'wt-import-export-for-woo'),
                'placeholder' => __('ASC'),
                'field_name' => 'order_by',
                'sele_vals' => array('ASC' => 'Ascending', 'DESC' => 'Descending'),
                'help_text' => __('Defaulted to Ascending. Applicable to above selected columns in the order specified.', 'wt-import-export-for-woo'),
                'type' => 'select',
                'css_class' => '',
            );

        return $fields;
    }
    
    
    public function exporter_alter_advanced_fields($fields, $base, $advanced_form_data) {
        if ($this->module_base != $base) {
            return $fields;
        }
        unset($fields['export_shortcode_tohtml']);
        
        return $fields;
    }
    
    public function importer_alter_advanced_fields($fields, $base, $advanced_form_data) {
        if ($this->module_base != $base) {
            return $fields;
        }        
        $out = array();
        
        
        
        $out['merge'] = array(
            'label' => __("If the tag exists in the store", 'wt-import-export-for-woo'),
            'type' => 'radio',
            'radio_fields' => array(                
                '0' => __('Skip', 'wt-import-export-for-woo'),
                '1' => __('Update', 'wt-import-export-for-woo')
            ),
            'value' => '0',
            'field_name' => 'merge',
            'help_text' => __('Tags are matched by their ID/slugs.', 'wt-import-export-for-woo'),
            'help_text_conditional'=>array(
                array(
                    'help_text'=> __('Retains the tag in the store as is and skips the matching tag from the input file.', 'wt-import-export-for-woo'),
                    'condition'=>array(
                        array('field'=>'wt_iew_merge', 'value'=>0)
                    )
                ),
                array(
                    'help_text'=> __('Update tag as per data from the input file', 'wt-import-export-for-woo'),
                    'condition'=>array(
                        array('field'=>'wt_iew_merge', 'value'=>1)
                    )
                )
            ),
            'form_toggler'=>array(
                'type'=>'parent',
                'target'=>'wt_iew_found_action'
            )
        );
           
        
        foreach ($fields as $fieldk => $fieldv) {
            $out[$fieldk] = $fieldv;
        }
		unset( $out['enable_speed_mode'] );
        return $out;
    }
    
    public function get_item_by_id($id) {
        $post['edit_url']= get_edit_term_link($id);
        $post['title'] = @get_term($id)->name;
        return $post; 
    }
	public static function get_item_link_by_id($id) {
        $post['edit_url']= get_edit_term_link($id);
        $post['title'] = @get_term($id)->name;
        return $post; 
    }
}
}
new Wt_Import_Export_For_Woo_Product_Tags();
