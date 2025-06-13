<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.webtoffee.com/
 * @since      1.0.0
 *
 * @package    Wt_Import_Export_For_Woo
 * @subpackage Wt_Import_Export_For_Woo/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wt_Import_Export_For_Woo
 * @subpackage Wt_Import_Export_For_Woo/includes
 * @author     Webtoffee <info@webtoffee.com>
 */
class Wt_Import_Export_For_Woo {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wt_Import_Export_For_Woo_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	public static $loaded_modules=array();
	public static $loaded_sub_modules=array();        

	public static $template_tb='wt_iew_mapping_template';
	public static $history_tb='wt_iew_action_history';
	public static $ftp_tb='wt_iew_ftp';
	public static $cron_tb='wt_iew_cron';

	public static $email_tb='wt_iew_email_template';


	public $plugin_admin;
	public $plugin_public;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WT_IEW_VERSION' ) ) {
			$this->version = WT_IEW_VERSION;
		} else {
			$this->version = '1.3.2';
		}
		$this->plugin_name = 'wt-import-export-for-woo';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wt_Import_Export_For_Woo_Loader. Orchestrates the hooks of the plugin.
	 * - Wt_Import_Export_For_Woo_i18n. Defines internationalization functionality.
	 * - Wt_Import_Export_For_Woo_Admin. Defines all hooks for the admin area.
	 * - Wt_Import_Export_For_Woo_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wt-import-export-for-woo-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wt-import-export-for-woo-i18n.php';

		/**
		 * The class responsible for defining remote file functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wt-import-export-for-woo-remoteadapter.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wt-import-export-for-woo-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wt-import-export-for-woo-public.php';

		/**
		 * Class includes input sanitization and role checking
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helpers/class-wt-security-helper.php';

		/**
		 * Class includes common helper functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helpers/class-wt-common-helper.php';

		/**
		 * Class includes helper functions for import and export modules
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helpers/class-wt-import-export-helper.php';

		/**
		 * Class includes log writing functions
		 */
		require_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-log.php';
		require_once WT_IEW_PLUGIN_PATH . 'admin/classes/class-logwriter.php';

		/**
		 * Show the GDPR promotion banner, if the class `Wt_Gdpr_Promotion_banner` not exists, and the revamped and legacy gdpr plugins are not active.
		 * 
		 * @since 1.3.1
		 */
		if ( ! class_exists( 'Wt_Gdpr_Promotion_banner' ) && !is_plugin_active( 'webtoffee-cookie-consent/webtoffee-cookie-consent.php' ) && !is_plugin_active( 'webtoffee-gdpr-cookie-consent/cookie-law-info.php' ) ) {
			require_once plugin_dir_path( __DIR__ ) . 'admin/views/class-wt-gdpr-promotion-banner.php';
		}


		$this->loader = new Wt_Import_Export_For_Woo_Loader();
		$this->plugin_admin = new Wt_Import_Export_For_Woo_Admin( $this->get_plugin_name(), $this->get_version() );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wt_Import_Export_For_Woo_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wt_Import_Export_For_Woo_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() 
	{
		//ajax hook for saving settings, Includes plugin main settings and settings from module
		$this->loader->add_action('wp_ajax_wt_iew_save_settings',$this->plugin_admin,'save_settings');
		$this->loader->add_action('wp_ajax_wt_iew_save_email_settings',$this->plugin_admin,'save_email_settings');		
        $this->loader->add_action('wp_ajax_wt_iew_delete_template_pro',$this->plugin_admin,'delete_template');
		$this->loader->add_action('wp_ajax_wt_iew_ajax_user_search',$this->plugin_admin,'ajax_user_search');
		$this->loader->add_action('wp_ajax_wt_iew_ajax_coupon_search',$this->plugin_admin,'ajax_coupon_search');
		/* Loading admin modules */
		$this->plugin_admin->admin_modules();

		/* Plugin page links */
		$this->loader->add_filter('plugin_action_links_'.WT_IEW_PLUGIN_BASENAME, $this->plugin_admin, 'plugin_action_links');
		
		/* Admin menus */
		$this->loader->add_action('admin_menu',$this->plugin_admin, 'admin_menu',11);
		
		/* Enqueue CSS and JS */
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts' );
		
		$this->loader->add_action( 'woocommerce_debug_tools', $this->plugin_admin, 'add_debug_tools' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wt_Import_Export_For_Woo_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public static function get_module_id($module_base)
	{
		return WT_IEW_PLUGIN_ID.'_'.$module_base;
	}

	/**
	 * Some modules are not start by default. So need to initialize via code OR get object of already started modules
	 *
	 * @since    1.0.0
	 */
	public static function load_modules($module)
	{
		if(Wt_Import_Export_For_Woo_Admin::module_exists($module))
		{ 
			if(!isset(self::$loaded_modules[$module]))
			{
				$module_class='Wt_Import_Export_For_Woo_'.ucfirst($module);
				self::$loaded_modules[$module]=new $module_class;
			}
			return self::$loaded_modules[$module];
		}
		else
		{
			return null;
		}
	}
	/**
	 * Load sub modules ( product_categories, product_tags ) comes inside product module
	 *
	 * @since    1.1.3
	 */
	public static function load_sub_modules($sub_module)
	{

			if(!isset(self::$loaded_sub_modules[$sub_module]))
			{
				$module_class='Wt_Import_Export_For_Woo_'.ucfirst($sub_module);
                                if(class_exists($module_class)){
                                    self::$loaded_sub_modules[$sub_module]=new $module_class;
                                }
			}
			return isset(self::$loaded_sub_modules[$sub_module]) ? self::$loaded_sub_modules[$sub_module] : null;

	}        

	/**
	 * Generate tab head for settings page.
	 * @since     1.0.0
	 */
	public static function generate_settings_tabhead($title_arr, $type="plugin")
	{	
		$out_arr=apply_filters("wt_iew_".$type."_settings_tabhead",$title_arr);
		foreach($out_arr as $k=>$v)
		{			
			if(is_array($v))
			{
				$v=(isset($v[2]) ? $v[2] : '').$v[0].' '.(isset($v[1]) ? $v[1] : '');
			}
		?>
			<a class="nav-tab" href="#<?php echo $k;?>"><?php echo $v; ?></a>
		<?php
		}
	}

	/**
	*  	Get remote file adapters. Eg: FTP, Gdrive, OneDrive
	* 	@param string $action action to be executed, If the current adapter is not suitable for a specific action then skip it
	*   @param string $adapter optional specify an adapter name to retrive the specific one
	* 	@return array|single array of remote adapters or single adapter if the adapter name specified
	*/
	public static function get_remote_adapters($action, $adapter='')
	{
		$adapters=array();
		$adapters = apply_filters("wt_iew_remote_adapters", $adapters, $action, $adapter);
		if($adapter != "")
		{
			return (isset($adapters[ $adapter ]) ? $adapters[ $adapter ] : null);
		}
		return $adapters;
	}

}
