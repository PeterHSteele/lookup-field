<?php
/**
 * Plugin Name:       Lookup Field
 * Description:       Doctor Lookup field for gravity forms
 * Requires at least: 6.3
 * Requires PHP:      8.0
 * Version:           1.0.0
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lookup-field
 *
 * @package           lookup-field
 */

defined('ABSPATH') || exit;

class LookupFieldSetup {

  public function __construct(){
    if ( !class_exists( 'GF_Field' )){
      return;
    }
    define( 'GFLOOKUPPATH', plugin_dir_path(__FILE__));
    $this->load();
    $this->add_hooks();
    //add_action( 'gform_editor_js', 'editor_script' );
  }

  private function load(){
    require_once GFLOOKUPPATH . 'inputs/class-lookup-field.php';
    GF_Fields::register( new GfLookupField() );
  }

  public function enqueue(){
    wp_enqueue_script(
      'lookup-script',
      plugins_url('/input.js',__FILE__),
      array('jquery')
    );
  }

  public function inline_styles(){
    ?>
    <style>
      .gfield--type-lookup{
        position: relative;
      }

      .gfield--type-lookup .lf-tooltip{
        display: none;
        position:absolute;
        width:100%;
        top:100%;
        left:0;
        box-shadow: 5px 5px 12px rgba(0,0,0,.2);
      }

      .gfield--type-lookup .lf-tooltip.is-visible{
        display: block
      }

      .gfield--type-lookup ul{
        list-style: none;
        margin: 0;
        padding: 0;
        box-shadow: 3px 3px 5px rgba(0,0,0,.1);
        background: #fff;
        border-radius: 5px;
        font-size: .8125em;
      }

      @media all and (min-width: 768px){
        .gfield--type-lookup ul{
          font-size: .9325em;
        }
      }

      .gfield--type-lookup li{
        color: #333;
        padding: .75em 1.25em;
        font-size: .9325em;
        gap: 1em;
        border-bottom: 1px solid #f3f3f3;
        border-radius: 5px;
      }

      .gfield--type-lookup li:last-child{
        border-bottom: 0;
      }

      .gfield--type-lookup li:hover,
      .gfield--type-lookup li.lf-has-focus{
        background: linear-gradient(100deg, rgb(138, 62, 240), rgb(154, 51, 225) );
        color: #fff;
      }

      .gfield--type-lookup .lf-address{
        text-transform: lowercase;
        color: #555;
      }

      .gfield--type-lookup li:hover .lf-address,
      .gfield--type-lookup li.lf-has-focus .lf-address{
        color: #fff;
      }
    </style>
    <?php
  }

  private function add_hooks(){
    add_action( 'gform_field_advanced_settings', array( 'GfLookupField', 'lookup_url_setting' ), 10, 2 );
    //add_action( 'gform_editor_js', 'editor_script' );
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue'));
    add_action( 'wp_head', array( $this, 'inline_styles') );
  }
}

$lookup = new LookupFieldSetup();