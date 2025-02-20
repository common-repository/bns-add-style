<?php
/*
Plugin Name: BNS Add Style
Plugin URI: https://profiles.wordpress.org/unihost/
Description: Adds an enqueued custom stylesheet to the active theme
Version: 0.9
Author: Unihost
Author URI: https://profiles.wordpress.org/unihost/
Textdomain: bns-as
License: GNU General Public License v3
License URI: http://www.gnu.org/licenses/old-licenses/gpl-3.0.html
*/

/**
 * BNS Add Style WordPress plugin
 *
 * IF all you need to do is change a Theme's existing CSS this plugin will
 * provide you an enqueued stylesheet that will not be over-written when a Theme
 * is update; saving you the work of creating and maintaining a Child-theme.
 *
 * @package     BNS_Add_Style
 * @link        https://wordpress.org/plugins/
 * @version     0.9
 * @author      Unihost
 * @copyright   Copyright (c) 2012-2017, Unihost
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3, as published by the
 * Free Software Foundation.
 *
 * You may NOT assume that you can use earlier versions of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to:
 *
 *      Free Software Foundation, Inc.
 *      51 Franklin St, Fifth Floor
 *      Boston, MA  02110-1301  USA
 *
 * The license for this software can also likely be found here:
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 *
 * @version     0.5
 * @date        December 15, 2012
 * Added LESS support with additional stylesheet `bns-add-less-style.css`
 *
 * @version     0.5.3
 * @date        December 15, 2012
 * Fix typos and add complete URL link
 * Refactor to only add LESS file; end-user will need to FTP / manually edit
 * file contents
 *
 * @version     0.5.4
 * @date        December 23, 2012
 * Correct style bleed through into Administration Panels
 *
 * @version     0.6
 * @date        June 25, 2013
 * Remove LESS support, lets just keep this as a simple CSS adder
 * Clean up documentation
 *
 * @version     0.7
 * @date        November 27, 2016
 * Clean up documentation
 *
 * @version     0.8
 * @date        November 27, 2016
 * Fixed Spelling mistakes 
 *
 * @version     0.9
 * @date        October 12, 2017
 * Added a set of Media Queries for reference
 *
 * @todo        Add access via the WordPress theme editor
 * @todo        Review use of `admin_init` hook - is there a better hook/method? There must be as this is causing some grief!!
 */
class BNS_Add_Style
{
    /** Constructor */
    function __construct()
    {
        /**
         * Check installed WordPress version for compatibility
         *
         * @package              BNS_Add_Style
         * @since                0.1
         * @internal             Version 2.5 - WP_Filesystem
         *
         * @uses        (global) wp_version
         * @uses                 add_action
         */
        global $wp_version;
        $exit_message = __('BNS Add Style requires WordPress version 2.5 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please Update!</a>', 'bns-as');
        if (version_compare($wp_version, "2.5", "<")) {
            exit($exit_message);
        }
        /** End if - version compare */

        /** Make sure the stylesheet is added immediately. */
        add_action('admin_init', array($this, 'add_stylesheet'));
        add_action('admin_init', array($this, 'deregister_admin'));
        /**
         * Enqueue the stylesheet after the default enqueue position (10)
         * to insure CSS specificity is adhered to
         */
        add_action('wp_enqueue_scripts', array($this, 'add_stylesheet'), 15, 2);

    }

    /** End function - construct */

