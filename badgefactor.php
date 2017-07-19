<?php
/**
 * Plugin Name: Badge Factor
 * Plugin URI: https://github.com/DigitalPygmalion/badge-factor
 * GitHub Plugin URI: mediaformat/badge-factor
 * Description: Badge Factor is a "glue" plugin which brings together many different plugins in order to deliver a comprehensive open badge solution.
 * Author: Digital Pygmalion
 * Version: 1.0.1
 * Author URI: http://digitalpygmalion.com/
 * License: MIT
 * Text Domain: badgefactor
 * Domain Path: /languages
 */

/*
 * Copyright (c) 2017 Digital Pygmalion
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of
 * the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
 * THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */


class BadgeFactor
{

    /**
     * BadgeFactor Version
     *
     * @var string
     */
    public static $version = '1.0.0';


    /**
     * Holds any blocker error messages stopping plugin running
     *
     * @var array
     *
     * @since 1.0.0
     */
    private $notices = array();


    /**
     * The plugin's required WordPress version
     *
     * @var string
     *
     * @since 1.0.0
     */
    public $required_wp_version = '4.7.2';


    /**
     * The plugin's required Gravity Forms version
     *
     * @var string
     *
     * @since 4.0
     */
    public $required_gf_version = '1.9';


    /**
     * BadgeFactor constructor.
     */
    function __construct()
    {
        // Plugin constants
        $this->basename = plugin_basename(__FILE__);
        $this->directory_path = plugin_dir_path(__FILE__);
        $this->directory_url = plugin_dir_url(__FILE__);

        // Load translations
        load_plugin_textdomain('badgefactor', false, basename( dirname( __FILE__ ) ).'/languages');

        // Activation / deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Hook declaration
        add_action( 'init', array($this, 'create_cpt_organisation'));
        add_action( 'init', array($this, 'create_cpt_badge'));
        add_action( 'init', array($this, 'update_cpt_submission'));
        add_action( 'init', array($this, 'add_member_badges_page'));

        add_action( 'publish_badges', array($this, 'create_badge_chain'), 10, 2);
        add_action( 'wp_trash_post',  array($this, 'trash_badge_chain'), 10, 1);
        add_action( 'admin_menu',     array($this, 'badgefactor_menu'), 99);
        add_action( 'add_meta_boxes', array($this, 'add_metabox'), 99);
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
        add_action( 'admin_init',     array($this, 'register_badgefactor_settings' ));
        add_action( 'template_redirect',  array($this, 'parse_member_badge_request' ));
        add_filter( 'acf/load_field/key=field_57ab18ef7b1d2', array($this, 'generate_useful_links'), 10, 1);
        add_filter( 'single_template',  array($this, 'locate_single_templates'));
        add_filter( 'archive_template', array($this, 'locate_archive_templates'), 10, 1);


	    add_action( 'wp_enqueue_scripts', array($this, 'badgefactor_scripts') );
	    add_action( 'wp_ajax_nopriv_toggle-private-status', array($this, 'ajax_toggle_private_status') );
	    add_action( 'wp_ajax_toggle-private-status', array($this, 'ajax_toggle_private_status') );

        add_theme_support('post-thumbnails');
        add_image_size('square-140', 140, 140, false);
        add_image_size('square-225', 225, 225, false);
        add_image_size('square-450', 450, 450, false);

    }

    ///////////////////////////////////////////////////////////////////////////
    //                                 HOOKS                                 //
    ///////////////////////////////////////////////////////////////////////////

    /**
     * BadgeFactor plugin activation hook.
     */
    public function activate()
    {
        // Setup default Credly options
        $credly_settings = (array) get_option( 'credly_settings', array() );


        $credly_settings['credly_enable']                      = 'false';
        $credly_settings['credly_badge_title']                 = 'post_title';
        $credly_settings['credly_badge_description']           = 'post_body';
        $credly_settings['credly_badge_short_description']     = 'post_excerpt';
        $credly_settings['credly_badge_criteria']              = '';
        $credly_settings['credly_badge_image']                 = 'featured_image';
        $credly_settings['credly_badge_testimonial']           = 'congratulations_text';
        $credly_settings['credly_badge_evidence']              = 'permalink';
        $credly_settings['credly_badge_sendemail_add_message'] = 'false';
        update_option( 'credly_settings', $credly_settings );

        flush_rewrite_rules();
    }


    /**
     * BadgeFactor plugin deactivation hook.
     */
    public function deactivate()
    {

    }

    function add_metabox()
    {
        foreach ( badgeos_get_achievement_types_slugs() as $achievement_type ) {
            remove_meta_box('badgeos_credly_details_meta_box', $achievement_type, 'advanced');
        }

    }

