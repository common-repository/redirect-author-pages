<?php
/*
 * Plugin Name: Redirect Author Pages
 * Description: This plugin Disable the author pages in WordPress and redirect user to another page.
 * Author:      Arul Prasad J
 * Author URI:  https://profiles.wordpress.org/arulprasadj/
 * Plugin URI:  https://wordpress.org/plugins/redirect-author-pages
 * Text Domain: redirect-author-pages
 * Domain Path: /languages/
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Version:     1.0
 */

/* Copyright (C)  2020-2021 arulprasadj (email:info@arulprasadj.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/


if (!class_exists( 'wp_redirect_author_pages' ) ) {

    include_once dirname( __FILE__ ) .'/public/core-redirect-author-pages.php';

    /**
     * Delete options on plugin install
     */
    function wp_redirect_author_pages_uninstall() {
        global $wpdb;
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name like 'wp_redirect_author_pages_%';" );
    }

    register_uninstall_hook( __FILE__,  'wp_redirect_author_pages_uninstall' );

    $wp_redirect_author_pages = new wp_redirect_author_pages();

}