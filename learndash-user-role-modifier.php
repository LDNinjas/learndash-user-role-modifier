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
        add_action( 'ld_added_group_access', [ $this, 'lurm_update_user_role' ], 10, 2 );
        add_filter( 'learndash_header_tab_menu', [ $this, 'lurm_add_custom_tabs' ], 10, 3 );
        add_action( 'add_meta_boxes', [ $this, 'lurm_add_metabox' ], 10, 2 );
        add_action( 'admin_enqueue_scripts', [ $this, 'lurm_enqueue_scripts' ] );
        add_action( 'wp_ajax_create_user_role', [ $this, 'lurm_create_user_role' ] );
        add_action( 'wp_ajax_delete_user_role', [ $this, 'lurm_delete_user_role' ] );
        add_action( 'wp_ajax_update_status', [ $this, 'lurm_update_status' ] );
        add_action( 'ld_removed_group_access', [ $this, 'lurm_remove_user_role' ], 10, 2 );
    }

    /**
     * Remove user role
     */
    public function lurm_remove_user_role( $user_id, $group_id ) {

        global $wpdb;

        $meta_key = $wpdb->base_prefix.'capabilities';

        $get_group_selected_role = get_post_meta( $group_id, 'lurm_custom_settings', true );   

        if( $get_group_selected_role ) {

            $user_role_name = isset( $get_group_selected_role['user_role'] ) ? $get_group_selected_role['user_role'] : '';
            
            if( $user_role_name ) {

                $custom_user_role = str_replace( ' ', '_', $user_role_name );
                $keyToRemove = strtolower( $custom_user_role );

                $get_capabilities = get_user_meta( $user_id, $meta_key, true );
                
                if ( array_key_exists( $keyToRemove, $get_capabilities ) ) {

                    $user = get_user_by( 'id', $user_id );
                    $user->remove_role( $keyToRemove );
                }
            }
        }
    }

    /**
     * update group status
     */
    public function lurm_update_status() {

        $group_id = isset( $_POST['group_id'] ) ? $_POST['group_id'] : 0;

        if( ! $group_id ) {
            wp_die();
        }

        $get_updated_data = get_post_meta( $group_id, 'lurm_custom_settings', true );

        if( $get_updated_data ) {

            $get_updated_data['option'] = 'false';
            update_post_meta( $group_id, 'lurm_custom_settings', $get_updated_data );
        }

        wp_die();
    }

    /**
     * delete role
     */
    public function lurm_delete_user_role() {

        $role_name = isset( $_POST['role_name'] ) ? $_POST['role_name'] : '';

        if( $role_name ) {

            $custom_user_role = str_replace( ' ', '_', $role_name );
            $custom_user_role = strtolower( $custom_user_role );
            remove_role( $custom_user_role );
        }

        wp_die();
    }

    /**
     * create user role
     */
    public function lurm_create_user_role() {

        $role_name = isset( $_POST['role_name'] ) ? $_POST['role_name'] : '';
        $is_checked = isset( $_POST['is_checked'] ) ? $_POST['is_checked'] : '';
        $selected_option = isset( $_POST['selected_option'] ) ? $_POST['selected_option'] : '';
        $group_id = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : 0;

        if( ! $group_id ) {
            wp_die();
        }

        if( 'true' == $is_checked ) {

            $role_name = ucwords( $role_name );
            
            $custom_user_role = str_replace( ' ', '_', $role_name );
            $custom_user_role = strtolower( $custom_user_role );
            
            if( 'Any Other' == $selected_option ) {

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
                    'read'                          => true,
                    'propanel_widgets'      => false,
                    'lurm_user_role'        => true
                ) );
                
            } else {
                $role_name = $selected_option;
            }

            $data = [];
            $data['option'] = $is_checked;
            $data['user_role'] = $role_name;
            update_post_meta( $group_id, 'lurm_custom_settings', $data );
        }

        wp_die();
    }

    /**
     * enqueue scripts
     */
    public function lurm_enqueue_scripts() {

        $rand = rand( 1, 99999999999 );

        wp_enqueue_style( 'lurm-frontend-css', LURM_ASSETS_URL . 'css/lurm-frontend.css', [], $rand, null );
        wp_enqueue_script( 'lurm-backend-js', LURM_ASSETS_URL . 'js/lurm-backend.js', [ 'jquery' ], $rand, true );
        wp_localize_script( 'lurm-backend-js', 'LURM', [
            'ajaxURL'       => admin_url( 'admin-ajax.php' ),
            'baseURL'       => get_permalink(),
        ] );
    }

    /**
     * add metabox
     */
    public function lurm_add_metabox( $post_type, $post ) {

        add_meta_box(

            'lurm-user-role-id',
            'Custom User Role Content',
            [ $this, 'lurm_metabox_content' ],
            $post_type,
            'advanced',
            'high'
        );
    }

    /**
     * metabox callback
     */
    public function lurm_metabox_content() {
        
        global $wp_roles;
        $roles = $wp_roles->roles;
        $role_names = array_column( $roles, 'name' );
        
        $group_id = get_the_ID();
        $get_updated_data = get_post_meta( $group_id, 'lurm_custom_settings', true );

        $option_is_enabled = isset( $get_updated_data['option'] ) ? $get_updated_data['option'] : '';

        // $custom_role = get_option( 'lurm-role-name' );

        $selected_role = __( 'Select a role', LURM_TEXT_DOMAIN );
        $lurm_checked = '';
        $display = '';

        if( 'true' == $option_is_enabled ) {
            
            $selected_role = isset( $get_updated_data['user_role'] ) ? $get_updated_data['user_role'] : '';

            if( ! in_array( $selected_role, $role_names ) ) {
                $selected_role = __( 'Select a role', LURM_TEXT_DOMAIN );
            }
            
            $lurm_checked = 'checked';
            $display = 'block';
        }
        ?>
        <div class="lurm-main-wrapper">
            <div class="lurm-inner-wrapper">
                <label class="switch">
                  <input type="checkbox" class="lurm-checkbox" <?php echo $lurm_checked; ?>>
                  <span class="slider round"></span>
                </label>
            </div>
            <div class="lurm-role-dropdown-wrapper" style="display: <?php echo $display; ?>;"> 
                <div class="lurm-role-dropdown-header">
                    <div class="lurm-select-text-wrap"><?php echo $selected_role; ?></div>
                    <div class="dashicons dashicons-arrow-down-alt2 lurm-role-down-arrow"></div>        
                </div>
                <div class="lurm-group-dropdown-content">
                    <div class="lurm-inner-wrap">
                        <?php 
                        if( ! empty( $role_names ) && is_array( $role_names ) ) {
                            ?>
                            <div class="lurm-select-role-text lurm-role-option">
                                <?php echo __( 'Any Other', LURM_TEXT_DOMAIN ); ?>
                            </div>
                            <?php
                            foreach( $role_names as $role_name ) {

                                if( 'Administrator' == $role_name ) {
                                    continue;
                                }

                                $role_key = str_replace( ' ', '_', $role_name );
                                $role_key = strtolower( $role_key );
                                $get_role = get_role( $role_key );
 
                                if( $get_role ) {

                                    $has_key = isset( $get_role->capabilities['lurm_user_role'] ) ? $get_role->capabilities['lurm_user_role'] : '';

                                    if( $has_key ) {
                                        ?>
                                        <div class="lurm-child-wrapper">
                                            <div class="lurm-role-option" style="width: 85%;" data-role_key="<?php echo $role_key; ?>"><?php echo $role_name ; ?></div>
                                            <div class="lurm-trash dashicons dashicons-trash"></div>
                                        </div>
                                        <?php
                                    } else {

                                        ?>
                                        <div class="lurm-child-wrapper">
                                            <div class="lurm-role-option" style="width: 100%;" data-role_key="<?php echo $role_key; ?>"><?php echo $role_name ; ?></div>
                                        </div>
                                        <?php
                                    }
                                }           
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="lurm-role-text-field">
                <p><input type="text" placeholder="<?php echo __( 'Enter role name', LURM_TEXT_DOMAIN ); ?>"></p>
                <button data_group-id="<?php echo $group_id; ?>"><?php echo __( 'Create Role', LURM_TEXT_DOMAIN ); ?></button>
            </div>
            <div class="mld-lurm-update-group-status">
                <button data_group-id="<?php echo $group_id; ?>"><?php echo __( 'Update', LURM_TEXT_DOMAIN ); ?></button> 
            </div>
        </div>
        <?php
    } 

    /**
     * added new tab
     */
    public function lurm_add_custom_tabs( $header_tabs_data, $menu_tab_key, $screen_post_type ) {

        $screen = get_current_screen();
        
        if( $screen && 'post' != $screen->base ) {
            return $header_tabs_data;
        }

        if( $screen_post_type && 'groups' == $screen_post_type ) {

            $header_tabs_data[] = [
                'id'        => 'lurm_tab_id',
                'name'      => 'User Role Tab',
                'metaboxes' => ['lurm-user-role-id']
            ];
        }

        return $header_tabs_data;
    }

    /**
     * update user role
     */
    public function lurm_update_user_role( $user_id, $group_id ) {

        global $wp_roles;
        $roles = $wp_roles->roles;

        $role_names = array_column( $roles, 'name' );
        $updated_data = get_post_meta( $group_id, 'lurm_custom_settings', true );
        $upated_role = isset( $updated_data['user_role'] ) ? $updated_data['user_role'] : '';
        $custom_user_role = str_replace( ' ', '_', $upated_role );
        $custom_user_role = strtolower( $custom_user_role );
        $user = get_userdata( $user_id );

        $is_option_enabled = isset( $updated_data['option'] ) ? $updated_data['option'] : '';

        if ( $user && ! empty( $custom_user_role ) && array_key_exists( $custom_user_role, wp_roles()->roles ) && 'true' == $is_option_enabled ) {

            if( in_array( $upated_role , $role_names ) ) {
                $user->add_role( $custom_user_role );
            }
        }
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