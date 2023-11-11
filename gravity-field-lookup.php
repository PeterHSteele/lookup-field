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
  }

  private function load(){
    require_once GFLOOKUPPATH . 'inputs/class-lookup-field.php';
    GF_Fields::register( new GfLookupField() );
  }

  public function enqueue($form,$is_ajax){
    $lookup_fields = GFAPI::get_fields_by_type($form,'lookup');
    if (count($lookup_fields)){
      wp_enqueue_script(
        'lookup-script',
        plugins_url('/input.js',__FILE__),
        array('jquery')
      );
    }
  }

  public function inline_styles(){
    ?>
    <style>.gfield--type-lookup{position:relative}.gfield--type-lookup .lf-tooltip{display:none;position:absolute;width:100%;z-index:1;top:100%;left:0;box-shadow:5px 5px 12px rgba(0,0,0,.2)}.gfield--type-lookup .lf-tooltip.is-visible{display:block}.gfield--type-lookup ul{list-style:none;margin:0;padding:0;box-shadow:3px 3px 5px rgba(0,0,0,.1);background:#fff;border-radius:5px;font-size:.8125em}.gfield--type-lookup li{color:#333;padding:.6875em 1em;font-size:.9325em;gap:1em;border-bottom:1px solid #f3f3f3;border-radius:5px;cursor:pointer;line-height:1.15}@media all and (min-width:768px){.gfield--type-lookup ul{font-size:.9325em}.gfield--type-lookup li{padding:.75em 1.25em;line-height:1.5}}.gfield--type-lookup li:last-child{border-bottom:0}.gfield--type-lookup li.lf-has-focus,.gfield--type-lookup li:hover{background:linear-gradient(100deg,#8a3ef0,#9a33e1);color:#fff}.gfield--type-lookup .lf-address{text-transform:lowercase;color:#555}.gfield--type-lookup li.lf-has-focus .lf-address,.gfield--type-lookup li:hover .lf-address{color:#fff}</style>
    <?php
  }

  private function add_hooks(){
    add_action( 'gform_field_advanced_settings', array( 'GfLookupField', 'lookup_url_setting' ), 10, 2 );
    add_action( 'gform_enqueue_scripts', array( $this, 'enqueue'), 10, 2);
    add_action( 'wp_head', array( $this, 'inline_styles') );
  }
}

$lookup = new LookupFieldSetup();