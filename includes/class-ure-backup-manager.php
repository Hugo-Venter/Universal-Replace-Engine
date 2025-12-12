<?php
/**
 * Backup Manager Class
 *
 * Handles SQL backup creation and restoration.
 * Creates portable SQL dumps for complete database backups.
 *
 * @package UniversalReplaceEngine
 * @since 1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Manager class for SQL backup/restore operations.
 */
class URE_Backup_Manager {

	/**
	 * WordPress database instance.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Backup directory.
	 *
	 * @var string
	 */
	private $backup_dir;

	/**
	 * Maximum backup age in days.
	 *
	 * @var int
	 */
	private $max_backup_age = 7;

	/**
	 * Rows per batch for backup.
	 *
	 * @var int
	 */
	private $batch_size = 1000;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;

		// Set backup directory (in uploads, protected).
		$upload_dir       = wp_upload_dir();
		$this->backup_dir = $upload_dir['basedir'] . '/ure-backups';

		// Create backup directory if it doesn't exist.
		$this->ensure_backup_directory();
	}

	/**
	 * Ensure backup directory exists and is protected.
	 */
	private function ensure_backup_directory() {
		if ( ! file_exists( $this->backup_dir ) ) {
			wp_mkdir_p( $this->backup_dir );
		}

		// Add .htaccess protection.
		$htaccess_file = $this->backup_dir . '/.htaccess';
		if ( ! file_exists( $htaccess_file ) ) {
			$htaccess_content = "Order deny,allow\nDeny from all\n";
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_put_contents_file_put_contents
			file_put_contents( $htaccess_file, $htaccess_content );
		}

		// Add index.php protection.
		$index_file = $this->backup_dir . '/index.php';
		if ( ! file_exists( $index_file ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_put_contents_file_put_contents
			file_put_contents( $index_file, '<?php // Silence is golden' );
		}
	}

	/**
	 * Create a backup of specified tables.
	 *
	 * @param array  $tables  Table names to backup.
	 * @param string $comment Optional comment for backup.
	 * @return array Result with success status and file path.
	 */
	public function create_backup( $tables = array(), $comment = '' ) {
		if ( empty( $tables ) ) {
			return array(
				'success' => false,
				'message' => __( 'No tables specified for backup.', 'universal-replace-engine' ),
			);
		}

		// Generate backup filename with timestamp and random salt.
		$timestamp = gmdate( 'Y-m-d_H-i-s' );
		$salt      = substr( md5( uniqid( '', true ) ), 0, 8 );
		$filename  = sprintf( 'ure-backup-%s-%s.sql', $timestamp, $salt );
		$file_path = $this->backup_dir . '/' . $filename;

		// Open file for writing.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$file_handle = fopen( $file_path, 'w' );

		if ( ! $file_handle ) {
			return array(
				'success' => false,
				'message' => __( 'Could not create backup file.', 'universal-replace-engine' ),
			);
		}

		// Write header.
		$this->write_backup_header( $file_handle, $tables, $comment );

		$backed_up_tables = array();

		// Backup each table.
		foreach ( $tables as $table_name ) {
			$result = $this->backup_table( $file_handle, $table_name );

			if ( $result['success'] ) {
				$backed_up_tables[] = $table_name;
			}
		}

		// Write footer.
		$this->write_backup_footer( $file_handle );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $file_handle );

		// Store backup metadata.
		$this->save_backup_metadata( $filename, $backed_up_tables, $comment );

		// Clean up old backups.
		$this->cleanup_old_backups();

		return array(
			'success'     => true,
			'message'     => __( 'Backup created successfully.', 'universal-replace-engine' ),
			'file'        => $filename,
			'file_path'   => $file_path,
			'file_size'   => filesize( $file_path ),
			'tables'      => $backed_up_tables,
			'table_count' => count( $backed_up_tables ),
		);
	}

	/**
	 * Write backup header.
	 *
	 * @param resource $file_handle File handle.
	 * @param array    $tables      Table names.
	 * @param string   $comment     Comment.
	 */
	private function write_backup_header( $file_handle, $tables, $comment ) {
		$header = array(
			'-- Universal Replace Engine SQL Backup',
			'-- Generated: ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC',
			'-- WordPress Version: ' . get_bloginfo( 'version' ),
			'-- MySQL Version: ' . $this->wpdb->db_version(),
			'-- PHP Version: ' . PHP_VERSION,
			'-- Site URL: ' . get_site_url(),
			'-- Tables: ' . count( $tables ),
		);

		if ( ! empty( $comment ) ) {
			$header[] = '-- Comment: ' . $comment;
		}

		$header[] = '--';
		$header[] = '';
		$header[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
		$header[] = 'SET time_zone = "+00:00";';
		$header[] = '';
		$header[] = '/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;';
		$header[] = '/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;';
		$header[] = '/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;';
		$header[] = '/*!40101 SET NAMES utf8mb4 */;';
		$header[] = '';

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		fwrite( $file_handle, implode( "\n", $header ) . "\n" );
	}

	/**
	 * Write backup footer.
	 *
	 * @param resource $file_handle File handle.
	 */
	private function write_backup_footer( $file_handle ) {
		$footer = array(
			'',
			'/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;',
			'/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;',
			'/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;',
			'',
			'-- Backup completed at ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC',
		);

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		fwrite( $file_handle, implode( "\n", $footer ) . "\n" );
	}

	/**
	 * Backup a single table.
	 *
	 * @param resource $file_handle File handle.
	 * @param string   $table_name  Table name.
	 * @return array Result.
	 */
	private function backup_table( $file_handle, $table_name ) {
		// Verify table exists.
		$tables = $this->wpdb->get_col( 'SHOW TABLES' );
		if ( ! in_array( $table_name, $tables, true ) ) {
			return array(
				'success' => false,
				'error'   => sprintf( 'Table %s does not exist', $table_name ),
			);
		}

		// Write table header.
		$table_header = array(
			'',
			'--',
			'-- Table structure for table `' . $table_name . '`',
			'--',
			'',
		);

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		fwrite( $file_handle, implode( "\n", $table_header ) . "\n" );

		// Get CREATE TABLE statement.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$create_table = $this->wpdb->get_row( "SHOW CREATE TABLE `{$table_name}`", ARRAY_N );

		if ( $create_table ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
			fwrite( $file_handle, "DROP TABLE IF EXISTS `{$table_name}`;\n" );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
			fwrite( $file_handle, $create_table[1] . ";\n\n" );
		}

		// Get row count.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row_count = $this->wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" );

		if ( $row_count > 0 ) {
			// Write data header.
			$data_header = array(
				'--',
				'-- Dumping data for table `' . $table_name . '`',
				'--',
				'',
			);

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
			fwrite( $file_handle, implode( "\n", $data_header ) . "\n" );

			// Get data in batches.
			$pages = ceil( $row_count / $this->batch_size );

			for ( $page = 0; $page < $pages; $page++ ) {
				$offset = $page * $this->batch_size;

				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$rows = $this->wpdb->get_results(
					$this->wpdb->prepare(
						"SELECT * FROM `{$table_name}` LIMIT %d OFFSET %d",
						$this->batch_size,
						$offset
					),
					ARRAY_A
				);

				if ( ! empty( $rows ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
					fwrite( $file_handle, "INSERT INTO `{$table_name}` VALUES\n" );

					$row_count_in_batch = count( $rows );
					$row_index          = 0;

					foreach ( $rows as $row ) {
						$row_index++;
						$values = array();

						foreach ( $row as $value ) {
							if ( null === $value ) {
								$values[] = 'NULL';
							} else {
								$values[] = "'" . $this->wpdb->_real_escape( $value ) . "'";
							}
						}

						$insert = '(' . implode( ',', $values ) . ')';

						if ( $row_index < $row_count_in_batch ) {
							$insert .= ',';
						} else {
							$insert .= ';';
						}

						// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
						fwrite( $file_handle, $insert . "\n" );
					}

					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
					fwrite( $file_handle, "\n" );
				}
			}
		}

		return array(
			'success'   => true,
			'table'     => $table_name,
			'row_count' => $row_count,
		);
	}

	/**
	 * Save backup metadata.
	 *
	 * @param string $filename Backup filename.
	 * @param array  $tables   Table names.
	 * @param string $comment  Comment.
	 */
	private function save_backup_metadata( $filename, $tables, $comment ) {
		$backups = get_option( 'ure_backups', array() );

		$backups[ $filename ] = array(
			'created'     => current_time( 'mysql' ),
			'created_gmt' => current_time( 'mysql', true ),
			'user_id'     => get_current_user_id(),
			'tables'      => $tables,
			'table_count' => count( $tables ),
			'comment'     => $comment,
			'file_path'   => $this->backup_dir . '/' . $filename,
		);

		update_option( 'ure_backups', $backups );
	}

	/**
	 * Get list of available backups.
	 *
	 * @return array List of backups with metadata.
	 */
	public function get_backups() {
		$backups = get_option( 'ure_backups', array() );
		$result  = array();

		foreach ( $backups as $filename => $metadata ) {
			$file_path = $metadata['file_path'];

			if ( file_exists( $file_path ) ) {
				$metadata['file_size']    = filesize( $file_path );
				$metadata['file_size_mb'] = round( filesize( $file_path ) / 1024 / 1024, 2 );
				$metadata['filename']     = $filename;
				$metadata['can_restore']  = is_readable( $file_path );

				// Add user display name.
				$user                    = get_userdata( $metadata['user_id'] );
				$metadata['user_name']   = $user ? $user->display_name : __( 'Unknown', 'universal-replace-engine' );

				$result[] = $metadata;
			}
		}

		// Sort by created date (newest first).
		usort( $result, function( $a, $b ) {
			return strtotime( $b['created_gmt'] ) - strtotime( $a['created_gmt'] );
		});

		return $result;
	}

	/**
	 * Restore from a backup file.
	 *
	 * @param string $filename Backup filename.
	 * @return array Result with success status.
	 */
	public function restore_backup( $filename ) {
		$backups = get_option( 'ure_backups', array() );

		if ( ! isset( $backups[ $filename ] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Backup not found in metadata.', 'universal-replace-engine' ),
			);
		}

		$file_path = $backups[ $filename ]['file_path'];

		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'Backup file not found or not readable.', 'universal-replace-engine' ),
			);
		}

		// Execute SQL file.
		$result = $this->execute_sql_file( $file_path );

		if ( $result['success'] ) {
			// Log restoration.
			do_action( 'ure_backup_restored', $filename, $backups[ $filename ] );
		}

		return $result;
	}

	/**
	 * Execute SQL file.
	 *
	 * @param string $file_path Path to SQL file.
	 * @return array Result.
	 */
	private function execute_sql_file( $file_path ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$sql = file_get_contents( $file_path );

		if ( false === $sql ) {
			return array(
				'success' => false,
				'message' => __( 'Could not read backup file.', 'universal-replace-engine' ),
			);
		}

		// Split into individual queries.
		$queries = $this->split_sql_file( $sql );

		$executed = 0;
		$errors   = array();

		foreach ( $queries as $query ) {
			$query = trim( $query );

			if ( empty( $query ) || strpos( $query, '--' ) === 0 ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $this->wpdb->query( $query );

			if ( false === $result ) {
				$errors[] = $this->wpdb->last_error;
			} else {
				$executed++;
			}
		}

		return array(
			'success'  => empty( $errors ),
			'message'  => sprintf(
				/* translators: %d: number of queries */
				__( 'Executed %d queries successfully.', 'universal-replace-engine' ),
				$executed
			),
			'executed' => $executed,
			'errors'   => $errors,
		);
	}

	/**
	 * Split SQL file into individual queries.
	 *
	 * @param string $sql SQL content.
	 * @return array Array of queries.
	 */
	private function split_sql_file( $sql ) {
		// Remove comments.
		$sql = preg_replace( '/^--.*$/m', '', $sql );
		$sql = preg_replace( '/\/\*.*?\*\//s', '', $sql );

		// Split by semicolons (simple approach).
		$queries = explode( ";\n", $sql );

		return array_filter( $queries );
	}

	/**
	 * Delete a backup.
	 *
	 * @param string $filename Backup filename.
	 * @return array Result.
	 */
	public function delete_backup( $filename ) {
		$backups = get_option( 'ure_backups', array() );

		if ( ! isset( $backups[ $filename ] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Backup not found.', 'universal-replace-engine' ),
			);
		}

		$file_path = $backups[ $filename ]['file_path'];

		if ( file_exists( $file_path ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			unlink( $file_path );
		}

		unset( $backups[ $filename ] );
		update_option( 'ure_backups', $backups );

		return array(
			'success' => true,
			'message' => __( 'Backup deleted successfully.', 'universal-replace-engine' ),
		);
	}

	/**
	 * Clean up old backups.
	 */
	private function cleanup_old_backups() {
		$backups     = get_option( 'ure_backups', array() );
		$max_age     = $this->max_backup_age * DAY_IN_SECONDS;
		$current_time = time();

		foreach ( $backups as $filename => $metadata ) {
			$created_time = strtotime( $metadata['created_gmt'] );
			$age          = $current_time - $created_time;

			if ( $age > $max_age ) {
				$this->delete_backup( $filename );
			}
		}
	}

	/**
	 * Get backup directory path.
	 *
	 * @return string
	 */
	public function get_backup_directory() {
		return $this->backup_dir;
	}
}