    /**
     * Add Custom Stylesheet
     * If the custom stylesheet does not exist this will create it.
     *
     * @package            BNS_Add_Style
     * @since              0.1
     *
     * @uses    (constant) FS_CHMOD_FILE - predefined mode settings for WP files
     * @uses    (global)   $wp_filesystem -> put_contents
     * @uses               WP_Filesystem API
     * @uses               get_stylesheet_directory
     * @uses               get_stylesheet_directory_uri
     * @uses               request_filesystem_credentials
     * @uses               wp_nonce_url
     *
     * @return  bool|null
     *
     * @version            0.7
     * @date               November 27, 2012
     * Add i18n support to introductory text of stylesheet
     */
    function add_custom_stylesheet()
    {

        /** @var $bns_add_style_path - path to the stylesheet */
        $bns_add_style_path_safe = WP_CONTENT_DIR . '/bns-add-custom-style.css';

        /** @var $bns_add_style_path_theme - path to working stylesheet */
        $bns_add_style_path_theme = get_stylesheet_directory() . '/bns-add-custom-style.css';

        /** If the custom stylesheet is not readable get the credentials to write it */
        if (!is_readable($bns_add_style_path_theme)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            $url = wp_nonce_url(get_stylesheet_directory_uri() . '/bns-add-custom-style.css');
            if (false === ($credentials = request_filesystem_credentials($url))) {
                return true;
            }
            /** End if - is readable */
            if (!WP_Filesystem($credentials)) {
                // our credentials were no good, ask the user for them again
                request_filesystem_credentials($url, '', true, false, '');

                return true;
            }
            /** End if - no credentials */
        }
        /** End if - not is readable */

        global $wp_filesystem;
        /** @var $css - introductory text of stylesheet */
        $css = __("/**
 * BNS Add Style - Custom Stylesheet
 *
 * This file was added after the activation of the BNS Add Style Plugin.
 *
 * If you no longer want to use these styles delete the contents of this file,
 * or simply deactivate the BNS Add Style Plugin (recommended).
 *
 * If you choose to deactivate this plugin this file will remain as is but will
 * not be used. If you reactivate this plugin the styles below will take effect.
 *
 * Add your custom styles for this theme below this comment block. Enjoy!
 * I've added a few media queries to make it easier.
 */
 /* Media queries for easy reference */
/* @media screen and (min-width: 481px) {
    class {}
}

@media screen and (max-width: 679px) {
    class {}
}

@media screen and (max-width: 1225px) {
    class {}
}

@media screen and (max-width: 1825px) {
    class {}
} */
 ", 'bns-as');
        /** The format and placement above is reproduced as shown in the editor?! */

        $wp_filesystem->put_contents(
            $bns_add_style_path_theme,
            $css,
            FS_CHMOD_FILE
        );

        $wp_filesystem->put_contents(
            $bns_add_style_path_safe,
            $css,
            FS_CHMOD_FILE
        );

        /** Now leave well enough alone after creating the CSS file */

        return null;

    }

    /** End function - add custom stylesheet */


    /**
     * Add Stylesheet
     * Adds a custom stylesheet to the active theme folder which can be accessed via
     * the "Edit Themes" functionality under Appearance | Editor
     *
     * @package BNS_Add_Style
     * @since   0.1
     *
     * @uses    add_custom_stylesheet
     * @uses    get_plugin_data
     * @uses    get_stylesheet_directory
     * @uses    get_stylesheet_directory_uri
     * @uses    wp_enqueue_style
     *
     * @version 0.2
     * @date    September 12, 2012
     * Set stylesheet version to be dynamic
     */
    function add_stylesheet()
    {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $bns_as_data = get_plugin_data(__FILE__);
        /* Enqueue Styles */
        if (is_readable(get_stylesheet_directory() . '/bns-add-custom-style.css')) {
            wp_enqueue_style('BNS-Add-Custom-Style', get_stylesheet_directory_uri() . '/bns-add-custom-style.css', array(), $bns_as_data['Version'], 'screen');
        } else {
            $this->add_custom_stylesheet();
            wp_enqueue_style('BNS-Add-Custom-Style', get_stylesheet_directory_uri() . '/bns-add-custom-style.css', array(), $bns_as_data['Version'], 'screen');
        }
        /** End if - is readable */
    }

    /** End function - add stylesheet */


    /**
     * Deregister Admin
     * Used to clear stylesheets being added ... probably _doing_it_wrong
     *
     * @package BNS_Add_Style
     * @since   0.5.4
     *
     * @uses    wp_deregister_style
     *
     * @todo    Fix this ugliness ... probably have to sort out the LESS method too
     */
    function deregister_admin()
    {
        wp_deregister_style('BNS-Add-Custom-Style');
    }
    /** End function - deregister admin */


}

/** End class - BNS Add Style */


/** @var $bns_add_style - initialize new class instance */
$bns_add_style = new BNS_Add_Style();