    /**
     * admin_menu hook.
     */
    public function badgefactor_menu()
    {
        $minimum_role = badgeos_get_manager_capability();

        // Create main menu
        remove_submenu_page('badgeos_badgeos', 'badgeos_sub_credly_integration');
        remove_submenu_page('badgeos_badgeos', 'badgeos_settings');
        remove_submenu_page('badgeos_badgeos', 'badgeos_sub_add_ons');
        remove_submenu_page('badgeos_badgeos', 'badgeos_sub_help_support');
        remove_submenu_page('badgeos_badgeos', 'open-badges-issuer');
        remove_menu_page('badgeos_badgeos');
        add_menu_page( 'Badge Factor', 'Badge Factor', $minimum_role, 'badgeos_badgeos', 'badgeos_settings', $this->directory_url . 'images/badgefactor_icon.png', 110 );
        add_submenu_page('badgeos_badgeos', __('Badge Factor Options', 'badgefactor'), __('Options', 'badgefactor'), 'manage_options', 'badgefactor', array($this, 'badgefactor_options'));
    }

    /**
     * add_options_page hook.
     */
    public function badgefactor_options()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        include('settings-page.tpl.php');
    }


    /**
     * Check if Gravity Forms version is compatible
     *
     * @return boolean Whether compatible or not
     *
     * @since 1.0.0
     */
    public function check_gravity_forms() {

        /* Gravity Forms version not compatible */
        if ( ! class_exists( 'GFCommon' ) || ! version_compare( GFCommon::$version, $this->required_gf_version, '>=' ) ) {
            $this->notices[] = sprintf( esc_html__( '%sGravity Forms%s Version %s is required. %sGet more info%s.', 'gravity-forms-pdf-extended' ), '<a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=235154">', '</a>', $this->required_gf_version, '<a href="https://gravitypdf.com/documentation/v4/user-activation-errors/#gravityforms-version">', '</a>' );

            return false;
        }

        return true;
    }


    function display_notices() {
        ?>
        <div class="error">
            <p><strong><?php esc_html_e( 'Badge Factor Installation Problem', 'badgefactor' ); ?></strong></p>

            <p><?php esc_html_e( 'The minimum requirements for Badge Factor have not been met. Please fix the issue(s) below to continue:', 'badgefactor' ); ?></p>
            <ul style="padding-bottom: 0.5em">
                <?php foreach ( $this->notices as $notice ) : ?>
                    <li style="padding-left: 20px;list-style: inside"><?php echo $notice; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }


    /**
     * Check if WordPress version is compatible
     *
     * @return boolean Whether compatible or not
     *
     * @since 1.0.0
     */
    public function is_compatible_wordpress_version() {
        global $wp_version;

        /* WordPress version not compatible */
        if ( ! version_compare( $wp_version, $this->required_wp_version, '>=' ) ) {
            $this->notices[] = sprintf( esc_html__( 'WordPress Version %s is required. %sGet more info%s.', 'gravity-forms-pdf-extended' ), $this->required_wp_version, '<a href="https://gravitypdf.com/documentation/v4/user-activation-errors/#wordpress-version">', '</a>' );

            return false;
        }

        return true;
    }


    function plugins_loaded()
    {
        /* Check minimum requirements are met */
        $this->is_compatible_wordpress_version();

        /* Check if any errors were thrown, enqueue them and exit early */
        if ( sizeof( $this->notices ) > 0 ) {
            add_action( 'admin_notices', array( $this, 'display_notices' ) );
            // FIXME deactivate_plugins( plugin_basename( __FILE__ ) );

            if ( isset( $_GET['activate'] ) )
            {
                unset( $_GET['activate'] );
            }

            return null;
        }
    }

    /**
     * admin_init hook.
     */
    function register_badgefactor_settings()
    {

        //register our settings
        register_setting( 'badgefactor-settings-group', 'badgefactor_form_page_text' );
        register_setting( 'badgefactor-settings-group', 'badgefactor_default_form_button_text' );
        register_setting( 'badgefactor-settings-group', 'badgefactor_default_certificate_name' );
        register_setting( 'badgefactor-settings-group', 'badgefactor_member_page_id' );
    }


    public function generate_useful_links( $field )
    {
        global $post;
        $post_id = $post->ID;
        $message = "";

        if ($this->check_gravity_forms())
        {
            $form_page_id = get_post_meta($post_id, 'badgefactor_form_page_id', true);
            if ($form_page_id !== '' && is_numeric($form_page_id) )
            {
                $message .= "<a target='_blank' href='".get_admin_url()."post.php?post={$form_page_id}&action=edit'>".__('Form Page', 'badgefactor')."</a><br/>";
            }
            else
            {
                $message .= __("Form Page not linked!", 'badgefactor')."<br/>";
            }

            $form_id = get_post_meta($post_id, 'badgefactor_form_id', true);
            if ($form_id !== '' && is_numeric($form_id) )
            {
                $message .= "<a target='_blank' href='".get_admin_url()."admin.php?page=gf_edit_forms&id={$form_id}'>".__('Form', 'badgefactor')."</a><br/>";
            }
            else
            {
                $message .= __("Form not linked!", 'badgefactor')."<br/>";
            }

        } else {
            $message .= __("GravityForms not used.", 'badgefactor')."<br/>";
        }

        $page_id = get_post_meta($post_id, 'badgefactor_page_id', true);
        if ($page_id !== '' && is_numeric($page_id) )
        {
            $message .= "<a target='_blank' href='".get_admin_url()."post.php?post={$page_id}&action=edit'>".__('Description Page', 'badgefactor')."</a>";
        }
        else
        {
            $message .= __("Description Page not linked!", 'badgefactor')."<br/>";
        }

        $field['message'] = $message;
        
        return $field;
    }


    /**
     * init hook to update the 'Submission' custom post type.
     */
    public function update_cpt_submission()
    {
        if( function_exists('register_field_group') ):

            register_field_group(array (
                'id' => 'badge_factor_submission_settings',
                'title' => __('Submission Settings', 'badgefactor'),
                'fields' => array (
                    array (
                        'key' => 'field_57a0a3587cee0',
                        'label' => __('Public', 'badgefactor'),
                        'name' => 'public',
                        'type' => 'true_false',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'message' => '',
                        'default_value' => 0,
                    ),
                ),
                'location' => array (
                    array (
                        array (
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'submission',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'options' => array (
                    'position' => 'side',
                    'layout' => 'default',
                    'label_placement' => 'top',
                    'instruction_placement' => 'label',
                    'hide_on_screen' => array(),
                    'active' => 1,
                    'description' => '',
                ),
            ));

        endif;
    }

    /**
     * publish_badges hook to create all the required posts (badge, page,
     * etc.) associated with a published badge.
     * @param $ID int Post ID
     * @param $post WP_Post Badge post
     * @return bool|WP_Error
     */
    public function create_badge_chain($ID, $post)
    {
        $form_page_id = 0;
        if ($post->post_type == 'badges')
        {
            if (get_post_meta( $ID, 'badgefactor_form_id', true) == '')
            {
                if ($this->check_gravity_forms())
                {
                    $form_id = $this->create_badge_submission_form($post);
                    if (!is_wp_error($form_id))
                    {
                        update_post_meta( $ID, 'badgefactor_form_id', $form_id );

                        if (get_post_meta( $ID, 'badgefactor_form_page_id', true) == '')
                        {
                            $form_page_id = $this->create_badge_form_page($post->post_title, $form_id);
                            if (!is_wp_error($form_page_id))
                            {
                                update_post_meta($ID, 'badgefactor_form_page_id', $form_page_id);
                            }
                        }
                    }
                }
            }

            if (get_post_meta( $ID, 'badgefactor_page_id', true) == '')
            {
                $page_id = $this->create_course_page($post, '<a href="'.get_permalink($form_page_id).'">'.__('Get this badge', 'badgefactor').'</a>');
                if (!is_wp_error($page_id))
                {
                    update_post_meta($ID, 'badgefactor_page_id', $page_id);
                }
                wp_update_post(array('ID' => $form_page_id, 'post_parent' => $page_id));
            }

            return TRUE;
        }
        return new WP_Error('invalid', __("Badge is not of valid post type!", 'badgefactor'));
    }

    /**
     * trash_badges hook to trash all posts associated with a trashed badge
     * @param $post_id int Post ID
     * @return bool|WP_Error
     */
    public function trash_badge_chain($post_id)
    {
        $post = get_post($post_id);
        if ($post !== NULL && $post->post_type == 'badges')
        {
            if ($this->check_gravity_forms())
            {
                $form_page_id = get_post_meta($post_id, 'badgefactor_form_page_id', true);
                if ($form_page_id !== '' && is_numeric($form_page_id) )
                {
                    $form_page_trashed = wp_trash_post((int)$form_page_id);
                    if (!$form_page_trashed) return new WP_Error('error', __("Cannot trash form page associated to badge!", 'badgefactor'));
                }

                $form_id = get_post_meta($post_id, 'badgefactor_form_id', true);
                if ($form_id !== '' && is_numeric($form_id) )
                {

                    $form_trashed = GFAPI::delete_form((int)$form_id);
                    if (!$form_trashed) return new WP_Error('error', __("Cannot trash form associated to badge!", 'badgefactor'));
                }
            }

            $page_id = get_post_meta($post_id, 'badgefactor_page_id', true);
            if ($page_id !== '' && is_numeric($page_id) )
            {
                $page_trashed = wp_trash_post((int)$page_id);
                if (!$page_trashed) return new WP_Error('error', __("Cannot trash page associated to badge!", 'badgefactor'));
            }
        }
        return TRUE;
    }

    /**
     * init hook to create the 'badge' achievement custom post type (if it
     * doesn't exist yet).
     */
    public function create_cpt_badge()
    {
        // Register the post type
        register_post_type( 'badges', array(
            'labels'             => array(
                'name'               => __( 'Badges', 'badgefactor'),
                'singular_name'      => __( 'Badge', 'badgefactor'),
                'add_new'            => __( 'Add New', 'badgefactor' ),
                'add_new_item'       => __( 'Add New Badge', 'badgefactor' ),
                'edit_item'          => __( 'Edit Badge', 'badgefactor' ),
                'new_item'           => __( 'New Badge', 'badgefactor' ),
                'all_items'          => __( 'Badges', 'badgefactor'),
                'view_item'          => __( 'View Badges', 'badgefactor' ),
                'search_items'       => __( 'Search Badges', 'badgefactor' ),
                'not_found'          => __( 'No Badge found', 'badgefactor' ),
                'not_found_in_trash' => __( 'No Badge found in Trash', 'badgefactor' ),
                'parent_item_colon'  => '',
                'menu_name'          => 'Badges',
            ),
            'rewrite' => array(
                'slug' => 'badges',
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => current_user_can( badgeos_get_manager_capability() ),
            'show_in_menu'       => 'badgeos_badgeos',
            'query_var'          => true,
            'capability_type'    => 'post',
            'has_archive'        => 'badges',
            'hierarchical'       => true,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'page-attributes' )
        ) );

        // Register the Achievement type
        badgeos_register_achievement_type( strtolower( 'Badge' ), strtolower( 'Badges' ) );

        register_taxonomy(
            'badge_category',
            'badge',
            array(
                'label' => __( 'Category', 'badgefactor'),
                'capabilities' => array(
                    'assign_terms' => 'edit_guides',
                    'edit_terms' => 'publish_guides',
                )
            )
        );

        if( function_exists('register_field_group') ):

            register_field_group(array (
                'id' => 'badge_factor_settings',
                'title' => 'Badge Factor',
                'fields' => array (
                    array (
                        'key' => 'field_579f78d2049',
                        'label' => __('Organisation', 'badgefactor'),
                        'name' => 'organisation',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'post_type' => array (
                            0 => 'organisation',
                        ),
                        'taxonomy' => array (
                        ),
                        'allow_null' => 0,
                        'multiple' => 0,
                        'return_format' => 'object',
                        'ui' => 1,
                    ),
                    array (
                        'key' => 'field_579f81358c890',
                        'label' => __('Category', 'badgefactor'),
                        'name' => 'category',
                        'type' => 'taxonomy',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'taxonomy' => 'badge_category',
                        'field_type' => 'checkbox',
                        'allow_null' => 0,
                        'add_term' => 1,
                        'save_terms' => 0,
                        'load_terms' => 0,
                        'return_format' => 'id',
                        'multiple' => 0,
                    ),
                    array (
                        'key' => 'field_579f856ba98ce',
                        'label' => __('Endorsed by', 'badgefactor'),
                        'name' => 'endorsed_by',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'post_type' => array (
                            0 => 'organisation',
                        ),
                        'taxonomy' => array (
                        ),
                        'allow_null' => 1,
                        'multiple' => 1,
                        'return_format' => 'object',
                        'ui' => 1,
                    ),
                    array (
                        'key' => 'field_57ab18ef7b1d2',
                        'label' => __('Useful links', 'badgefactor'),
                        'name' => 'useful_links',
                        'type' => 'message',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'message' => '',
                        'new_lines' => '',
                        'esc_html' => 0,
                    ),
                    array (
                        'key' => 'field_584acafa654f8',
                        'label' => 'Date de fin',
                        'name' => 'end_date',
                        'type' => 'date_picker',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'display_format' => 'd/m/Y',
                        'return_format' => 'd F Y',
                        'first_day' => 1,
                    ),
                ),
                'location' => array (
                    array (
                        array (
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'badges',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'options' => array (
                    'position' => 'side',
                    'layout' => 'default',
                    'label_placement' => 'top',
                    'instruction_placement' => 'label',
                    'hide_on_screen' => array(),
                    'active' => 1,
                    'description' => '',
                ),
            ));

        endif;

        if( function_exists('register_field_group') ):

            register_field_group(array (
                'id' => 'badgefactor_criteria',
                'title' => __('Badge Criteria', 'badgefactor'),
                'fields' => array (
                    array (
                        'key' => 'field_56a8fb6f1e8dd',
                        'label' => __('Section Title', 'badgefactor'),
                        'name' => 'badge_criteria_title',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => __('Criteria', 'badgefactor'),
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                        'readonly' => 0,
                        'disabled' => 0,
                    ),
                    array (
                        'key' => 'field_56a8ec024a013',
                        'label' => __('Criteria', 'badgefactor'),
                        'name' => 'badge_criteria',
                        'type' => 'wysiwyg',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                    ),
                ),
                'location' => array (
                    array (
                        array (
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'badges',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'options' => array(
                    'position' => 'normal',
                    'layout' => 'default',
                    'label_placement' => 'top',
                    'instruction_placement' => 'label',
                    'hide_on_screen' => array(),
                    'active' => 1,
                    'description' => '',
                )
            ));

        endif;

        flush_rewrite_rules();

    }

    /**
     * init hook to create an 'organisation' custom post type.
     */
    public function create_cpt_organisation()
    {
        register_post_type(
            'organisation',
            array(
                'labels' => array(
                    'name' => __( 'Organisations', 'badgefactor'),
                    'singular_name' => __( 'Organisation', 'badgefactor' )
                ),
                'rewrite' => array(
                    'slug' => 'organisations',
                ),
                'capability_type'  => 'post',
                'has_archive'      => 'organisations',
                'public'           => true,
                'show_in_menu' => 'badgeos_badgeos',
            )
        );

        if( function_exists('register_field_group') )
        {

            register_field_group(array (
                'id'    => 'organisation_fields',
                'title' => __('Organisation Fields', 'badgefactor'),
                'fields' => array (
                    array (
                        'key' => 'field_57dfe078ccfc4',
                        'label' => 'Image',
                        'name' => 'image',
                        'type' => 'image',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'return_format' => 'id',
                        'preview_size' => 'square-225',
                        'library' => 'all',
                        'min_width' => 450,
                        'min_height' => 450,
                        'min_size' => '',
                        'max_width' => 450,
                        'max_height' => 450,
                        'max_size' => '',
                        'mime_types' => '',
                    ),
                    array (
                        'key' => 'field_57dfe4fed12cb',
                        'label' => 'Twitter link',
                        'name' => 'twitter_link',
                        'type' => 'url',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                    ),
                    array (
                        'key' => 'field_57dfe520d12cc',
                        'label' => 'LinkedIn Link',
                        'name' => 'linkedin_link',
                        'type' => 'url',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                    ),
                    array (
                        'key' => 'field_57dfe536d12cd',
                        'label' => 'Facebook Link',
                        'name' => 'facebook_link',
                        'type' => 'url',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                    ),
                ),
                'location' => array (
                    array (
                        array (
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'organisation',
                        ),
                    ),
                ),
                'options' => array (
                    'position' => 'side',
                    'layout' => 'default',
                    'label_placement' => 'top',
                    'instruction_placement' => 'label',
                    'hide_on_screen' => array(),
                    'active' => 1,
                    'description' => '',
                ),
                'menu_order' => 0,

            ));

        }

    }

    /**
     * Create Submission
     * @since  1.0.0
     * @param  integer $achievement_id The achievement ID intended for submission
     * @param  string  $title          The title of the post
     * @param  string  $content        The post content
     * @param  integer $user_id        The user ID
     * @return boolean                 Returns true if able to create form
     */
    function create_submission( $achievement_id  = 0, $title = '', $content = '', $user_id = 0 ) {

        $submission_data = array(
            'post_title'	=>	$title,
            'post_content'	=>	$content,
            'post_status'	=>	'publish',
            'post_author'	=>	$user_id,
            'post_type'		=>	'submission',
        );

        //insert the post into the database
        if ( $submission_id = wp_insert_post( $submission_data ) ) {
            // save the achievement ID related to the submission
            add_post_meta( $submission_id, '_badgeos_submission_achievement_id', $achievement_id );

            // Available action for other processes
            do_action( 'badgeos_save_submission', $submission_id );

            // Submission status workflow
            $status_args = array(
                'achievement_id' => $achievement_id,
                'user_id' => $user_id
            );

            $status = 'pending';

            // Check if submission is auto approved or not
            if ( badgeos_is_submission_auto_approved( $submission_id ) ) {
                $status = 'approved';

                $status_args[ 'auto' ] = true;
            }

            badgeos_set_submission_status( $submission_id, $status, $status_args );

            return $submission_id;

        } else {

            return false;

        }
    }

    public function add_member_badges_page()
    {
        // FIXME not working...
        add_rewrite_tag('%member%', '([^&]+)');
	    add_rewrite_tag('%badges%', '([^&]+)');
        add_rewrite_rule('^members/([^/]+)/badges/([^/]+)/?$','index.php?badges=$matches[2]&member=$matches[1]','top');
        flush_rewrite_rules();
    }

    public function parse_member_badge_request() {
        //echo get_single_template(); die;
	    if ( get_query_var( 'member' ) && get_query_var( 'badges' ) ) {
            add_filter('template_include', function() {
                return $this->directory_path . '/templates/single-badges.php';
            });
        }

    }

    ///////////////////////////////////////////////////////////////////////////
    //                            PRIVATE METHODS                            //
    ///////////////////////////////////////////////////////////////////////////

    /**
     * Creates a submission form (if it doesn't yet exist)
     * @param $post WP_Post Badge post
     * @internal param Form $title title
     * @return int|WP_Error ID of form created
     */
    private function create_badge_submission_form($post)
    {
        // Check if $post is a valid WP_Post object
        if (!($post instanceof WP_Post))
        {
            return new WP_Error( 'invalid', __( 'Invalid badge object', 'badgefactor' ) );
        }
        $title = $post->post_title;

        // Create form if it doesn't exist yet
        if (RGFormsModel::is_unique_title( $title ))
        {
            try {
                $form = array();
                $form['title'] = $title;
                $form['labelPlacement'] = 'top_label';
                $form['descriptionPlacement'] = 'below';
                $form['button'] = new GF_Field_Text(
                    array(
                        'text' => esc_attr( get_option('badgefactor_default_form_button_text', 'Send') ),
                    )
                );
                $form["version"] = "1.9.15.14";
                $form['fields'] = array();
                $form['fields'][0] = new GF_Field_Hidden(
                    array(
                        'label' => 'achievement_id',
                        'defaultValue' => $post->ID,
                        'id' => 1
                    )
                );
                $form['fields'][1] = new GF_Field_Text(
                    array(
                        'label' => 'Example',
                        'id' => 2
                    )
                );
                $form['confirmation'] = array(
                    'type' => 'message',
                    'message' => '',
                );


                $form_id = GFAPI::add_form($form);
                $pdf_settings = array(
                    'name' => $title.' PDF',
                    'template' => 'zadani',
                    'filename' => '{user:ID}-{form_id}-{entry_id}',
                    'save' => 'Yes'
                );
                $pdf = GPDFAPI::add_pdf($form_id, $pdf_settings);

                return $form_id;
            }
            catch (Exception $e) {
                return new WP_Error('error', $e->getMessage());
            }
        }
        return new WP_Error('error', __("Form name is not unique!", 'badgefactor'));
    }

    /**
     * Creates an empty badge submission form page
     * @param $title string Badge title
     * @param $form_id int GravityForm ID
     * @return int|WP_Error ID of page created
     */
    private function create_badge_form_page($title, $form_id)
    {
        global $user_ID;

        $form_page_text = html_entity_decode(get_option('badgefactor_form_page_text', '<h3>In order to get this badge, please submit the following.</h3>'));
        $page = array(
            'post_type' => 'page',
            'post_content' => $form_page_text .
                '[badgeos_gravityform_submission gravityform_id="'. $form_id . '"]',
            'post_author' => $user_ID,
            'post_status' => 'draft',
            'post_title' => $title . ' - Formulaire',
        );

        $post_id = wp_insert_post($page, true);

        if (is_wp_error($post_id))
        {
            return $post_id;
        }

        // Including WPML translation
        if (class_exists('SitePress')) {
            global $sitepress;
            global $wpdb;

            $default_lang = $sitepress->get_default_language();
            $trid = 1 + $wpdb->get_var( "SELECT MAX(trid) FROM {$wpdb->prefix}icl_translations" );
            $wpdb->insert($wpdb->prefix.'icl_translations', array( 'element_type' => 'post_page', 'element_id' => $post_id, 'trid' => $trid, 'language_code' => $default_lang ));
        }

        return $post_id;
    }

    /**
     * Creates an empty course page
     * @param $post WP_Post Badge post
     * @param null $content
     * @return int|WP_Error ID of page created
     */
    private function create_course_page($post, $content = null)
    {
        // Check if $post is a valid WP_Post object
        if (!($post instanceof WP_Post))
        {
            return new WP_Error( 'invalid', __( 'Invalid badge object', 'badgefactor' ) );
        }

        $title = $post->post_title;
        global $user_ID;

        $page = array(
            'post_type' => 'page',
            'post_content' => $content,
            'post_author' => $user_ID,
            'post_status' => 'draft',
            'post_title' => $title,
        );

        $post_id = wp_insert_post($page, true);

        if (is_wp_error($post_id))
        {
            return $post_id;
        }

        // Add necessary post metas to course page
        update_post_meta( $post_id, 'is_course_page', TRUE);

        // Including WPML translation
        if (class_exists('SitePress')) {
            global $sitepress;
            global $wpdb;

            $default_lang = $sitepress->get_default_language();
            $trid = 1 + $wpdb->get_var( "SELECT MAX(trid) FROM {$wpdb->prefix}icl_translations" );
            $wpdb->insert($wpdb->prefix.'icl_translations', array( 'element_type' => 'post_page', 'element_id' => $post_id, 'trid' => $trid, 'language_code' => $default_lang ));
        }

        return $post_id;
    }

    /**
     * Get the absolute path to this plugin directory
     */
    private function plugin_path()
    {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );

    }

    public function locate_archive_templates( $template )
    {
        global $post;

        if (is_post_type_archive('organisation'))
        {
            $template = $this->plugin_path() . '/templates/archive-organisation.php';
        }
        else if (is_post_type_archive('badges'))
        {
            $template = $this->plugin_path() . '/templates/archive-badges.php';
        }

        return $template;

    }

    public function locate_single_templates()
    {
        global $post;
        switch ($post->post_type)
        {
            case 'organisation':
                if(file_exists(get_template_directory() . '/templates/single-organisation.php')){
		    $template = get_template_directory() . '/templates/single-organisation.php';
		} else {
                    $template = $this->plugin_path() . '/templates/single-organisation.php';
		}
                break;
            case 'badges':
                if(file_exists(get_template_directory() . '/templates/single-badges.php')){
		    $template = get_template_directory() . '/templates/single-badges.php';
		} else {
                    $template = $this->plugin_path() . '/templates/single-badges.php';
		}
                break;
            default:
                $template = null;
        }

        return $template;

    }


    public function get_badges()
    {
        $query = new WP_Query([
            'post_status' => 'publish',
            'post_type' => 'badges',
            'posts_per_page' => -1,
        ]);

        return $query->get_posts();
    }

    public function get_badge($badge_id)
    {
        return get_post($badge_id);
    }

    public function get_badge_id_by_slug($badge_slug) {
        $args = array(
            'name'        => $badge_slug,
            'post_type'   => 'badges',
            'post_status' => 'publish',
            'numberposts' => 1
        );
        $badge = get_posts($args);
        return $badge ? $badge[0]->ID : false;
    }

    public function get_nb_badges()
    {
        return count($this->get_badges());
    }

    public function get_nb_badge_earners($badge_id)
    {
        return count($this->get_badge_earners($badge_id));

    }

    public function get_badge_earners($badge_id)
    {
        $result = FALSE;
        if (function_exists('badgeos_get_achievement_earners_list'))
        {
            $result = badgeos_get_achievement_earners($badge_id);
        }
        return $result;

    }

    public function get_badge_organisations($exclude_self = false)
    {
        global $post;
        $args = [
            'post_status' => 'publish',
            'post_type' => 'organisation',
            'posts_per_page' => -1,
        ];
        if ($exclude_self)
        {
            $args['post__not_in'] = array( $post->ID );
        }
        $query = new WP_Query($args);

        return $query->get_posts();

    }

    public function get_badge_page_url($badge_id)
    {
        return get_permalink(get_field('badgefactor_page_id', $badge_id));
    }

    public function get_badge_criteria_title($badge_id)
    {
        return get_field('badge_criteria_title', $badge_id);
    }

    public function get_badge_criteria($badge_id)
    {
        return get_field('badge_criteria', $badge_id);
    }

    public function get_badge_issuer_name($badge_id)
    {
        return get_post(get_post_meta($badge_id, 'organisation', true))->post_title;
    }

    public function get_badge_issuer_url($badge_id)
    {
        return get_post_permalink(get_post_meta($badge_id, 'organisation', true));
    }

    public function get_nb_badges_by_organisation($organisation_id)
    {
        return count($this->get_badges_by_organisation($organisation_id));
    }

    public function get_badges_by_organisation($organisation_id)
    {
        $query = new WP_Query([
            'post_status' => 'publish',
            'post_type' => 'badges',
            'meta_key'  => 'organisation',
            'meta_value' => $organisation_id,
            'posts_per_page' => -1,
        ]);

        return $query->get_posts();
    }



    /**
     * Display currently earned badges for author.
     * @param  integer $userID ID of the user whose achievements to display.
     * @return mixed           echo'd badge loop.
     */
    public function get_user_achievements ( $author_id ) {

        $achievements = array();

	    //Re-fetch our data if the transient has expired.
	    if ( false === ( $badges = get_transient( 'custom_badgeos_user_achievements' ) ) ) {
		    //Grab the user's current list of achievements, by ID
		    $ids = badgeos_get_user_earned_achievement_ids( $author_id );

		    $types = array();
		    foreach( $ids as $id ) :
			    //shuffle the badge type into its own array.
			    $types[] = get_post_type( $id );
		    endforeach;
		    //Assign our arguments based on passed in parameters and unique badge types and only earned badges by ID.
		    $args = array(
			    'posts_per_page' => -1,
			    'post_type' => array_unique($types),
			    'post__in' => $ids
		    );
		    $badges = new WP_Query( $args );
		    //store our resulting WP_Query object in a transient for one hour.
		    set_transient( 'custom_badgeos_user_achievements', $badges, 60*60 );
	    }
	    //Loop through our badges as we would any other post listing, display the parts we want.
	    if( $badges->have_posts() ) : while( $badges->have_posts() ) : $badges->the_post(); ?>
            <?php $achievements[] = get_post(); ?>
	    <?php endwhile; wp_reset_postdata(); endif;
	    return $achievements;
    }

    /**
     * Display currently earned badges for author.
     * @param  integer $count   How many posts to display per page
     * @param  integer $author_id ID of the author to display. Defaults to ID 1
     * @return mixed           echo'd badge loop.
     */
    function user_achievements( $author_id ) {

        $achievements = $this->get_user_achievements($author_id);

        foreach ( $achievements as $post) {
	        echo badgeos_get_achievement_post_thumbnail();
	        echo "<span class='badgeos-title-wrap'>";
	        the_title();
	        echo "</span>";

        }
    }

    /**
     * Add http:// to a string
     * @param  integer $url a string we want to be sure has http:// in front of
     * @return interger url with http://
     */
    public function addhttp($url) {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        return $url;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function getSslPage($url) {
        $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @param $page_slug
     * @param string $output
     * @param string $post_type
     * @return array|null|WP_Post
     */
    public function get_page_by_slug($page_slug, $output = OBJECT, $post_type = 'page' ) {
	  	global $wpdb;
	   	$page = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s AND post_status = 'publish'", $page_slug, $post_type ) );

	    if ( $page )
	    	return get_post($page, $output);
		return null;
	}


    /**
     * @param $badge_id
     * @param $user_login
     * @return mixed
     */
	public function get_submission($badge_id, $wp_user)
    {
        $query = new WP_Query([
            'post_status' => 'publish',
            'post_type' => 'submission',
            'author_name' => $wp_user->user_login,
            'meta_query' => [
                [
                    'key' => '_badgeos_submission_achievement_id',
                    'value' => $badge_id
                ]
            ],
            'posts_per_page' => 1,
        ]);
        $return = $query->get_posts();
        if (empty($return))
        {
            $query = new WP_Query([
                'post_status' => 'publish',
                'post_type' => 'nomination',
                'meta_query' => [
                    [
                        'key' => '_badgeos_nomination_achievement_id',
                        'value' => $badge_id
                    ],
                    [
                        'key' => '_badgeos_nomination_user_id',
                        'value' => $wp_user->ID
                    ]
                ],
                'posts_per_page' => 1,
            ]);
            $return = $query->get_posts();

        }
        return array_shift($return);

    }

    public function get_proof($submission_id) {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT pdf.form_id AS form_id,
                         pdfm.display_meta AS form_meta,
                         pdf.lead_id AS lead_id
                         FROM {$wpdb->prefix}rg_lead_detail AS pdf
                         INNER JOIN {$wpdb->prefix}postmeta AS pm
                         ON pdf.value = pm.meta_value
                         INNER JOIN {$wpdb->prefix}posts AS p
                         ON p.ID = pm.post_id
                         INNER JOIN {$wpdb->prefix}rg_form_meta AS pdfm
                         ON pdf.form_id = pdfm.form_id
                         WHERE p.post_status = 'publish'
                         AND pm.meta_key = '_badgeos_submission_achievement_id'
                         AND pm.post_id = %s", $submission_id
            )
        );

        /*
        echo "<pre>";

        print_r($row);
        $pdf_meta = json_decode($row->form_meta);


        $pdf_id = (array)($pdf_meta->gfpdf_form_settings);
        print_r($pdf_id); die;
*/

        //GPDFAPI::get_pdf($row->form_id, unshift($pdf_id)->id);
    }

    /**
     * @deprecated
     * @param $submission_id
     * @return false|null|string
     */
    public function get_attachment_by_submission_id( $submission_id) {
        $attachments = get_attached_media('application/pdf', $submission_id);
        if ( is_array($attachments) ) {
            $attachment = array_shift($attachments);
            return wp_get_attachment_url($attachment->ID);
        }
        return null;
    }


    /**
     * @return int
     */
    public function is_current_page_awarded_achievement()
    {
        $members_page_slug = str_replace(home_url()."/", '', get_permalink(get_option('bp-pages')['members']));
        // FIXME Bring into a WP Admin config variable
        $members_achievement_slug = "badges";
        // FIXME Bring into a WP Admin config variable
        $members_badge_slug = "badges";
        global $wp;
        return preg_match("#{$members_page_slug}(.*)/{$members_badge_slug}/(.*)#", $wp->request);


    }

    /**
     * Checks whether or not an achievement's privacy status is set to private
     * @param $submission_id int Submission ID
     * @return bool
     */
    public function is_achievement_private($submission_id)
    {
        $post = get_post($submission_id);
        if (!in_array($post->post_type, ['submission', 'nomination']))
        {
            return true;
        }

        $public = true;
        $status = get_post_meta($submission_id, 'public');
        if (is_array($status)) {
            if (empty($status)) {
                $this->set_achievement_public($submission_id);
            } else {
                $public = array_shift($status);
                $public = (bool)$public;

            }
        }
        return !$public;
    }

    /**
     * Checks whether an achievement is approved or not
     * @param $submission_id int Submission ID
     * @return bool
     */
    public function is_achievement_approved($submission_id)
    {
        $submission = get_post( $submission_id );

        $status = get_post_meta( $submission_id, '_badgeos_submission_status', true);
        if($status != 'pending' && $status != 'denied'){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * Set an achievement privacy status to private
     * @param $submission_id int Submission ID
     * @return bool|int
     */
    public function set_achievement_private($submission_id)
    {
        $post = get_post($submission_id);
        if ($post->post_type != 'submission' || $post->post_author != get_current_user_id())
            return FALSE;

        return update_post_meta($submission_id, 'public', false);
    }

    /**
     * Set an achievement privacy status to public
     * @param $submission_id int Submission ID
     * @return bool|int
     */
    public function set_achievement_public($submission_id)
    {
        $post = get_post($submission_id);
        if ($post->post_type != 'submission' || $post->post_author != get_current_user_id())
            return FALSE;

        return update_post_meta($submission_id, 'public', true);
    }

    /**
     * Toggle the privacy status of a submission
     * @param $submission_id int Submission ID
     * @return bool|string New status or null
     */
    public function toggle_private_status($submission_id)
    {
        $post = get_post($submission_id);
        if ($post->post_type != 'submission' || $post->post_author != get_current_user_id())
            return FALSE;

        $new_status = 'public';
        if ($this->is_achievement_private($submission_id))
        {
            $this->set_achievement_public($submission_id);
        }
        else
        {
            $this->set_achievement_private($submission_id);
            $new_status = 'private';
        }
        return $new_status;
    }

    /**
     * Get the Buddypress login page.
     * @return bool
     */
    public function bf_login_page()
    {
        //Get the bf-pages option, deserialize this information and get the id related to the register key.

        $bf_pages = get_option('bp-pages');

        //If this variable is false, the BP plugin is not on the site.
        if($bf_pages){
            foreach ( $bf_pages as $key => $bf_page){
                if(strtolower($key) == "register"){
                    $return = $bf_page;
                }
            }
        }
        else{
            $return = false;
        }

        return $return;
    }


	/**
	 *
	 */
	public function ajax_toggle_private_status()
    {
		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'myajax-nonce' ) )
		{
			die();
		}

		$achievement_id = $_POST['achievement_id'];
		$new_status = $GLOBALS['badgefactor']->toggle_private_status($achievement_id);

		$response = json_encode( array( 'success' => true, 'status' => $new_status) );
		header( "Content-Type: application/json" );
		echo $response;

		exit;
	}


	public function badgefactor_scripts()
    {
	    wp_register_script( 'badgefactor-script', plugins_url( '/assets/js/bf.js', __FILE__ ) );
    }

}

$GLOBALS['badgefactor'] = new BadgeFactor();
