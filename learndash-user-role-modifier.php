<?php
/**
 * Plugin Name: LearnDash User Role Modifier
 * Version: 1.0
 * Description: This addon enhances group management by adding a custom field in the group edit section. When a student is assigned to a group, a new user role is automatically added to them.
 * Author: LDninjas.com
 * Author URI: LDninjas.com
 * Plugin URI: LDninjas.com
 * Text Domain: learndash-user-role-modifier
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class LearnDash_User_Role_Modifier
 */
class LearnDash_User_Role_Modifier {

    const VERSION = '1.0';

    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof LearnDash_User_Role_Modifier ) ) {
            self::$instance = new self;

            self::$instance->setup_constants();
            self::$instance->hooks();
            self::$instance->includes();
        }

        return self::$instance;
    }

    /**
     * defining constants for plugin
     */
    public function setup_constants() {

        /**
         * Directory
         */
        define( 'LURM_DIR', plugin_dir_path ( __FILE__ ) );
        define( 'LURM_DIR_FILE', LURM_DIR . basename ( __FILE__ ) );
        define( 'LURM_INCLUDES_DIR', trailingslashit ( LURM_DIR . 'includes' ) );
        define( 'LURM_TEMPLATES_DIR', trailingslashit ( LURM_DIR . 'templates' ) );
        define( 'LURM_BASE_DIR', plugin_basename(__FILE__));

        /**
         * URLs
         */
        define( 'LURM_URL', trailingslashit ( plugins_url ( '', __FILE__ ) ) );
        define( 'LURM_ASSETS_URL', trailingslashit ( LURM_URL . 'assets/' ) );

        define( 'LURM_VERSION', self::VERSION );

        /**
         * Text Domain
         */
        define( 'LURM_TEXT_DOMAIN', 'learndash-user-role-modifier' );
    }

    /**
     * Plugin requiered files
     */
    public function includes() {
    }

    /**
     * Plugin Hooks
     */
    public function hooks() {
        add_filter( 'learndash_settings_fields', [ $this, 'lurm_add_metabox_in_group_setting' ] ,30,2 );
        add_action( 'save_post', [ $this, 'lurm_save_custom_user_role_data' ], 30, 3 );
        add_action( 'ld_added_group_access', [ $this, 'lurm_update_user_role' ], 10, 2 );
    }

    /**
     * update user role
     */
    public function lurm_update_user_role( $user_id, $group_id ) {

        $updated_data = get_post_meta( $group_id, 'lurm_custom_settings', true );
        $upated_role = isset( $updated_data['user_role'] ) ? $updated_data['user_role'] : '';
        $user = get_userdata( $user_id );

        $is_option_enabled = isset( $updated_data['option'] ) ? $updated_data['option'] : '';

        if ( $user && ! empty( $upated_role ) && array_key_exists( $upated_role, wp_roles()->roles ) && 'on' == $is_option_enabled ) {

            $user->add_role( $upated_role );
        }
    }

    /**
     * save custom role data
     */
    public function lurm_save_custom_user_role_data( $post_id = 0, $post = null, $update = false ) {

        if ( isset( $_POST['learndash-group-access-settings']['lurm_user_role_option_enabled'] ) ) {

            $custom_user_role_option = isset( $_POST['learndash-group-access-settings']['lurm_user_role_option_enabled'] ) ? esc_attr( $_POST['learndash-group-access-settings']['lurm_user_role_option_enabled'] ) : '';

            $custom_user_role = isset( $_POST['learndash-group-access-settings']['lurm_user_role_custom'] ) ? esc_attr( $_POST['learndash-group-access-settings']['lurm_user_role_custom'] ) : '';
            $role_name = ucwords( $custom_user_role );
            $custom_user_role = str_replace( ' ', '_', $custom_user_role );
            $custom_user_role = strtolower( $custom_user_role );

            if( 'on' == $custom_user_role_option && $custom_user_role ) {

                $data = [];
                $data['option'] = $custom_user_role_option;
                $data['user_role'] = $custom_user_role;
                update_post_meta( $post_id, 'lurm_custom_settings', $data );

                add_role( $custom_user_role, $role_name, array(
                    'view_ticket' => true,
                    'close_ticket' => true,
                    'reply_ticket' => true,
                    'create_ticket' => true,
                    'attach_files' => true,
                    'vc_access_rules_frontend_editor' => true,
                    'vc_access_rules_post_settings' => true,
                    'vc_access_rules_settings' => true,
                    'vc_access_rules_templates' => true,
                    'vc_access_rules_shortcodes' => true,
                    'read' => true,
                    'propanel_widgets' => false,
                ) );
            }
        } else {

            $updated_data = get_post_meta( $post_id, 'lurm_custom_settings', true );
            $updated_role = isset( $updated_data['user_role'] ) ? $updated_data['user_role'] : '';
            $data = [];
            $data['option'] = 'off';
            $data['user_role'] = $updated_role;
            update_post_meta( $post_id, 'lurm_custom_settings', $data );
        }
    }

    /**
     * group setting
     */
    public function lurm_add_metabox_in_group_setting( $setting_option_fields = array(), $settings_metabox_key = '' ) {

        if( 'learndash-group-access-settings' == $settings_metabox_key ) {
            
            $post_id = get_the_ID();
            $custom_settings = get_post_meta( $post_id, 'lurm_custom_settings', true );

            $lurm_custom_option = isset( $custom_settings['option'] ) ? $custom_settings['option'] : '';
            
            $lurm_custom_role = isset( $custom_settings['user_role'] ) ? $custom_settings['user_role'] : '';
            
            $lurm_custom_role = str_replace( '_', ' ', $lurm_custom_role );
            $lurm_custom_role = ucwords( $lurm_custom_role );

            $open = false;
            $child_state = 'closed';

            if( 'on' == $lurm_custom_option ) {
                $open = true;
                $child_state = 'open';
            }

            $setting_option_fields['lurm_user_role_option_enabled'] = array(
                'name'                => 'lurm_user_role_option_enabled',
                'label'               => 'User Role Option',
                'type'                => 'checkbox-switch',
                'value'               => $open,
                'default'             => '',
                'options'             => array(
                    ''       => 'Allow different user role to students',
                    // 'CUSTOM' => '',
                ),
                'help_text'           => 'Allow different user role to students.',
                'child_section_state' => $child_state,
                'rest'                => array(
                    'show_in_rest' => true,
                    'rest_args' => array(
                        'schema' => array(
                            'field_key'   => 'custom_user_role',
                            'description' => 'Custom User Role',
                            'type'        => 'string',
                            'default'     => '',
                        ),
                    ),
                ),
            );

            $setting_option_fields['lurm_user_role_custom'] = array(
                'name'                => 'lurm_user_role_custom',
                'label'               => 'Custom User Role',
                'type'                => 'text',
                'value'               => $lurm_custom_role,
                'default'             => '',
                'attrs'               => array(
                    'step' => 1,
                    'min'  => 0,
                ),
                'class'               => 'small-text',
                'parent_setting'      => 'lurm_user_role_option_enabled',
                'rest'                => array(
                    'show_in_rest' => true,
                    'rest_args' => array(
                        'schema' => array(
                            'field_key'   => 'courses_per_page_custom',
                            'description' => 'Custom User Role',
                            'type'        => 'string',
                            'default'     => '',
                        ),
                    ),
                ),
            );
        }

        return $setting_option_fields;
    }
}

/**
 * Display admin notifications if dependency not found.
 */
function lurm_ready() {

    if( !is_admin() ) {
        return;
    }

    if( ! class_exists( 'SFWD_LMS' ) ) {
        deactivate_plugins ( plugin_basename ( __FILE__ ), true );
        $class = 'notice is-dismissible error';
        $message = __( 'LearnDash User Role Modifier add-on requires LearnDash plugin is to be activated', 'learndash-user-role-modifier' );
        printf ( '<div id="message" class="%s"> <p>%s</p></div>', $class, $message );
    }
}

/**
 * @return bool
 */
function LURM() {
    if ( ! class_exists( 'SFWD_LMS' ) ) {
        add_action( 'admin_notices', 'lurm_ready' );
        return false;
    }

    return LearnDash_User_Role_Modifier::instance();
}
add_action( 'plugins_loaded', 'LURM' );