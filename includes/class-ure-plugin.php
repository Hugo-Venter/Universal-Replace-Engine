<?php
/**
 * Main Plugin Class
 *
 * Singleton pattern for managing the plugin lifecycle and dependencies.
 *
 * @package UniversalReplaceEngine
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class using singleton pattern.
 */
class URE_Plugin {

	/**
	 * Single instance of this class.
	 *
	 * @var URE_Plugin
	 */
	private static $instance = null;

	/**
	 * Search/Replace engine instance.
	 *
	 * @var URE_Search_Replace
	 */
	public $search_replace;

	/**
	 * Logger instance.
	 *
	 * @var URE_Logger
	 */
	public $logger;

	/**
	 * Admin instance.
	 *
	 * @var URE_Admin
	 */
	public $admin;

	/**
	 * Database Manager instance.
	 *
	 * @var URE_Database_Manager
	 */
	public $database_manager;

	/**
	 * Backup Manager instance.
	 *
	 * @var URE_Backup_Manager
	 */
	public $backup_manager;

	/**
	 * AJAX Handler instance.
	 *
	 * @var URE_Ajax
	 */
	public $ajax;

	/**
	 * Get singleton instance.
	 *
	 * @return URE_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - private to enforce singleton.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies() {
		require_once URE_PLUGIN_DIR . 'includes/class-ure-settings.php';
		require_once URE_PLUGIN_DIR . 'includes/class-ure-profiles.php';
		require_once URE_PLUGIN_DIR . 'includes/class-ure-search-replace.php';
		require_once URE_PLUGIN_DIR . 'includes/class-ure-logger.php';
		require_once URE_PLUGIN_DIR . 'includes/class-ure-ajax.php';
		require_once URE_PLUGIN_DIR . 'includes/class-ure-admin.php';
		require_once URE_PLUGIN_DIR . 'includes/class-ure-database-manager.php';
		require_once URE_PLUGIN_DIR . 'includes/class-ure-backup-manager.php';

		// Initialize components.
		$this->logger           = new URE_Logger();
		$this->search_replace   = new URE_Search_Replace( $this->logger );
		$this->database_manager = new URE_Database_Manager( $this->logger );
		$this->backup_manager   = new URE_Backup_Manager();
		$this->ajax             = new URE_Ajax( $this->search_replace, $this->logger );
		$this->admin            = new URE_Admin( $this->search_replace, $this->logger );
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		// Enqueue admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		/**
		 * Extension hook for Pro features.
		 * Allows Pro version to hook in and add additional functionality.
		 *
		 * @since 1.0.0
		 * @param URE_Plugin $plugin The main plugin instance.
		 */
		do_action( 'ure_plugin_loaded', $this );
	}

	/**
	 * Enqueue admin CSS and JavaScript.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on our plugin page.
		if ( 'tools_page_universal-replace-engine' !== $hook ) {
			return;
		}

		// Enqueue CSS.
		wp_enqueue_style(
			'ure-admin-css',
			URE_PLUGIN_URL . 'assets/admin.css',
			array(),
			URE_VERSION
		);

		// Enqueue JavaScript.
		wp_enqueue_script(
			'ure-admin-js',
			URE_PLUGIN_URL . 'assets/admin.js',
			array( 'jquery' ),
			URE_VERSION,
			true
		);

		// Localize script with AJAX URL and nonce.
		wp_localize_script(
			'ure-admin-js',
			'ureData',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'ure_ajax_nonce' ),
				'ajaxProcessing' => URE_Settings::get( 'ajax_processing', true ),
			)
		);
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return URE_VERSION;
	}
}
