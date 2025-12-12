<?php
/**
 * Profiles Manager Class
 *
 * Handles saving and loading search/replace profiles.
 *
 * @package UniversalReplaceEngine
 * @since 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Profiles Manager class for saving/loading configurations.
 */
class URE_Profiles {

	/**
	 * Option name for profiles.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'ure_profiles';

	/**
	 * Get all profiles for current user.
	 *
	 * @param int $user_id User ID (0 = current user).
	 * @return array Array of profiles.
	 */
	public static function get_all( $user_id = 0 ) {
		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		$profiles = get_user_meta( $user_id, self::OPTION_NAME, true );

		if ( ! is_array( $profiles ) ) {
			return array();
		}

		return $profiles;
	}

	/**
	 * Get a single profile by name.
	 *
	 * @param string $name     Profile name.
	 * @param int    $user_id  User ID (0 = current user).
	 * @return array|null Profile data or null if not found.
	 */
	public static function get( $name, $user_id = 0 ) {
		$profiles = self::get_all( $user_id );

		if ( isset( $profiles[ $name ] ) ) {
			return $profiles[ $name ];
		}

		return null;
	}

	/**
	 * Save a profile.
	 *
	 * @param string $name    Profile name.
	 * @param array  $data    Profile data.
	 * @param int    $user_id User ID (0 = current user).
	 * @return bool True on success.
	 */
	public static function save( $name, $data, $user_id = 0 ) {
		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( empty( $name ) ) {
			return false;
		}

		$profiles = self::get_all( $user_id );

		// Sanitize profile data.
		$sanitized_data = array(
			'search'         => isset( $data['search'] ) ? sanitize_text_field( $data['search'] ) : '',
			'replace'        => isset( $data['replace'] ) ? sanitize_text_field( $data['replace'] ) : '',
			'post_types'     => isset( $data['post_types'] ) ? array_map( 'sanitize_text_field', (array) $data['post_types'] ) : array(),
			'case_sensitive' => isset( $data['case_sensitive'] ) ? (bool) $data['case_sensitive'] : false,
			'regex_mode'     => isset( $data['regex_mode'] ) ? (bool) $data['regex_mode'] : false,
			'scope'          => isset( $data['scope'] ) ? sanitize_key( $data['scope'] ) : 'post_content',
			'created_at'     => isset( $profiles[ $name ]['created_at'] ) ? $profiles[ $name ]['created_at'] : current_time( 'mysql' ),
			'updated_at'     => current_time( 'mysql' ),
		);

		$profiles[ $name ] = $sanitized_data;

		return update_user_meta( $user_id, self::OPTION_NAME, $profiles );
	}

	/**
	 * Delete a profile.
	 *
	 * @param string $name    Profile name.
	 * @param int    $user_id User ID (0 = current user).
	 * @return bool True on success.
	 */
	public static function delete( $name, $user_id = 0 ) {
		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		$profiles = self::get_all( $user_id );

		if ( ! isset( $profiles[ $name ] ) ) {
			return false;
		}

		unset( $profiles[ $name ] );

		return update_user_meta( $user_id, self::OPTION_NAME, $profiles );
	}

	/**
	 * Delete all profiles for a user.
	 *
	 * @param int $user_id User ID (0 = current user).
	 * @return bool True on success.
	 */
	public static function delete_all( $user_id = 0 ) {
		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		return delete_user_meta( $user_id, self::OPTION_NAME );
	}

	/**
	 * Check if a profile exists.
	 *
	 * @param string $name    Profile name.
	 * @param int    $user_id User ID (0 = current user).
	 * @return bool True if exists.
	 */
	public static function exists( $name, $user_id = 0 ) {
		return null !== self::get( $name, $user_id );
	}

	/**
	 * Get profile count for a user.
	 *
	 * @param int $user_id User ID (0 = current user).
	 * @return int Number of profiles.
	 */
	public static function count( $user_id = 0 ) {
		return count( self::get_all( $user_id ) );
	}
}
