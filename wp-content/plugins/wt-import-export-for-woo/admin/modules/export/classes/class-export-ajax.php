<?php
/**
 * Ajax section of the Export module
 *
 * @link        
 *
 * @package  Wt_Import_Export_For_Woo
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wt_Import_Export_For_Woo_Export_Ajax
{
	public $step='';
	public $steps=array();
	public $step_btns=array();
	public $export_method='';
	public $to_export='';
	public $cron_edit_id;
	public $module_base;

	protected $step_title='';
	protected $step_keys=array();
	protected $current_step_index=0;
	protected $current_step_number=1;
	protected $last_page=false;
	protected $total_steps=0;
	protected $step_summary='';
	protected $mapping_enabled_fields=array();
	protected $mapping_templates=array();
	protected $selected_template=0;
	protected $selected_template_form_data=array(); /* this variable is using to store form_data of selected template or selected history entry */
	protected $export_obj=null;
	protected $rerun_id=0;


	public function __construct($export_obj, $to_export, $steps, $export_method, $selected_template, $rerun_id, $cron_edit_id)
	{
		$this->export_obj=$export_obj;
		$this->to_export=$to_export;
		$this->steps=$steps;
		$this->export_method=$export_method;
		$this->selected_template=$selected_template;
		$this->rerun_id=$rerun_id;
		$this->cron_edit_id = $cron_edit_id;
	}

	/** 
	*	Ajax main function to retrive steps HTML 
	*/
	public function get_steps($out)
	{
		//sleep(3);
		$steps=(is_array($_POST['steps']) ? $_POST['steps'] : array($_POST['steps']));
		$steps=Wt_Iew_Sh_Pro::sanitize_item($steps, 'text_arr');
		$page_html=array();

		if($this->selected_template>0) /* taking selected tamplate form_data */
		{
			$this->get_template_form_data($this->selected_template);

		}elseif($this->rerun_id>0)
		{
			$this->selected_template_form_data=$this->export_obj->form_data;
		}

		foreach($steps as $step)
		{
			$method_name=$step.'_page';
			if(method_exists($this, $method_name))
			{
				$page_html[$step]=$this->{$method_name}();
				
				if($step=='method_export' && ($this->selected_template>0 || $this->rerun_id>0))
				{
					$out['template_data']=$this->selected_template_form_data;
				}
			}
		}
		$out['status']=1;
		$out['page_html']=$page_html;
		return $out;
	}

	/**
	* 	Ajax function to retrive meta step data
	*/
	public function get_meta_mapping_fields($out)
	{
		if($this->selected_template>0) /* taking selected tamplate form_data */
		{
			$this->get_template_form_data($this->selected_template);

		}elseif($this->rerun_id>0)
		{
			if(empty($this->export_obj->form_data) && 0 === $this->cron_edit_id){
			$history_module_obj=Wt_Import_Export_For_Woo::load_modules('history');
			if(!is_null($history_module_obj))
			{
                          
				/* check the history entry is for import and also has form_data */
				$history_data=$history_module_obj->get_history_entry_by_id($this->rerun_id);
				if($history_data && $history_data['template_type']==$this->module_base)
				{
					$form_data=maybe_unserialize($history_data['data']);
					if($form_data && is_array($form_data))
					{
						$this->selected_template_form_data= $form_data;
					}
				}
			}
			}else{
				$this->selected_template_form_data=$this->export_obj->form_data;
			}
		}
		if($this->cron_edit_id>0){

			//take cron data by edit id
			$cron_module_obj=Wt_Import_Export_For_Woo::load_modules('cron');

			if(!is_null($cron_module_obj))
			{
				/* check the cron entry is for import and also has form_data */
                $cron_data=$cron_module_obj->get_cron_by_id($this->cron_edit_id);
				if($cron_data)
				{
					$form_data=maybe_unserialize($cron_data['data']);
					if($form_data && is_array($form_data))
					{
						$this->selected_template_form_data=$form_data;
					}
				}
			}
		}		

		$this->get_mapping_enabled_fields();

		$meta_mapping_screen_fields=array();
		foreach($this->mapping_enabled_fields as $field_key=>$field_vl)
		{
			$field_vl=(!is_array($field_vl) ? array($field_vl, 0) : $field_vl);
			$meta_mapping_screen_fields[$field_key]=array(
				'title'=>'',
				'checked'=>$field_vl[1],
				'fields'=>array(),
			);
		}
		
		//taking current page form data
		$meta_step_form_data=(isset($this->selected_template_form_data['meta_step_form_data']) ? $this->selected_template_form_data['meta_step_form_data'] : array());

		/* form_data/template data of fields in mapping page */
		$form_data_meta_mapping_fields=isset($meta_step_form_data['mapping_fields']) ? $meta_step_form_data['mapping_fields'] : array();


		$meta_mapping_screen_fields=apply_filters('wt_iew_exporter_alter_meta_mapping_fields', $meta_mapping_screen_fields, $this->to_export, $form_data_meta_mapping_fields);
		
		if(isset($meta_mapping_screen_fields['meta']['fields'])){
			if(count($meta_mapping_screen_fields['meta']['fields']) > 700){
				$large_meta_columns = true;
			}

			if(isset($form_data_meta_mapping_fields['meta'])){
				$template_meta_order = array();
				foreach ($form_data_meta_mapping_fields['meta'] as $template_meta_key =>  $template_meta_options ) {
					$template_meta_order[$template_meta_key] = $template_meta_key;
				}
				foreach(array_diff_key($meta_mapping_screen_fields['meta']['fields'], $template_meta_order) as $add_meta){
					$form_data_meta_mapping_fields['meta'][$add_meta] = array($add_meta,0);

				}
				$meta_mapping_screen_fields['meta']['fields'] = array_merge($template_meta_order,$meta_mapping_screen_fields['meta']['fields']) ;
			}
			if(isset($form_data_meta_mapping_fields['hidden_meta'])){	
				$template_hidden_meta_order = array();
				foreach ($form_data_meta_mapping_fields['hidden_meta'] as $template_hidden_meta_key =>  $template_hidden_meta_options ) {
					$template_hidden_meta_order[$template_hidden_meta_key] = $template_hidden_meta_key;
				}
				$meta_mapping_screen_fields['hidden_meta']['fields'] = array_merge($template_hidden_meta_order,$meta_mapping_screen_fields['hidden_meta']['fields']);
			}
			if(isset($form_data_meta_mapping_fields['attributes'])){
				$template_attributes_order = array();
				foreach ($form_data_meta_mapping_fields['attributes'] as $template_attributes_key =>  $template_attributes_options ) {
					$template_attributes_order[$template_attributes_key] = $template_attributes_key;
				}
				$meta_mapping_screen_fields['attributes']['fields'] = $template_attributes_order;
			}
			if(isset($form_data_meta_mapping_fields['taxonomies'])){
				$template_taxonomies_order = array();
				foreach ($form_data_meta_mapping_fields['taxonomies'] as $template_taxonomies_key =>  $template_taxonomies_options ) {
					$template_taxonomies_order[$template_taxonomies_key] = $template_taxonomies_key;
				}
				$meta_mapping_screen_fields['taxonomies']['fields'] = $template_taxonomies_order;
				
			}
		}
		
		$draggable_tooltip=__("Drag to rearrange the columns");
		$module_url=plugin_dir_url(dirname(__FILE__));

		$meta_html=array();
		if($meta_mapping_screen_fields && is_array($meta_mapping_screen_fields))
		{
			/* loop through mapping fields */
			foreach($meta_mapping_screen_fields as $meta_mapping_screen_field_key=>$meta_mapping_screen_field_val)
			{	
				$current_meta_step_form_data=(isset($form_data_meta_mapping_fields[$meta_mapping_screen_field_key]) ? $form_data_meta_mapping_fields[$meta_mapping_screen_field_key] : array());
				ob_start();			
				include dirname(plugin_dir_path(__FILE__)).'/views/_export_meta_step_page.php';
				$meta_html[$meta_mapping_screen_field_key]=ob_get_clean();
			}
		}
		
		foreach ( $meta_html as $key => $value ) {
			$meta_html[$key] = Wt_Iew_IE_Helper::sanitize_and_minify_html($value);
		}

		$out['status']=1;
		$out['meta_html']=$meta_html;
		return $out;
	}

	public function save_template($out)
	{
		return $this->do_save_template('save', $out);
	}

	public function save_template_as($out)
	{
		return $this->do_save_template('save_as', $out);
	}

	public function update_template($out)
	{
		return $this->do_save_template('update', $out);
	}

	/**
	* 	Ajax hook to upload the exported file.
	*
	*/
	public function upload($out)
	{
		$export_id=(isset($_POST['export_id']) ? intval($_POST['export_id']) : 0);
		$exported_to = array(
			'export_to_email' => (isset($_POST['export_to_email'])) ? sanitize_email($_POST['export_to_email']) : 'No',
			'email_sent' => (isset($_POST['email_sent'])) ? sanitize_text_field($_POST['email_sent']) : 'No',
		);
		$out=$this->export_obj->process_upload('upload', $export_id, $this->to_export,$exported_to);
		
		if($out['response']===true)
		{			
			$out['status']=1;
		}else
		{
			$out['status']=0;
		}
		return $out;
	}

	/**
	* Process the image export
	*
	* @return array 
	*/
	public function export_image($out)
	{
		$offset=(isset($_POST['offset']) ? intval($_POST['offset']) : 0);
		$export_id=(isset($_POST['export_id']) ? intval($_POST['export_id']) : 0);

		if($export_id==0) /* first batch */
		{
			/* process form data */
			$form_data=(isset($_POST['form_data']) ? Wt_Import_Export_For_Woo_Common_Helper::process_formdata(maybe_unserialize(($_POST['form_data']))) : array());

			//sanitize form data
			$form_data=Wt_Iew_IE_Helper::sanitize_formdata($form_data, $this->export_obj);
		}else
		{
			/* no need to send form_data. It will take from history table by `process_image_export` method */
			$form_data=array();
		}
		
		/* do the image export process */
		$out=$this->export_obj->process_image_export($form_data, 'export_image', $this->to_export, '', $export_id, $offset);
		if($out['response']===true)
		{			
			$out['status']=1;
		}else
		{
			$out['status']=0;
		}
		return $out;
	}

	/**
	* Process the export data
	*
	* @return array 
	*/
	public function export($out)
	{
		$offset=(isset($_POST['offset']) ? intval($_POST['offset']) : 0);
		$export_id=(isset($_POST['export_id']) ? intval($_POST['export_id']) : 0);
		$file_name='';

		if($export_id==0) /* first batch */
		{
			/* process form data */
			$form_data=(isset($_POST['form_data']) ? Wt_Import_Export_For_Woo_Common_Helper::process_formdata(maybe_unserialize(($_POST['form_data']))) : array());

			//sanitize form data
			$form_data=Wt_Iew_IE_Helper::sanitize_formdata($form_data, $this->export_obj);

			/* taking file name from user input. (If given) */
			if(isset($form_data['advanced_form_data']) && isset($form_data['advanced_form_data']['wt_iew_file_name']) && $form_data['advanced_form_data']['wt_iew_file_name']!="")
			{
				$file_name=$form_data['advanced_form_data']['wt_iew_file_name'];
			}

		}else
		{
			/* no need to send form_data. It will take from history table by `process_action` method */
			$form_data=array();
		}
		
		/* do the export process */
		$out=$this->export_obj->process_action($form_data, 'export', $this->to_export, $file_name, $export_id, $offset);
		if($out['response']===true)
		{			
			$out['status']=1;
		}else
		{
			$out['status']=0;
		}
		return $out;
	}

	/**
	* Save/Update template (Ajax sub function)
	* @param boolean $is_update is update existing template or save as new
	* @return array response status, name, id
	*/
	public function do_save_template($step, $out)
	{
		$is_update=($step=='update' ? true : false);

		/* take template name from post data, if not then create from time stamp */
		$template_name=(isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : date('d-M-Y h:i:s A'));
		
		$template_name = stripslashes($template_name);
		$out['name']= $template_name;
		$out['id']=0;
		$out['status']=1;

		if($this->to_export!='')
		{
			global $wpdb;

			/* checking: just saved and again click the button so shift the action as update */
			if($step=='save' && $this->selected_template>0) 
			{
				$is_update=true;
			}

			/* checking template with same name exists */
			$template_data=$this->get_mapping_template_by_name($template_name);
			if($template_data)
			{
				$is_throw_warn=false;
				if($is_update)
				{
					if($template_data['id']!=$this->selected_template)
					{
						$is_throw_warn=true;
					}	
				}else
				{
					$is_throw_warn=true;
				}

				if($is_throw_warn)
				{
					$out['status']=0;
					if($step=='save_as')
					{
						$out['msg']=__('Please enter a different name');
					}else
					{
						$out['msg']=__('Template with same name already exists');	
					}
					return $out;
				}		
			}			

			$tb=$wpdb->prefix.Wt_Import_Export_For_Woo::$template_tb;
			
			/* process form data */
			$form_data=(isset($_POST['form_data']) ? Wt_Import_Export_For_Woo_Common_Helper::process_formdata(maybe_unserialize(($_POST['form_data']))) : array());

			//sanitize form data
			$form_data=Wt_Iew_IE_Helper::sanitize_formdata($form_data, $this->export_obj);

			/* upadte the template */
			if($is_update)
			{ 
							
				$update_data=array(
					'data'=>maybe_serialize($form_data),
					'name'=>$template_name, //may be a rename
				);
				$update_data_type=array(
					'%s',
					'%s'
				);
				$update_where=array(
					'id'=>$this->selected_template
				);
				$update_where_type=array(
					'%d'
				);
				if($wpdb->update($tb, $update_data, $update_where, $update_data_type, $update_where_type)!==false)
				{
					$out['id']=$this->selected_template;
					$out['name']=$template_name;
					$out['msg']=__('Template updated successfully');
					return $out;
				}
			}else
			{
				$insert_data=array(
					'template_type'=>'export',
					'item_type'=>$this->to_export,
					'name'=>$template_name,
					'data'=>maybe_serialize($form_data),
				);
				$insert_data_type=array(
					'%s','%s','%s','%s'
				);
				if($wpdb->insert($tb, $insert_data, $insert_data_type)) //success
				{
					$out['id']=$wpdb->insert_id;
					$out['msg']=__('Template saved successfully');
					return $out;
				}
			}
		}
		$out['status']=0;
		return $out;
	}

	/**
	*  Step 1 (Ajax sub function)
	*  Built in steps, post type choosing page
	*/
	public function post_type_page()
	{
		$post_types=apply_filters('wt_iew_exporter_post_types', array());
		$post_types=(!is_array($post_types) ? array() : $post_types);
		$this->step='post_type';
		$step_info=$this->steps[$this->step];
		$item_type=$this->to_export;
		
		$this->prepare_step_summary();
		$this->prepare_footer_button_list();

		ob_start();		
		$this->prepare_step_header_html();
		include_once dirname(plugin_dir_path(__FILE__)).'/views/_export_post_type_page.php';
		$this->prepare_step_footer_html();
		return ob_get_clean();
	}

	/**
	*  Step 2 (Ajax sub function)
	* Built in steps, export method choosing page
	*/
	public function method_export_page()
	{
		$this->step='method_export';
		$step_info=$this->steps[$this->step];
		if($this->to_export!="")
		{
			/* setting a default export method */
			$this->export_method=($this->export_method=='' ? $this->export_obj->default_export_method : $this->export_method);
			$this->export_obj->export_method=$this->export_method;
			$this->steps=$this->export_obj->get_steps();

			$form_data_export_template=$this->selected_template;
			$form_data_mapping_enabled=array();
			if($this->rerun_id>0)
			{
				if(isset($this->selected_template_form_data['method_export_form_data']))
				{
					if(isset($this->selected_template_form_data['method_export_form_data']['selected_template']))
					{
						/* do not set this value to `$this->selected_template` */
						$form_data_export_template=$this->selected_template_form_data['method_export_form_data']['selected_template'];
					}
					if(isset($this->selected_template_form_data['method_export_form_data']['mapping_enabled_fields']))
					{
						$form_data_mapping_enabled=$this->selected_template_form_data['method_export_form_data']['mapping_enabled_fields'];
						$form_data_mapping_enabled=(is_array($form_data_mapping_enabled) ? $form_data_mapping_enabled : array());
					}
				}
			}

			$this->prepare_step_summary();
			$this->prepare_footer_button_list();

			/* meta field list for quick export */
			$this->get_mapping_enabled_fields();

			/* template list for template export */
			$this->get_mapping_templates();

			ob_start();		
			$this->prepare_step_header_html();
			include_once dirname(plugin_dir_path(__FILE__)).'/views/_export_method_export_page.php';
			$this->prepare_step_footer_html();
			return ob_get_clean();
		}else
		{
			return '';
		}
	}

	/*
	 * Get step information
	 * @param string $step
	 */

	public function get_step_info( $step ) {
		return isset( $this->steps[ $step ] ) ? $this->steps[ $step ] : array( 'title' => ' ', 'description' => ' ' );
	}

	/**
	*  	Step 3 (Ajax sub function)
	* 	Built in steps, filter page
	*/
	public function filter_page()
	{
		$this->step='filter';
		$step_info= $this->get_step_info($this->step);
		if($this->to_export!='')
		{

			$this->prepare_step_summary();
			$this->prepare_footer_button_list();

			//taking current page form data
			$filter_form_data=(isset($this->selected_template_form_data['filter_form_data']) ? $this->selected_template_form_data['filter_form_data'] : array());

                        if (isset($_REQUEST['rerun_id']) && absint($_REQUEST['rerun_id']) > 0) {
                            $requested_cron_edit_id = (isset($_REQUEST['rerun_id']) ? absint($_REQUEST['rerun_id']) : 0);
                            $cron_module_obj = Wt_Import_Export_For_Woo::load_modules('cron');
                            if (!is_null($cron_module_obj)) {
                                /* check the cron entry is for export and also has form_data */
                                $cron_data = $cron_module_obj->get_cron_by_id($requested_cron_edit_id);
                                $form_data=maybe_unserialize($cron_data['data']);
                                $filter_form_data = $form_data['filter_form_data'];
                            }
                        }
                        if(empty($filter_form_data)){ // check if a re-run request from history entry
                        if (isset($_REQUEST['rerun_id']) && absint($_REQUEST['rerun_id']) > 0) {
                            $requested_rerun_id = (isset($_REQUEST['rerun_id']) ? absint($_REQUEST['rerun_id']) : 0);
                            $history_module_obj = Wt_Import_Export_For_Woo::load_modules('history');
                            if (!is_null($history_module_obj)) {
                                /* check the cron entry is for export and also has form_data */
                                $history_data = $history_module_obj->get_history_entry_by_id($requested_rerun_id);
                                $form_data=maybe_unserialize($history_data['data']);
                                $filter_form_data = $form_data['filter_form_data'];

                            }
                        }
                        }

			$filter_screen_fields=$this->export_obj->get_filter_screen_fields($filter_form_data);

			ob_start();		
			$this->prepare_step_header_html();
			include_once dirname(plugin_dir_path(__FILE__)).'/views/_export_filter_page.php';
			$this->prepare_step_footer_html();
			return ob_get_clean();

		}else
		{
			return '';
		}
	}


	/**
	* Step 4 (Ajax sub function)
	* Built in steps, mapping page
	*/
	public function mapping_page()
	{	
		$this->step='mapping';
		$step_info= $this->get_step_info($this->step);
		if($this->to_export!='')
		{

			$this->prepare_step_summary();
			$this->prepare_footer_button_list();

			//taking current page form data
			$mapping_form_data=(isset($this->selected_template_form_data['mapping_form_data']) ? $this->selected_template_form_data['mapping_form_data'] : array());
		
                        if (isset($_REQUEST['rerun_id']) && absint($_REQUEST['rerun_id']) > 0) {
                            $requested_cron_edit_id = (isset($_REQUEST['rerun_id']) ? absint($_REQUEST['rerun_id']) : 0);
                            $cron_module_obj = Wt_Import_Export_For_Woo::load_modules('cron');
                            if (!is_null($cron_module_obj)) {
                                /* check the cron entry is for export and also has form_data */
                                $cron_data = $cron_module_obj->get_cron_by_id($requested_cron_edit_id);
                                $form_data=maybe_unserialize($cron_data['data']);
                                $mapping_form_data = $form_data['mapping_form_data'];
                            }
                        }
                        
                        
			/* form_data/template data of fields in mapping page */
			$form_data_mapping_fields=isset($mapping_form_data['mapping_fields']) ? $mapping_form_data['mapping_fields'] : array();
			
			/* default mapping page fields */
			$mapping_fields=array();
			$mapping_fields=apply_filters('wt_iew_exporter_alter_mapping_fields', $mapping_fields, $this->to_export, $form_data_mapping_fields);

			
			/* meta fields list */
			$this->get_mapping_enabled_fields();

			/* mapping enabled meta fields */
			$form_data_mapping_enabled_fields=(isset($mapping_form_data['mapping_enabled_fields']) ? $mapping_form_data['mapping_enabled_fields'] : array());
										
			ob_start();		
			$this->prepare_step_header_html();
			include_once dirname(plugin_dir_path(__FILE__)).'/views/_export_mapping_page.php';
			$this->prepare_step_footer_html();
			return ob_get_clean();
		}else
		{
			return '';
		}
	}

	/**
	* Step 5 (Ajax sub function)
	* Built in steps, advanced page
	*/
	public function advanced_page()
	{
		$this->step='advanced';
		$step_info= $this->get_step_info($this->step);
		if($this->to_export!='')
		{

			$this->prepare_step_summary();
			$this->prepare_footer_button_list();

			//taking current page form data
			$advanced_form_data=(isset($this->selected_template_form_data['advanced_form_data']) ? $this->selected_template_form_data['advanced_form_data'] : array());

                         if (isset($_REQUEST['rerun_id']) && absint($_REQUEST['rerun_id']) > 0) {
                            $requested_cron_edit_id = (isset($_REQUEST['rerun_id']) ? absint($_REQUEST['rerun_id']) : 0);
                            $cron_module_obj = Wt_Import_Export_For_Woo::load_modules('cron');
                            if (!is_null($cron_module_obj)) {
                                /* check the cron entry is for export and also has form_data */
                                $cron_data = $cron_module_obj->get_cron_by_id($requested_cron_edit_id);
                                $form_data=maybe_unserialize($cron_data['data']);
                                $advanced_form_data = $form_data['advanced_form_data'];
                            }
                        }
                        
			$advanced_screen_fields=$this->export_obj->get_advanced_screen_fields($advanced_form_data);
			
			ob_start();
			$this->prepare_step_header_html();
			include_once dirname(plugin_dir_path(__FILE__)).'/views/_export_advanced_page.php';
			$this->prepare_step_footer_html();
			return ob_get_clean();

		}else
		{
			return '';
		}		
	}

	/**
	* Get template form data
	*/
	protected function get_template_form_data($id)
	{
		$template_data=$this->get_mapping_template_by_id($id);
		if($template_data)
		{
			$decoded_form_data=Wt_Import_Export_For_Woo_Common_Helper::process_formdata(maybe_unserialize($template_data['data']));
			$this->selected_template_form_data=(!is_array($decoded_form_data) ? array() : $decoded_form_data);
		}
	}

	/**
	* Taking mapping template by Name
	*/
	protected function get_mapping_template_by_name($name)
	{
		global $wpdb;
		$tb=$wpdb->prefix.Wt_Import_Export_For_Woo::$template_tb;
		$qry=$wpdb->prepare("SELECT * FROM $tb WHERE template_type=%s AND item_type=%s AND name=%s",array('export', $this->to_export, $name));
		return $wpdb->get_row($qry, ARRAY_A);
	}

	/**
	* Taking mapping template by ID
	*/
	protected function get_mapping_template_by_id($id)
	{
		global $wpdb;
		$tb=$wpdb->prefix.Wt_Import_Export_For_Woo::$template_tb;
		$qry=$wpdb->prepare("SELECT * FROM $tb WHERE template_type=%s AND item_type=%s AND id=%d",array('export', $this->to_export, $id));
		return $wpdb->get_row($qry, ARRAY_A);
	}

	/**
	* Taking all mapping templates
	*/
	protected function get_mapping_templates()
	{
		if($this->to_export=='')
		{
			return;
		}		
		global $wpdb;
		$tb=$wpdb->prefix.Wt_Import_Export_For_Woo::$template_tb;
		$val=$wpdb->get_results("SELECT * FROM $tb WHERE template_type='export' AND item_type='".$this->to_export."' ORDER BY id DESC", ARRAY_A);	

		//add a filter here for modules to alter the data
		$this->mapping_templates=($val ? $val : array());
	}

	/**
	* Get meta field list for mapping page
	*
	*/
	protected function get_mapping_enabled_fields()
	{
		$mapping_enabled_fields=array(
			'hidden_meta'=>array(__('Hidden meta'),0),
			'meta'=>array(__('Meta'),1),
		);
		$this->mapping_enabled_fields=apply_filters('wt_iew_exporter_alter_mapping_enabled_fields', $mapping_enabled_fields, $this->to_export, array());
	}

	protected function prepare_step_footer_html()
	{		
		include dirname(plugin_dir_path(__FILE__)).'/views/_export_footer.php';
	}

	protected function prepare_step_summary()
	{
		$step_info= $this->get_step_info($this->step);
		$this->step_title=$step_info['title'];
		$this->step_keys=array_keys($this->steps);
		$this->current_step_index=array_search($this->step, $this->step_keys);
		$this->current_step_number=$this->current_step_index+1;
		$this->last_page=(!isset($this->step_keys[$this->current_step_index+1]) ? true : false);
		$this->total_steps=count($this->step_keys);
		$this->step_summary=sprintf(__("Step %d of %d", 'wt-import-export-for-woo'), $this->current_step_number, $this->total_steps);
	}

	protected function prepare_step_header_html()
	{	
		include dirname(plugin_dir_path(__FILE__)).'/views/_export_header.php';
	}

	protected function prepare_footer_button_list()
	{
		$out=array();
		$step_keys=$this->step_keys;
		$current_index=$this->current_step_index;
		$last_page=$this->last_page;
		if($current_index!==false) /* step exists */
		{
			if($current_index>0) //add back button
			{
				$out['back']=array(
					'type'=>'button',
					'action_type'=>'step',
					'key'=>$step_keys[$current_index-1],
					'text'=>'<span class="dashicons dashicons-arrow-left-alt2" style="line-height:27px;"></span> '.__('Back', 'wt-import-export-for-woo'),
				);
			}
			
			if(isset($step_keys[$current_index+1])) /* not last step */
			{
				$next_number=$current_index+2;
				$next_key=$step_keys[$current_index+1];
				$next_title= __($this->steps[$next_key]['title'], 'wt-import-export-for-woo');
				$out['next']=array(
					'type'=>'button',
					'action_type'=>'step',
					'key'=>$next_key,
					'text'=>__('Step', 'wt-import-export-for-woo').' '.$next_number.': '.$next_title.' <span class="dashicons dashicons-arrow-right-alt2" style="line-height:27px;"></span>',
				);

				if($this->export_method=='quick' || $this->export_method=='template') //Quick Or Template method
				{
					$out['or']=array(
						'type'=>'text',
						'text'=>__('Or', 'wt-import-export-for-woo'),
					);
				}

			}else
			{
				$last_page=true;
			}

			if($this->export_method=='quick' || $this->export_method=='template' || $last_page) //template method, or last page, or quick export
			{
				if($last_page && $this->export_method!='quick') //last page and not quick export
				{
					if($this->export_method=='template')
					{
						$out['save']=array(
							'key'=>'save',
							'icon'=>'',
							'type'=>'dropdown_button',
							'text'=>__('Save template', 'wt-import-export-for-woo'), 
							'items'=>array(
								'update'=>array(
									'key'=>'update_template',
									'text'=>__('Save', 'wt-import-export-for-woo'),  //no prompt
								),
								'save'=>array(
									'key'=>'save_template_as',
									'text'=>__('Save As', 'wt-import-export-for-woo'), //prompt for name
								)
							)
						);
					}else
					{
						$out['save']=array(
							'key'=>'save_template',
							'icon'=>'',
							'type'=>'button',
							'text'=>__('Save template', 'wt-import-export-for-woo'), //prompt for name
						);
					}
				}
				
				if($last_page)
				{
					$out['export_image']=array(
						'key'=>'export_image',
						'class'=>'iew_export_image_btn',
						'icon'=>'',
						'type'=>'button',
						'text'=>__('Export images', 'wt-import-export-for-woo'),
					);
				}

				$out['export']=array(
					'key'=>'export',
					'class'=>'iew_export_btn',
					'icon'=>'',
					'type'=>'button',
					'text'=>__('Export', 'wt-import-export-for-woo'),
				);
			}
		}		
		$this->step_btns=apply_filters('wt_iew_exporter_alter_footer_btns', $out, $this->step, $this->steps);
	}
	
	public function get_order_billing_values(){
		
		$key = sanitize_text_field( $_POST['item'] );
		$type = '_billing_';
		return Wt_Import_Export_For_Woo_Order::get_order_meta_values($type, $key);
	}
	
	public function get_order_shipping_values(){
		
		$key = sanitize_text_field( $_POST['item'] );
		$type = '_shipping_';
		return Wt_Import_Export_For_Woo_Order::get_order_meta_values($type, $key);
	}	
}