<?php
class wp_redirect_author_pages {

    /**
     *
     * Register all actions and filters
     */
    function __construct() {
        add_action( 'template_redirect',    array( 'wp_redirect_author_pages', 'disable_author_page' ) );
        add_action( 'admin_init',           array( 'wp_redirect_author_pages', 'register_settings' ) );
        add_action( 'admin_menu',           array( 'wp_redirect_author_pages', 'options_menu' ) );
        add_action( 'plugins_loaded',       array( 'wp_redirect_author_pages', 'load_translations' ) );
        add_filter( 'author_link',          array( 'wp_redirect_author_pages', 'disable_author_link') );
        add_filter("plugin_row_meta",       array('wp_redirect_author_pages','APJPluginRowMeta') , 1, 2);
        add_filter( 'plugin_action_links_redirect-author-pages/redirect-author-pages.php' , array( 'wp_redirect_author_pages', 'plugin_settings_link' ) );
    }

    /**
     * Redirect the user
     *
     * This function is registerd to the template_redirect hook and  checks
     * to redirect the user to the selected page (or to the homepage)
     *
     */
    static public function disable_author_page() {
        global $post;
        $authorrequest = FALSE;
        if ( is_404() && ( get_query_var( 'author' ) || get_query_var( 'author_name' ) ) ) {
              if ( get_option( 'wp_redirect_author_pages_redirect_non_authors' ) == 1 ) {
                  $authorrequest = true;
              }
        }

        if ( is_404() && ! ( get_query_var( 'author' ) || get_query_var( 'author_name' ) ) ) {
              return;
        }

        if ( ( is_author() || $authorrequest ) && get_option( 'wp_redirect_author_pages_activate' ) == 1 ) {
            $adminonly = get_option( 'wp_redirect_author_pages_adminonly', '0' );
            $author_can = false;

            if ( ! is_404() && $adminonly ) {
                if( is_object( $post ) ) {
                    $author_can = author_can( get_the_ID(), 'administrator' );
                }
            }

            if ( $adminonly && $author_can===true || !$adminonly && !is_404() || is_404() && ( get_option( 'wp_redirect_author_pages_redirect_non_authors' ) == 1 ) ) {
                $status = get_option( 'wp_redirect_author_pages_status', '301' );
                $url = get_option( 'wp_redirect_author_pages_destination', '' );
                if ( $url == '' ) {
                    $url = home_url();
                } else {
                    $url = get_permalink( $url );
                }
                wp_redirect( $url, $status );
                exit;
            }
        }
    }

    /**
     * Register all settings
     *
     * Register all the settings, the plugin uses.
     */
    static public function register_settings() {
        register_setting( 'wp_redirect_author_pages_settings', 'wp_redirect_author_pages_status' );
        register_setting( 'wp_redirect_author_pages_settings', 'wp_redirect_author_pages_activate' );
        register_setting( 'wp_redirect_author_pages_settings', 'wp_redirect_author_pages_destination' );
        register_setting( 'wp_redirect_author_pages_settings', 'wp_redirect_author_pages_adminonly' );
        register_setting( 'wp_redirect_author_pages_settings', 'wp_redirect_author_pages_redirect_non_authors' );
        register_setting( 'wp_redirect_author_pages_settings', 'wp_redirect_author_pages_authorlink' );
    }

    /**
     * Overwrite the author url with an empty string
     *
     * @param string $content url to author page
     * @return string
     */
    static public function disable_author_link( $content ) {
        if ( get_option( 'wp_redirect_author_pages_authorlink', '0' ) == 1 ) {
            return "";
        } else {
            return $content;
        }
    }

    /**
     * load the plugin textdomain
     *
     * load the plugin textdomain with translations for the backend settingspage
     */
    static public function load_translations() {
        load_plugin_textdomain( 'redirect-author-pages', false, apply_filters ( 'wp_redirect_author_pages_translationpath', dirname( plugin_basename( __FILE__ )) . '/languages/' ) );
    }

    /**
     * Generate the options menu page
     *
     * Generate the options page under the options menu
     */
    static public function options_menu() {
        add_options_page( 'Redirect Author Pages',  __('Redirect Author Pages','redirect-author-pages', 'apj'), 'manage_options',
        __FILE__, array( 'wp_redirect_author_pages', 'create_options_disable_author_menu' ) );
    }

