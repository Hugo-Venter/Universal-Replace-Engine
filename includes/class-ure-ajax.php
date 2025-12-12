<?php
/**
 * AJAX Handler Class
 *
 * Handles AJAX requests for progress updates during operations.
 *
 * @package UniversalReplaceEngine
 * @since 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX Handler class for asynchronous operations.
 */
class URE_Ajax {

	/**
	 * Search/Replace engine instance.
	 *
	 * @var URE_Search_Replace
	 */
	private $search_replace;

	/**
	 * Logger instance.
	 *
	 * @var URE_Logger
	 */
	private $logger;

	/**
	 * Constructor.
	 *
	 * @param URE_Search_Replace $search_replace Search/Replace instance.
	 * @param URE_Logger         $logger         Logger instance.
	 */
	public function __construct( $search_replace, $logger ) {
		$this->search_replace = $search_replace;
		$this->logger         = $logger;

		// Register AJAX handlers.
		add_action( 'wp_ajax_ure_preview_batch', array( $this, 'preview_batch' ) );
		add_action( 'wp_ajax_ure_apply_batch', array( $this, 'apply_batch' ) );
		add_action( 'wp_ajax_ure_get_status', array( $this, 'get_status' ) );
	}

	/**
	 * Handle preview batch AJAX request.
	 */
	public function preview_batch() {
		check_ajax_referer( 'ure_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'universal-replace-engine' ) ) );
		}

		$search         = isset( $_POST['search'] ) ? wp_unslash( $_POST['search'] ) : '';
		$replace        = isset( $_POST['replace'] ) ? wp_unslash( $_POST['replace'] ) : '';
		$post_types     = isset( $_POST['post_types'] ) ? (array) $_POST['post_types'] : array( 'post', 'page' );
		$case_sensitive = isset( $_POST['case_sensitive'] ) && 'true' === $_POST['case_sensitive'];
		$regex_mode     = isset( $_POST['regex_mode'] ) && 'true' === $_POST['regex_mode'];
		$batch_page     = isset( $_POST['batch_page'] ) ? absint( $_POST['batch_page'] ) : 1;

		if ( empty( $search ) ) {
			wp_send_json_error( array( 'message' => __( 'Search term is required.', 'universal-replace-engine' ) ) );
		}

		// Get batch size from settings.
		$batch_size = URE_Settings::get( 'content_batch_size', 100 );

		// Perform preview for this batch.
		$result = $this->search_replace->preview_batch(
			$search,
			$replace,
			$post_types,
			$case_sensitive,
			$regex_mode,
			$batch_page,
			$batch_size
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		// Store preview data in transient.
		if ( 1 === $batch_page ) {
			// First batch - store fresh data.
			set_transient( 'ure_preview_' . get_current_user_id(), $result, HOUR_IN_SECONDS );
		} else {
			// Append to existing preview data.
			$existing = get_transient( 'ure_preview_' . get_current_user_id() );
			if ( $existing && isset( $existing['matches'] ) ) {
				$result['matches'] = array_merge( $existing['matches'], $result['matches'] );
				set_transient( 'ure_preview_' . get_current_user_id(), $result, HOUR_IN_SECONDS );
			}
		}

		wp_send_json_success( $result );
	}

	/**
	 * Handle apply batch AJAX request.
	 */
	public function apply_batch() {
		check_ajax_referer( 'ure_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'universal-replace-engine' ) ) );
		}

		$search         = isset( $_POST['search'] ) ? wp_unslash( $_POST['search'] ) : '';
		$replace        = isset( $_POST['replace'] ) ? wp_unslash( $_POST['replace'] ) : '';
		$post_types     = isset( $_POST['post_types'] ) ? (array) $_POST['post_types'] : array( 'post', 'page' );
		$case_sensitive = isset( $_POST['case_sensitive'] ) && 'true' === $_POST['case_sensitive'];
		$regex_mode     = isset( $_POST['regex_mode'] ) && 'true' === $_POST['regex_mode'];
		$batch_page     = isset( $_POST['batch_page'] ) ? absint( $_POST['batch_page'] ) : 1;

		if ( empty( $search ) ) {
			wp_send_json_error( array( 'message' => __( 'Search term is required.', 'universal-replace-engine' ) ) );
		}

		// Get batch size from settings.
		$batch_size = URE_Settings::get( 'content_batch_size', 100 );

		// Apply changes for this batch.
		$result = $this->search_replace->apply_batch(
			$search,
			$replace,
			$post_types,
			$case_sensitive,
			$regex_mode,
			$batch_page,
			$batch_size
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		// If this is the last batch, log the operation.
		if ( $result['is_last_batch'] ) {
			$summary = sprintf(
				/* translators: 1: search, 2: replace, 3: updates count */
				__( 'Replaced "%1$s" with "%2$s" in %3$d posts.', 'universal-replace-engine' ),
				$search,
				$replace,
				$result['total_updates']
			);

			$this->logger->log_operation(
				get_current_user_id(),
				$summary,
				array(
					'type'           => 'content',
					'search'         => $search,
					'replace'        => $replace,
					'post_types'     => $post_types,
					'case_sensitive' => $case_sensitive,
					'regex_mode'     => $regex_mode,
					'total_updates'  => $result['total_updates'],
				)
			);
		}

		wp_send_json_success( $result );
	}

	/**
	 * Get operation status.
	 */
	public function get_status() {
		check_ajax_referer( 'ure_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'universal-replace-engine' ) ) );
		}

		$operation_id = isset( $_POST['operation_id'] ) ? sanitize_key( $_POST['operation_id'] ) : '';

		if ( empty( $operation_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Operation ID is required.', 'universal-replace-engine' ) ) );
		}

		$status = get_transient( 'ure_operation_' . $operation_id );

		if ( false === $status ) {
			wp_send_json_error( array( 'message' => __( 'Operation not found or expired.', 'universal-replace-engine' ) ) );
		}

		wp_send_json_success( $status );
	}
}
