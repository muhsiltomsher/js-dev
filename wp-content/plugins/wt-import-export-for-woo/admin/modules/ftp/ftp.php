<?php
/**
 * Ftp profile section of the plugin
 *
 * @link          
 *
 * @package  Wt_Import_Export_For_Woo 
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wt_Import_Export_For_Woo_Ftp
{
	private $to_export='';
	private $to_export_id='';
	public $module_id='';
	public static $module_id_static='';
	public $module_base='ftp';	
	public $popup_page=0; //is ajax call from popup. May be it from export/import page
	public $lables=array(); //labels for translation
	public $ftp_form_fields = array();
	public $ftp_form_validation_rule = array();	
	public function __construct()
	{
		$this->module_id=Wt_Import_Export_For_Woo::get_module_id($this->module_base);
		self::$module_id_static=$this->module_id;

		add_filter('wt_iew_plugin_settings_tabhead',array( __CLASS__,'settings_tabhead'));
		add_action('wt_iew_plugin_out_settings_form',array($this,'out_settings_form'));

		add_filter('wt_iew_exporter_alter_advanced_fields',array($this,'exporter_alter_advanced_fields'),10,3);
		add_filter('wt_iew_importer_alter_method_import_fields', array($this, 'importer_alter_method_import_fields'), 10, 3);

		/* Ajax hook to save FTP details */
		add_action('wp_ajax_iew_ftp_ajax',array($this,'ajax_main'),11);

		/* Add FTP adapter to remoter adapter list */
		add_filter('wt_iew_remote_adapters',array( $this,'remote_adapter'),11,3);
		add_filter('wt_iew_exporter_remote_adapter_names',array( $this,'remote_adapter_name'));
		add_filter('wt_iew_importer_remote_adapter_names',array( $this,'remote_adapter_name'));
		
		add_filter('wt_iew_exporter_file_into_fields_row_id',array( $this,'exporter_file_into_fields_row_id'));
		add_action('wt_iew_exporter_file_into_js_fn',array( $this,'exporter_file_into_js_fn'));
		add_action('wt_iew_importer_file_from_js_fn',array( $this,'importer_file_from_js_fn'));

		add_action('admin_enqueue_scripts', array($this,'enqueue_assets'),10,1);
		add_action('wt_iew_exporter_before_head', array($this,'add_popup_crud_html'),10,1);
		add_action('wt_iew_importer_before_head', array($this,'add_popup_crud_html'),10,1);
		
		/* validate ftp entries before doing an action */
		add_action('wt_iew_exporter_validate', array($this,'exporter_validate'));
		
		/* reset the formdata. Needed when user changes the import method */
		add_action('wt_iew_importer_reset_form_data', array($this,'importer_reset_form_data'));

		/* validate ftp entries before doing an action */
		add_action('wt_iew_importer_validate', array($this,'importer_validate'),10,1);
		
		/* set the validated file info to varaiable. This for revalidating if any changes ocuured */
		add_action('wt_iew_importer_set_validate_file_info', array($this, 'importer_set_validate_file_info'), 10, 1);

		//labels using in multiple places
		$this->lables['select_one']=__('Select atleast one.');
		$this->lables['no_ftp_prfile_found']=__('No FTP profiles found.');

		/* When altering fields and validation rule, please check `save_ftp` method */
		$this->ftp_form_fields=array('wt_iew_profilename', 'wt_iew_hostname', 'wt_iew_ftpuser', 'wt_iew_ftppassword', 'wt_iew_ftpport', 'wt_iew_ftpexport_path', 'wt_iew_ftpimport_path', 'wt_iew_is_sftp', 'wt_iew_useftps', 'wt_iew_passivemode');
		$this->ftp_form_validation_rule=array('wt_iew_ftpport'=>array('type'=>'absint'), 'wt_iew_is_sftp'=>array('type'=>'int'), 'wt_iew_useftps'=>array('type'=>'int'), 'wt_iew_passivemode'=>array('type'=>'int'));
	}

	public function enqueue_assets()
	{
		if(isset($_GET['page']) && ($_GET['page']==Wt_Import_Export_For_Woo::get_module_id('export') || $_GET['page']==Wt_Import_Export_For_Woo::get_module_id('import') || $_GET['page']==WT_IEW_PLUGIN_ID))
		{
			wp_enqueue_script($this->module_id, plugin_dir_url( __FILE__ ).'assets/js/main.js',array('jquery'), WT_IEW_VERSION);
			$params=array(
				'nonces' => array(
		            'main'=>wp_create_nonce($this->module_id),
		        ),
		        'ajax_url' => admin_url('admin-ajax.php'),
		        'msgs'=>array(
		        	'add_new'=>__('Add new', 'wt-import-export-for-woo'),
		        	'add_new_hd'=>__('Add new FTP profile', 'wt-import-export-for-woo'),
		        	'edit'=>__('Edit', 'wt-import-export-for-woo'),
		        	'edit_hd'=>__('Edit FTP profile', 'wt-import-export-for-woo'),
		        	'mandatory'=>__('All fields are mandatory', 'wt-import-export-for-woo'),
		        	'sure'=>__('Confirm? All import/export profiles associated with this FTP profile will not work. You can\'t undo this action.', 'wt-import-export-for-woo'),
		        	'wait'=>__('Please wait...', 'wt-import-export-for-woo'),
		        	'delete'=>__('Delete', 'wt-import-export-for-woo'),
		        	'aborted'=>__('Aborted', 'wt-import-export-for-woo'),
		        	'some_mandatory'=>__('Please fill mandatory fields', 'wt-import-export-for-woo'),
		        	'choose_a_profile'=>__('Please choose an FTP profile', 'wt-import-export-for-woo'),
		        	'enter_an_export_path'=>__('Export path is mandatory.', 'wt-import-export-for-woo'),
		        	'enter_an_import_path'=>__('Import path is mandatory.', 'wt-import-export-for-woo'),
		        	'enter_an_import_file'=>__('Import file is mandatory.', 'wt-import-export-for-woo'),
		        	'select_one'=>$this->lables['select_one'],
		        	'no_ftp_prfile_found'=>$this->lables['no_ftp_prfile_found'],
		        )
			);
			wp_localize_script($this->module_id, 'wt_iew_ftp_params', $params);

			wp_enqueue_style($this->module_id, plugin_dir_url( __FILE__ ).'assets/css/main.css', array(), WT_IEW_VERSION, 'all');
		}
	}

	/**
	*	Add HTML for FTP popup
	*/
	public function add_popup_crud_html()
	{
		?>
		<div class="wt_iew_popup_ftp_crud wt_iew_popup">
			<div class="wt_iew_popup_hd">
				<span class="wt_iew_popup_hd_label"><?php _e('FTP profiles');?></span>
				<div class="wt_iew_popup_close">X</div>
			</div>
			<div class="wt_iew_ftp_settings_page" style="padding:15px; text-align:left;">
				
			</div>
		</div>
		<?php	
	}

	public function importer_set_validate_file_info()
	{
		?>
		wt_iew_ftp.importer_set_validate_file_info(file_from);
		<?php
	}

	/**
	* 	Reset formdata of import
	*/
	public function importer_reset_form_data()
	{
		?>
		wt_iew_ftp.importer_reset_form_data();
		<?php
	}

	public function importer_validate()
	{
		?>
		if(is_continue)
		{
			is_continue=wt_iew_ftp.validate_import_ftp_fields(is_continue, action, action_type, is_previous_step);
		}
		<?php
	}


	public function exporter_validate()
	{
		?>
		if(is_continue)
		{
			is_continue=wt_iew_ftp.validate_export_ftp_fields(is_continue, action, action_type, is_previous_step);
		}
		<?php
	}

	public function importer_file_from_js_fn()
	{
		?>
		if(file_from=='ftp')
		{
			wt_iew_ftp.toggle_ftp_path();
			wt_iew_ftp.popUpCrud('import');
		}
		<?php
	}

	/**
	*	JS code to toggle FTP form fields 
	*/
	public function exporter_file_into_js_fn()
	{
		?>
		if(file_into=='ftp')
		{
			wt_iew_ftp.toggle_ftp_path();
			wt_iew_toggle_schedule_btn(1); /* show cron btn, if exists */
			wt_iew_ftp.popUpCrud('export');
		}
		<?php
	}

	public function remote_adapter_name($adapters)
	{
		$adapters['ftp']=__('FTP');
		return $adapters;
	}

	/**
	* 	Add FTP adapter to remoter adapter list
	*/
	public function remote_adapter($adapters, $action, $adapter)
	{
		if($adapter != "")
		{
			if('ftp' == $adapter)
			{
				$adapters['ftp']= include_once plugin_dir_path(__FILE__).'classes/class-ftpadapter.php';
			}
		}else
		{
			$adapters['ftp']=include_once plugin_dir_path(__FILE__).'classes/class-ftpadapter.php';
		}		
		return $adapters;
	}

	/**
	* 	Tab head for module settings page
	*/
	public static function settings_tabhead($arr)
	{
		$out=array();
		foreach ($arr as $key => $value)
		{
			$out[$key]=$value;
			if($key=='wt-advanced')
			{
				$out['wt-ftp']=__('FTP settings');
			}
		}
		if(!isset($out['wt-ftp'])) /* not found wt-advanced tab */
		{
			$out['wt-ftp']=__('FTP settings');
		}
		return $out;
	}

	/**
	* Main ajax hook for ajax actions. 
	*/
	public function ajax_main()
	{
		$allowed_actions=array('save_ftp', 'delete_ftp', 'ftp_list', 'settings_page', 'test_ftp');
		$action=(isset($_POST['iew_ftp_action']) ? sanitize_text_field($_POST['iew_ftp_action']) : '');
		$out=array('status'=>true, 'msg'=>'');
		if(!Wt_Iew_Sh_Pro::check_write_access(WT_IEW_PLUGIN_ID))
		{
			$out['status']=false;

		}else
		{
			if(in_array($action,$allowed_actions))
			{
				if(method_exists($this,$action))
				{
					$out=$this->{$action}($out); //some methods will not retrive array
				}
			}
		}
		echo json_encode($out);
		exit();	
	}

	public function exporter_file_into_fields_row_id($arr)
	{
		$arr=(is_array($arr) ? $arr : array());
		return array_merge($arr, array('#export_type_tr', '#cron_start_time_tr', '#cron_interval_tr', '#ftp_profile_tr', '#export_path_tr'));
	}

	/**
	* Add FTP related fields to the importer method_import step
	*
	*/
	public function importer_alter_method_import_fields($fields, $to_import, $form_data)
	{
		$out=array();
		foreach($fields as $fieldk=>$fieldv)
		{
			$out[$fieldk]=$fieldv;
			if($fieldk=='file_from')
			{
				$label_ftp_profiles=__("View/Add FTP profiles", 'wt-import-export-for-woo');
				$label_add_ftp_profile=__("Add new FTP profile", 'wt-import-export-for-woo');
				$ftp_list=$this->get_ftp_profile_for_select('import');
				$out['ftp_profile']=array(
					'label'=>__("Select an FTP profile", 'wt-import-export-for-woo'),
					'type'=>'select',
					'tr_id'=>'ftp_profile_tr',
					'tr_class'=>$fieldv['tr_class'], //add tr class from parent.Because we need to toggle the tr when parent tr toggles.
					'sele_vals'=>$ftp_list,
					'field_name'=>'ftp_profile',
					'form_toggler'=>array(
						'type'=>'child',
						'id'=>'wt_iew_file_from',
						'val'=>'ftp',
					),
					'validation_rule'=>array('type'=>'int'),
					'after_form_field_html'=>'<a class="wt_iew_ftp_profiles" data-label-ftp-profiles="'.$label_ftp_profiles.'" data-label-add-ftp-profile="'.$label_add_ftp_profile.'" data-tab="'.(count($ftp_list)>1 ? 'ftp-profiles' : 'add-new-ftp').'">'.(count($ftp_list)>1 ? $label_ftp_profiles : $label_add_ftp_profile).'</a>',
				);
				$out['use_default_path']=array(
					'label'=>__("Import file path", 'wt-import-export-for-woo'),
					'type'=>'radio',
					'value'=>'Yes',
					'radio_fields'=>array(
						'Yes'=>__('Default'),
						'No'=>__('Custom')
					),
					'tr_id'=>'use_default_path_tr',
					'field_name'=>'use_default_path',
					'form_toggler'=>array(
						'type'=>'child',
						'id'=>'wt_iew_file_from',
						'val'=>'ftp',
					),
					'help_text'=>__('Use import path from FTP profile.', 'wt-import-export-for-woo')
				);
				$out['import_path']=array(
					'label'=>__("Import path"),
					'type'=>'text',
					'value'=>"/",
					'tr_id'=>'import_path_tr',
					'css_class'=>'wt_iew_ftp_path',
					'tr_class'=>$fieldv['tr_class'],
					'field_name'=>'import_path',
					'form_toggler'=>array(
						'type'=>'child',
						'id'=>'wt_iew_file_from',
						'val'=>'ftp',
					)
				);
				$out['import_file']=array(
					'label'=>__("Import file", 'wt-import-export-for-woo'),
					'type'=>'text',
					'value'=>"",
					'tr_id'=>'import_file_tr',
					'tr_class'=>$fieldv['tr_class'],
					'field_name'=>'import_file',
					'form_toggler'=>array(
						'type'=>'child',
						'id'=>'wt_iew_file_from',
						'val'=>'ftp',
					)
				);
				$out['delete_file_after_import']=array(
					'label'=>__("Delete file after import", 'wt-import-export-for-woo'),
					'type'=>'radio',
					'value'=>'No',
					'radio_fields'=>array(
						'Yes'=>__('Yes', 'wt-import-export-for-woo'),
						'No'=>__('No', 'wt-import-export-for-woo')
					),
					'tr_id'=>'delete_file_after_import_tr',
					'field_name'=>'delete_file_after_import',
					'form_toggler'=>array(
						'type'=>'child',
						'id'=>'wt_iew_file_from',
						'val'=>'ftp',
					),
					'help_text'=>__('Delete the importing CSV from the selected FTP server.', 'wt-import-export-for-woo')
				);                                                             

			}
		}

		return $out;
	}

	/**
	* Add FTP related fields to the exporter advanced step
	*
	*/
	public function exporter_alter_advanced_fields($fields,$base,$advanced_form_data)
	{
		$export_type_arr=array(
			'export_now'=>__('Export now', 'wt-import-export-for-woo'),
		);
		if(Wt_Import_Export_For_Woo_Admin::module_exists('cron'))
		{
			$export_type_arr['schedule_now']=__('Schedule now', 'wt-import-export-for-woo');
		}

		$out=array();
		foreach($fields as $fieldk=>$fieldv)
		{
			$out[$fieldk]=$fieldv;
			
				$label_ftp_profiles=__("View/Add FTP profiles", 'wt-import-export-for-woo');
				$label_add_ftp_profile=__("Add new FTP profile", 'wt-import-export-for-woo');
				$ftp_list=$this->get_ftp_profile_for_select('export');
				$out['file_into'] = array(
					'label'=>__("Upload export file into FTP server", 'wt-import-export-for-woo'),
					'type'=>'radio',
					'radio_fields' => array('ftp'=>__('Enable'),'No'=>__('Disable')),
					'field_name'=>'file_into',
					'default_value'=>'No',
					'form_toggler'=>array(
						'type'=>'parent',
						'target'=>'wt_iew_file_into'
					),
					
				);
				
				$out['ftp_profile']=array(
					'label'=>__("Select an FTP profile", 'wt-import-export-for-woo'),
					'type'=>'select',
					'tr_id'=>'ftp_profile_tr',
					'sele_vals'=>$ftp_list,
					'field_name'=>'ftp_profile',
					'form_toggler'=>array(
						'type'=>'child',
						'id'=>'wt_iew_file_into',
						'val'=>'ftp',
					),
					'validation_rule'=>array('type'=>'int'),
					'after_form_field_html'=>'<a class="wt_iew_ftp_profiles" data-label-ftp-profiles="'.$label_ftp_profiles.'" data-label-add-ftp-profile="'.$label_add_ftp_profile.'" data-tab="'.(count($ftp_list)>1 ? 'ftp-profiles' : 'add-new-ftp').'">'.(count($ftp_list)>1 ? $label_ftp_profiles : $label_add_ftp_profile).'</a>',
				);
				$out['use_default_path']=array(
					'label'=>__("Export file path", 'wt-import-export-for-woo'),
					'type'=>'radio',
					'value'=>'Yes',
					'radio_fields'=>array(
						'Yes'=>__('Default', 'wt-import-export-for-woo'),
						'No'=>__('Custom', 'wt-import-export-for-woo')
					),
					'tr_id'=>'use_default_path_tr',
					'field_name'=>'use_default_path',
					'form_toggler'=>array(
						'type'=>'child',
						'id'=>'wt_iew_file_into',
						'val'=>'ftp',
					),
					'help_text'=>__('Use export path from FTP profile.', 'wt-import-export-for-woo')
				);
				$out['export_path']=array(
					'label'=>__("Export path", 'wt-import-export-for-woo'),
					'type'=>'text',
					'value'=>"/",
					'tr_id'=>'export_path_tr',
					'css_class'=>'wt_iew_ftp_path',
					'field_name'=>'export_path',
					'form_toggler'=>array(
						'type'=>'child',
						'id'=>'wt_iew_file_into',
						'val'=>'ftp',
					)
				);
		}
		return $out;
	}

	/**
	* Process ftp list for select boxes
	*/
	public function get_ftp_profile_for_select($action)
	{
		$profiles=$this->get_ftp_data();
		$sele_arr=array();
		if($profiles && is_array($profiles) && count($profiles)>0)
		{
			$sele_arr[0]=array('value'=>$this->lables['select_one'],'path'=>"");
			foreach($profiles as $profile)
			{
				$path=($action=='export' ? $profile['export_path'] : $profile['import_path']);
				$sele_arr[$profile['id']]=array('value'=>$profile['name'],'path'=>$path);
			}
		}else
		{
			$sele_arr[0]=array('value'=>$this->lables['no_ftp_prfile_found'],'path'=>"");
		}
		return $sele_arr;
	}

	/** 
	*	Test FTP connection 
	* 	@param array $out output array sample
	*/
	public function test_ftp($out)
	{
		$test_ftp_fields=array('wt_iew_hostname'=>'Host Name', 'wt_iew_ftpuser'=>'Username', 'wt_iew_ftppassword'=>'Password', 'wt_iew_ftpport'=>'Port', 'wt_iew_useftps'=>'FTPS', 'wt_iew_passivemode'=>'Passive mode', 'wt_iew_is_sftp'=>'SFTP');
		$profile_data=array();
		foreach($test_ftp_fields as $ftp_form_field=>$ftp_form_field_label)
    	{
    		$val=Wt_Iew_Sh_Pro::sanitize_data((isset($_POST[ $ftp_form_field ]) ? $_POST[ $ftp_form_field ] : ''), $ftp_form_field, $this->ftp_form_validation_rule);
    		if($val==="") /* all text values are mandatory */
    		{
    			$out['msg']=__($ftp_form_field_label." is mandatory");
    			$out['status']=false;
    			break;
    		}else
    		{
    			if($ftp_form_field=='wt_iew_ftpport' && $val===0)
    			{
    				$out['msg']=__($ftp_form_field_label." is mandatory");
	    			$out['status']=false;
	    			break;
    			}
    		}
    		$profile_data[$ftp_form_field]=$val;
    	}

    	if($out['status']) //no validation error
    	{
    		$ftp_profile=array(
				'server'=>$profile_data['wt_iew_hostname'],
				'user_name'=>$profile_data['wt_iew_ftpuser'],
				'password'=>$profile_data['wt_iew_ftppassword'],
				'port'=>$profile_data['wt_iew_ftpport'],
				'ftps'=>$profile_data['wt_iew_useftps'],
				'passive_mode'=>$profile_data['wt_iew_passivemode'],
				'is_sftp'=>$profile_data['wt_iew_is_sftp'],
			);
			include_once plugin_dir_path(__FILE__).'classes/class-ftpadapter.php';
			$ftp_adapter=new Wt_Import_Export_For_Woo_FtpAdapter();
			$out=$ftp_adapter->test_ftp(0, $ftp_profile);
		}

		return $out;
	}

	/**
	* Delete FTP profile
	* @param array $out output array sample
	*/
	public function delete_ftp($out)
	{
		$id=(isset($_POST['wt_iew_ftp_id']) ? intval($_POST['wt_iew_ftp_id']) : 0);
		if($id>0)
		{
			global $wpdb;
			$tb=$wpdb->prefix.Wt_Import_Export_For_Woo::$ftp_tb;
			$wpdb->delete($tb,array('id'=>$id),array('%d')); 
		}else
		{
			$out['msg']=__("Error");
    		$out['status']=false;
		}
		return $out;
	}

	/**
	* Ajax function to save FTP details
	*/
	private function save_ftp($out)
	{    	
    	$update_data=array();
    	foreach($this->ftp_form_fields as $ftp_form_field)
    	{
    		$val=Wt_Iew_Sh_Pro::sanitize_data((isset($_POST[ $ftp_form_field ]) ? $_POST[ $ftp_form_field ] : ''), $ftp_form_field, $this->ftp_form_validation_rule);
    		if($val==="") /* all text values are mandatory */
    		{
    			$out['msg']=__("All fields are mandatory", 'wt-import-export-for-woo');
    			$out['status']=false;
    			break;
    		}else /* other than text. May be int */
    		{
    			if($ftp_form_field=='wt_iew_ftpport' && $val===0)
    			{
    				$out['msg']=__("All fields are mandatory", 'wt-import-export-for-woo');
	    			$out['status']=false;
	    			break;
    			}
    		}
    		$update_data[$ftp_form_field]=$val;
    	}     	   	   	

    	$id=(isset($_POST['wt_iew_ftp_id']) ? intval($_POST['wt_iew_ftp_id']) : 0);
    	$name= stripslashes($update_data['wt_iew_profilename']);

    	if($out['status']) //no validation error, ftp edit call, check for duplcate name.
    	{
    		$ftp_data=$this->get_ftp_data_by_name($name);
    		if(count($ftp_data)>1) //least case
    		{
    			$out['msg']=__("FTP profile with same name already exists.", 'wt-import-export-for-woo');
    			$out['status']=false;
    		}else 
    		{ 	
    			if(isset($ftp_data[0]['id']) && $ftp_data[0]['id']!=$id) /* profile with same name exists */
    			{
    				$out['msg']=__("FTP profile with same name already exists.", 'wt-import-export-for-woo');
    				$out['status']=false;
    			}
    		}
    	}
    	
    	if($out['status']) //no validation error
    	{
    		$db_data=array(
				'name'=>$name,
				'server'=>$update_data['wt_iew_hostname'],
				'user_name'=>$update_data['wt_iew_ftpuser'],
				'password'=>$update_data['wt_iew_ftppassword'],
				'port'=>$update_data['wt_iew_ftpport'],
				'export_path'=>$update_data['wt_iew_ftpexport_path'],
				'import_path'=>$update_data['wt_iew_ftpimport_path'],
				'ftps'=>$update_data['wt_iew_useftps'],
				'passive_mode'=>$update_data['wt_iew_passivemode'],
				'is_sftp'=>$update_data['wt_iew_is_sftp'],
			);
			$db_data_type=array('%s','%s','%s','%s','%d','%s','%s','%d','%d','%d');
			if($id>0)
			{
				$out['id']=$id;
				if(!$this->update_ftp_data($id,$db_data,$db_data_type))
				{
					$out['msg']=__("Error");
    				$out['status']=false;
				}
			}else
			{
				$id=$this->add_ftp_data($db_data,$db_data_type);
				$out['id']=$id;
				if($id==0)
				{
					$out['msg']=__("Error");
    				$out['status']=false;
				}
			}
    	}
    	return $out;
	}

	/**
	* Print Settings page HTML Ajax function
	*/
	private function settings_page()
	{
		$this->popup_page=(isset($_POST['popup_page']) ? intval($_POST['popup_page']) : 0);
		include plugin_dir_path( __FILE__ ).'views/_settings_page.php';
		exit(); //not return anything, prints html
	}

	/**
	* Print FTP list HTML
	*/
	private function get_ftplist_html()
	{
		$ftp_list=$this->get_ftp_data();
		include plugin_dir_path( __FILE__ ).'views/_ftp-list.php';
	}

	/**
	* Print FTP list HTML Ajax function
	*/
	private function ftp_list($out)
	{
		$this->popup_page=(isset($_POST['popup_page']) ? intval($_POST['popup_page']) : 0);
		$this->get_ftplist_html();
		exit(); //not return anything, prints html
	}

	/** 
	* Module settings form
	*/
	public function out_settings_form($args)
	{
		$this->enqueue_assets();
		$view_file=plugin_dir_path( __FILE__ ).'views/settings.php';	
		
		$params=array(
		);
		Wt_Import_Export_For_Woo_Admin::envelope_settings_tabcontent('wt-ftp', $view_file, '', $params, 0);
	}

	/**
	* Create FTP profile
	* @param array $insert_data array of insert data
	* @param array $insert_data_type array of insert data format
	* @return array
	*/
	private function add_ftp_data($insert_data,$insert_data_type)
	{
		global $wpdb;
		$tb=$wpdb->prefix.Wt_Import_Export_For_Woo::$ftp_tb;
		if($wpdb->insert($tb,$insert_data,$insert_data_type)) //success
		{
			return $wpdb->insert_id;
		}
		return 0;
	}

	/**
	* Update FTP profile
	* @param int $id id of FTP profile
	* @param array $update_data array of update data
	* @param array $update_data_type array of update data format
	* @return array
	*/
	private function update_ftp_data($id,$update_data,$update_data_type)
	{
		global $wpdb;
		//updating the data
		$tb=$wpdb->prefix.Wt_Import_Export_For_Woo::$ftp_tb;
		$update_where=array(
			'id'=>$id
		);
		$update_where_type=array(
			'%d'
		);
		if($wpdb->update($tb,$update_data,$update_where,$update_data_type,$update_where_type)!==false)
		{
			return true;
		}
		return false;
	}

	/**
	* Get FTP profile by name from DB
	* @param string $name
	* @return array 
	*/
	private function get_ftp_data_by_name($name)
	{
		global $wpdb;
		$tb=$wpdb->prefix.Wt_Import_Export_For_Woo::$ftp_tb;
		$qry=$wpdb->prepare("SELECT * FROM $tb WHERE name=%s",array($name));
		$val=$wpdb->get_results($qry,ARRAY_A);
		if($val)
		{
			return $val;
		}else
		{
			return array();
		}
	}

	/**
	* Get FTP profile by name from DB
	* @param string $name
	* @return array 
	*/
	public static function get_ftp_data_by_id($id)
	{
		global $wpdb;
		$tb=$wpdb->prefix.Wt_Import_Export_For_Woo::$ftp_tb;
		$qry=$wpdb->prepare("SELECT * FROM $tb WHERE id=%d",array($id));
		$val=$wpdb->get_row($qry,ARRAY_A);
		if($val)
		{
			return $val;
		}else
		{
			return false;
		}
	}

	/**
	* Get FTP profile list from DB
	* @return array list of FTP profiles
	*/
	public static function get_ftp_data()
	{
		global $wpdb;
		$tb=$wpdb->prefix.Wt_Import_Export_For_Woo::$ftp_tb;
		$val=$wpdb->get_results("SELECT * FROM $tb ORDER BY id DESC",ARRAY_A);
		if($val)
		{
			return $val;
		}else
		{
			return array();
		}
	}
}
new Wt_Import_Export_For_Woo_Ftp();