    /**
     * Generate the options page for the plugin
     *
     * @global type $settings
     */
    static public function create_options_disable_author_menu() {
        global $settings;
        $selectedpage = get_option( 'wp_redirect_author_pages_destination' );
    ?>
    <div class="wrap"  id="disableauthorpages">
    <h2><?php _e( 'Redirect Author Pages Plugin Settings', 'redirect-author-pages' ); ?></h2>
    <p><?php _e( 'Below are the settings to disable the author pages.', 'redirect-author-pages' ); ?></p>
    <form method="POST" action="options.php">
    <?php
    settings_fields( 'wp_redirect_author_pages_settings' );
    echo '<table class="form-table">';
    ?>
    <tr>
        <td style="width: 13px;"><input type="checkbox" name="wp_redirect_author_pages_activate" value="1" <?php if ( get_option( 'wp_redirect_author_pages_activate' ) ) echo " checked "; ?> /></td>
        <td><?php _e( 'Redirect Author Pages', 'redirect-author-pages' ); ?></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?php
            echo _e( 'Redirect destination page   ====>', 'redirect-author-pages' );
            echo wp_dropdown_pages("name=wp_redirect_author_pages_destination&selected={$selectedpage}&echo=0");
             ?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
        <?php _e( 'HTTP Status  ====>', 'redirect-author-pages' );?>
        <select name="wp_redirect_author_pages_status">
                <option value="301" <?php if ( get_option( 'wp_redirect_author_pages_status' ) == '301' ) { echo ' selected '; } ?> ><?php _e( '301 (Moved Permanently)', 'redirect-author-pages' );?></option>
                <option value="307" <?php if ( get_option( 'wp_redirect_author_pages_status' ) == '307' ) { echo ' selected '; } ?> ><?php _e( '307 (Temporary Redirect)', 'redirect-author-pages' );?></option>
            </select>
        </td>
    </tr>
    <tr>
    <tr>
        <td></td>
        <td>
            <input type="checkbox" name="wp_redirect_author_pages_adminonly" value="1" <?php if ( get_option( 'wp_redirect_author_pages_adminonly' ) ) echo " checked "; ?> />
            <?php _e( 'Disable for admin author pages only', 'redirect-author-pages' ); ?>
        </td>
    </tr>
        <td></td>
        <td>
            <input type="checkbox" name="wp_redirect_author_pages_authorlink" value="1" <?php if ( get_option( 'wp_redirect_author_pages_authorlink' ) ) echo " checked "; ?> />
            <?php _e( 'Disable Authorlink', 'redirect-author-pages' ); ?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <input type="checkbox" name="wp_redirect_author_pages_redirect_non_authors" value="1" <?php if ( get_option( 'wp_redirect_author_pages_redirect_non_authors' ) ) echo " checked "; ?> />
            <?php _e( 'Redirect non exists author pages', 'redirect-author-pages' ); ?>
        </td>
    </tr>
    </table>
    <br/>
    <input type="submit" class="button-primary" value="<?php _e('&nbsp;&nbsp;Update&nbsp;&nbsp;', 'redirect-author-pages' )?>" />
    </form>
    </div>
    <?php
    }

    /**
     * add settings link on plugin page
     *
     * @param $links
     * @return mixed
     * @since 0.10
     */
    static public function plugin_settings_link( $links ) {
        $settings_link = '<a href="options-general.php?page=redirect-author-pages%2Fpublic%2Fcore-redirect-author-pages.php">' . __('Settings', 'redirect-author-pages') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
        /**
     * Plugin row meta/action links
     * @return void
     */
    static public function APJPluginRowMeta($links_array, $plugin_file_name)
    {
        if (strpos($plugin_file_name, 'redirect-author-pages.php')) $links_array = array_merge($links_array, array(
            '<a target="_blank" href="https://paypal.me/arulprasadj?locale.x=en_GB"><span style="font-size: 20px; height: 20px; width: 20px;" class="dashicons dashicons-heart"></span>' . __('Donate', 'redirect-author-pages') . '</a>'
        ));
        return $links_array;
    }
}
