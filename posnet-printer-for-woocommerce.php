<?php
/**
 * Crunchify Hello World Plugin is the simplest WordPress plugin for beginner.
 * Take this as a base plugin and modify as per your need.
 *
 * @package Crunchify Hello World Plugin
 * @author Crunchify
 * @license GPL-2.0+
 * @link https://crunchify.com/tag/wordpress-beginner/
 * @copyright 2017 Crunchify, LLC. All rights reserved.
 *
 * @wordpress-plugin
 * Plugin Name:             Posnet Printer for WooCommerce
 * Plugin URI:              https://wordpress.org/plugins/posnet-printer-for-woocommerce
 * Description:             Posnet printer integration plugin | Plugin umożliwiający integrację z kasami fiskalnymi Posnet
 * Version:                 1.0.3
 * Author:                  BigDotSoftware
 * Author URI:              http://bigdotsoftware.pl/posnetserver-restful-service-dla-drukarek-posnet
 * License:                 GPL-2.0+
 * License URI:             http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:             posnet-printer-for-woocommerce
 * Domain Path:             /lang
 * WC requires at least:    3.0.0
 * WC tested up to:         3.7.0
 */

defined( 'ABSPATH' ) || exit;

define('Posnet_Printer_For_Woo_TAB0', 'tab0');
define('Posnet_Printer_For_Woo_TAB1', 'tab1');

//Enable below for development purposes only! comment while creating official release
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Posnet_Printer_For_Woocommerce_Orders_Table extends WP_List_Table {
   
   function __construct(){
      global $status, $page;
      parent::__construct( array(
            'singular'  => __( 'orderid', 'mylisttable' ),     //singular name of the listed records
            'plural'    => __( 'orderids', 'mylisttable' ),   //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
      ) );
      add_action( 'admin_head', array( &$this, 'admin_header' ) );            
   }
    
   function admin_header() {
      echo '<style type="text/css">';
      echo '.wp-list-table .column-id { width: 5%; }';
      echo '.wp-list-table .column-billing { width: 40%; }';
      echo '.wp-list-table .column-date_created { width: 35%; }';
      echo '.wp-list-table .column-total { width: 20%;}';
      echo '</style>';
   }
   function no_items() {
      _e( 'No Woocommerce orders found.' );
   }
  
   function column_default( $item, $column_name ) {
      switch( $column_name ) { 
         case 'id':
            return '<b>#' . $item->get_id() . '</b>';
         case 'billing':
            return $item->get_billing_first_name() . ' ' . $item->get_billing_last_name(). ', ' . $item->get_billing_address_1(). ' ' . $item->get_billing_address_2() . ', ' . $item->get_billing_postcode() . ' ' . $item->get_billing_city();
         case 'date_created':
            return $item->get_date_created();
         case 'total':
            return $item->get_formatted_order_total();
         default:
            return print_r( $item, true );
      }
   }
   function get_sortable_columns() {
      $sortable_columns = array(
         'id'  => array('id',false),
         'billing' => array('billing',false),
         'date_created'   => array('date_created',false),
         'total'   => array('total',false)
      );
      return $sortable_columns;
   }

   function get_columns(){
      $columns = array(
            'cb'           => '<input type="checkbox" />',
            'id'           => __( 'Zamówienie', 'mylisttable' ),
            'billing'      => __( 'Klient', 'mylisttable' ),
            'date_created' => __( 'Data', 'mylisttable' ),
            'total'        => __( 'Suma', 'mylisttable' )
      );
      return $columns;
   }

   function column_id($item){
      $page = "posnet-printer-for-woocommerce"; //is_int((int)$_REQUEST['page'])?(int)$_REQUEST['page']:0;
      $actions = array(
         'view'      => sprintf('<a href="?page=%s&action=%s&orderid=%s">Drukuj</a>', $page, 'view', $item->get_id())
      );
      return sprintf('%1$s %2$s', $item->get_id(), $this->row_actions($actions) );
   }
   function get_bulk_actions() {
      $actions = array(
         'view'    => 'Drukuj'
      );
      return $actions;
   }
   function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="orderid[]" value="%s" />',$item->get_id()
        );    
   }
   function prepare_items() {
      $columns  = $this->get_columns();
      $hidden   = array();
      $sortable = $this->get_sortable_columns();
      $this->_column_headers = array( $columns, $hidden, $sortable );
     
      $per_page = 5; //$per_page = $this->get_items_per_page('orders_per_page', 5);
      $current_page = $this->get_pagenum();
      
      $args = array(
         'type' => 'shop_order',
         'paginate' => true,
         'limit' => $per_page,
         'paged' => $current_page,
      );
      $results = wc_get_orders( $args );
      
      /*$found_data = array();
      foreach ($results->orders as $order) {
         $found_data[] = $order->get_data();
      }
      print_r($found_data);*/
      //$total_items = count( $this->example_data );
      $total_items = $results->total;
      
      //$found_data = array_slice( $this->example_data,( ( $current_page-1 )* $per_page ), $per_page );
      $this->set_pagination_args( array(
         'total_items' => $total_items,                  //WE have to calculate the total number of items
         'per_page'    => $per_page                     //WE have to determine how many items to show on a page
      ) );
      $this->items = $results->orders;//$found_data;
      //print_r($this->items);
   }
}


function posnet_printer_for_woocommerce_add_menu() {
	//add_submenu_page("options-general.php", "Posnet Printer", "Posnet Printer", "manage_options", "woocommerce-posnet-printer", "posnet_printer_for_woocommerce_page");
   add_submenu_page( 'woocommerce', "Posnet Printer", "Posnet Printer", 'manage_options', "posnet-printer-for-woocommerce", "posnet_printer_for_woocommerce_page" );
}
add_action("admin_menu", "posnet_printer_for_woocommerce_add_menu");

function posnet_printer_for_woocommerce_validateOrderIDs($orderids) {
   $result = array();
   foreach($orderids as $orderid) {
      $orderid = (int)$orderid;
      if( is_int($orderid) )
         $result[] = $orderid;
   }
   return $result;
}

function posnet_printer_for_woocommerce_processOrderIDs($orderids) {
   $documents = array();
   foreach($orderids as $orderid) {
      $order = wc_get_order( $orderid );
      // print_r($order);
      // print_r($order->get_items());
      $positions = array();   // Pozycje do ufiskalnienia
      
      echo '<h3>Zamówienie #'.$order->get_id().'</h3>';
      echo '<b>Zamawiający:</b> ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(). ', ' . $order->get_billing_address_1(). ' ' . $order->get_billing_address_2() . ', ' . $order->get_billing_postcode() . ' ' . $order->get_billing_city() . '<br/>';
      echo '<br/>';
      echo '<table><tr><th>#</th><th>Nazwa</th><th>Ilość</th><th>Stawka</th><th>VAT</th><th>Netto</th><th>Brutto</th></tr>';
      $lp = 1;
      foreach ($order->get_items() as $item_key => $item ) {   //dla każdej pozycji wyliczamy VAT
         // print_r($item);
         $item_name       = $item->get_name(); // Name of the product
         $quantity        = $item->get_quantity();  
         $tax_class       = $item->get_tax_class();
         $line_total      = $item->get_total(); // Line total (discounted)
         $line_total_tax  = round($item->get_total_tax(),2); // Line total tax (discounted)
         $vatp = round(($line_total_tax / $line_total) * 100.0);
         $brutto = $line_total_tax + $line_total;
         $positions[] = array('na' => $item_name, 'il'=>$quantity, 'vtp'=>$vatp, 'pr'=> round((($item->get_total_tax() + $item->get_total()) / $quantity)*100) );
         echo "<tr><td>$lp</td><td>$item_name</td><td align=\"right\">$quantity szt</td><td align=\"right\">$vatp %</td><td align=\"right\">$line_total_tax {$order->get_currency()}</td><td align=\"right\">$line_total {$order->get_currency()}</td><td align=\"right\">$brutto {$order->get_currency()}</td></tr>";
         $lp++;
      }
      if( count($order->get_shipping_methods() )>0) {
         $line_total       = $order->get_shipping_total();
         $line_total_tax   = round($order->get_shipping_tax(),2);
         $item_name        = $order->get_shipping_method();
         $quantity = 1;
         $vatp = round(($line_total_tax / $line_total) * 100.0);
         $brutto = round($order->get_shipping_tax() + $order->get_shipping_total(),2);
         $positions[] = array('na' => $item_name, 'il'=>$quantity, 'vtp'=>$vatp, 'pr'=>round($order->get_shipping_tax() + $order->get_shipping_total() )*100);
         echo "<tr><td>$lp</td><td>$item_name</td><td align=\"right\">$quantity szt</td><td align=\"right\">$vatp %</td><td align=\"right\">$line_total_tax {$order->get_currency()}</td><td align=\"right\">$line_total {$order->get_currency()}</td><td align=\"right\">$brutto {$order->get_currency()}</td></tr>";
      }
      echo '</table>';
      echo '<br/>';
      echo '<b>VAT:</b> ' .round($order->get_total_tax(),2) . ' ' . $order->get_currency() . '</br>';
      echo '<b>Razem brutto:</b> ' .$order->get_total() . ' ' . $order->get_currency() . '</br>';
      
      $documents[] = array('to'=>$order->get_total()*100, 'lines'=>$positions);
   }
   return $documents;
}

function posnet_printer_for_woocommerce_readPluginConfiguration() {
   
   $options = get_option('woocommerce_posnet_printer_config');
   if ($options === false){
      $options = array(
         'PosnetServerHost' => 'http://127.0.0.1:3050',
         'ExtraLines' => '[
            {id:2,  na: "987"},
            {id:15, na: "Jan Kowalski"},
            {id:39, na: "FV 12345/2018"},
            {id:33, na: "dodatkowe informacje #1"},
            {id:33, na: "dodatkowe informacje #2", sw: true, sh: true},
            {id:38, na: "text info"}
            ]'
      );
      update_option( 'woocommerce_posnet_printer_config', $options );
   }
   return $options;
   
   //    $PosnetServerHost = stripslashes_deep(esc_attr(get_option('woocommerce-posnet-printer-serverurl-text', 'http://127.0.0.1:3050')));
   //    $ExtraLines = stripslashes_deep(esc_attr(get_option('woocommerce-posnet-printer-extralines-text', '[
   // {id:2,  na: "987"},
   // {id:15, na: "Jan Kowalski"},
   // {id:39, na: "FV 12345/2018"},
   // {id:33, na: "dodatkowe informacje #1"},
   // {id:33, na: "dodatkowe informacje #2", sw: true, sh: true},
   // {id:38, na: "text info"}
   // ]')));
   
   // return array(
   //    'PosnetServerHost' => $PosnetServerHost,
   //    'ExtraLines' => $ExtraLines
   // );
}
/**
 * Setting Page Options
 * - add setting page
 * - save setting page
 *
 * @since 1.0
 */
function posnet_printer_for_woocommerce_page()
{
   $configdata = posnet_printer_for_woocommerce_readPluginConfiguration();
   $PosnetServerHost = $configdata['PosnetServerHost'];
   $ExtraLines = $configdata['ExtraLines'];
?>
<div class="wrap">
 
   <h1>Posnet Server Woocommerce plugin by <a href="http://bigdotsoftware.pl/posnetserver-restful-service-dla-drukarek-posnet" target="_blank">BigDotSoftware</a></h1>
   
   <?php
   // Determine active tab
   if( isset( $_GET[ 'tab' ] ) ) {
      $active_tab = isset( $_GET[ 'tab' ] ) && in_array($_GET[ 'tab' ], array(Posnet_Printer_For_Woo_TAB0, Posnet_Printer_For_Woo_TAB1)) ? $_GET[ 'tab' ] : Posnet_Printer_For_Woo_TAB0;
   } else if( isset( $_POST[ 'tab' ] ) ) {
      $active_tab = isset( $_POST[ 'tab' ] ) && in_array($_POST[ 'tab' ], array(Posnet_Printer_For_Woo_TAB0, Posnet_Printer_For_Woo_TAB1)) ? $_POST[ 'tab' ] : Posnet_Printer_For_Woo_TAB0;
   } else {
      $active_tab = Posnet_Printer_For_Woo_TAB0;
   }

   ?>
   <h2 class="nav-tab-wrapper">
      <a href="?page=posnet-printer-for-woocommerce&tab=<?php echo Posnet_Printer_For_Woo_TAB0; ?>" class="nav-tab <?php echo $active_tab == Posnet_Printer_For_Woo_TAB0 ? 'nav-tab-active' : ''; ?>">Drukowanie</a>
      <a href="?page=posnet-printer-for-woocommerce&tab=<?php echo Posnet_Printer_For_Woo_TAB1; ?>" class="nav-tab <?php echo $active_tab == Posnet_Printer_For_Woo_TAB1 ? 'nav-tab-active' : ''; ?>">Ustawienia</a>
   </h2>
         
   <!-- ACTIVE TAB0 -->
   <?php if( $active_tab == Posnet_Printer_For_Woo_TAB0 ) { ?>
   
      <h2>Drukuj Paragon do wybranego zamówienia</h2>
      <form method='POST' action='<?php echo admin_url( 'admin.php?page=posnet-printer-for-woocommerce' ); ?>'>
         <input type='text' value='<?php echo isset( $_REQUEST[ 'orderid' ] ) && is_int((int)$_REQUEST[ 'orderid' ]) ? (int)$_REQUEST[ 'orderid' ] : ''; ?>' name='orderid'/>
         <input type='hidden' name='action' value='view'/>
         <input type='hidden' name='tab' value='<?php echo $active_tab; ?>'/>
         <?php wp_nonce_field( 'submitform', 'submitform_nonce' ); ?>
         <?php submit_button('Pokaż zamówienie'); ?>
      </form>
      
      <?php
      if( isset( $_REQUEST[ 'orderid' ] ) && (isset( $_REQUEST[ 'action' ] ) && in_array($_REQUEST[ 'action' ], array('view','print')) || isset( $_REQUEST[ 'action2' ] ) && in_array($_REQUEST[ 'action2' ], array('view','print'))) ) {
         $orderids = array();
         if( is_array($_REQUEST[ 'orderid' ]) )
            $orderids = $_REQUEST[ 'orderid' ];
         else
            $orderids[] = $_REQUEST[ 'orderid' ];
         
         $orderids = posnet_printer_for_woocommerce_validateOrderIDs($orderids);
         $documents = posnet_printer_for_woocommerce_processOrderIDs($orderids)
         
         ?>
         <div id="printwait" style="display:none">
            Trwa drukowanie, proszę czekać
            <img src="<?php echo esc_url(plugins_url('spinner-1s-200px.gif', __FILE__ )); ?>"/>
         </div>
         <div id="printwaitok" style="display:none;background: green;color: white;padding: 10px;border-radius: 5px;">
            Drukowanie zakończono pomyślnie
            <img src="<?php echo esc_url(plugins_url('spinner-1s-200px.gif', __FILE__ )); ?>"/>
         </div>
         <div id="printwaitwarning" style="display:none;background: orange;color: white;padding: 10px;border-radius: 5px;">
            Drukowanie zakończono częściowo pomyślnie
            <img src="<?php echo esc_url(plugins_url('spinner-1s-200px.gif', __FILE__ )); ?>"/>
         </div>         
         <div id="printwaiterror" style="display:none;background: red;color: white;padding: 10px;border-radius: 5px;">
            Błąd
         </div>
         
         <script>
         function drukujfiskalny() {
            
            document.getElementById("printwait").style.display = "initial";
            document.getElementById("printwaitok").style.display = "none";
            document.getElementById("printwaitwarning").style.display = "none";
            document.getElementById("printwaiterror").style.display = "none";
            
            var http = new XMLHttpRequest();
            var url = '<?php echo $PosnetServerHost; ?>/<?php echo count($orderids)>1?'paragony':'paragon'; ?>';
            var params = [];
            <?php foreach($documents as $document) { ?>
            params.push({
               lines : [ 
                  <?php
                  $lp = 0;
                  foreach ($document['lines'] as $poskey => $positem ) {
                     if( $lp>0) echo ",";
                     $vtp = str_pad( number_format((float)$positem['vtp'], 2, ',', ''), 2, '0', STR_PAD_LEFT); // format to: 05,00 or 23,00
                     echo "{ na: \"{$positem['na']}\", il: {$positem['il']}, vtp: \"$vtp\", pr: {$positem['pr']} }\n";
                     $lp++;
                  }
                  ?>
               ],
               summary : {
                  to: <?php echo $document['to']."\n"; ?>
               },
               extralines: <?php echo $ExtraLines==""?'[]':htmlspecialchars_decode($ExtraLines); ?>
            });
            <?php } ?>
            http.open('POST', url, true);

            //Send the proper header information along with the request
            http.setRequestHeader('Content-type', 'application/json');

            http.onreadystatechange = function() {//Call a function when the state changes.
               //alert(http.responseText);
               console.log(http.readyState);
               console.log(http.status);
               
               if(http.readyState == 4 /*DONE*/) {
                  
                  var responseObj = {};
                  try{
                     responseObj = JSON.parse(http.responseText);
                  }catch(e) {
                     // something is wrong
                     console.error('Cannot parse:' + http.responseText);
                     document.getElementById("printwaiterror").style.display = "initial";
                     document.getElementById("printwaiterror").innerText = 'Nie można odczytać odpowiedzi z serwisu';
                  }
               
                  if( http.status == 200 ) {
                     // alert(http.responseText);
                     // w przypadku wersji 2.1 serwis zwraca 200OK dla requestu /paragony nawet jesli ktorys z paragonow sie nie powdodl. Nalezy sprawdzic kazy element tablicy "hits" z osobna
                     
                     <?php if(count($orderids)>1) { ?>
                     var somefailed = responseObj.hits.filter(a=>a.ok==false);
                     var numery = responseObj.hits.filter(a=>a.ok==true).map(a=>{return a.bn}).join();
                     var allerrors = responseObj.hits.filter(a=>a.ok==false).map(a=>{return a.message}).join();
                     if( somefailed.length == responseObj.hits.length ) {
                        document.getElementById("printwaiterror").style.display = "initial";
                        document.getElementById("printwaiterror").innerHTML = 'Błąd drukowania paragonów: ' + allerrors;
                     } else if( somefailed.length > 0 ) {
                        document.getElementById("printwaitwarning").style.display = "initial";
                        document.getElementById("printwaitwarning").innerHTML = 'Tylko część paragonów wydrukowano pomyślnie. <b>Paragony: ' + numery + '</b> Błędy: ' + allerrors;
                     } else {
                        document.getElementById("printwaitok").style.display = "initial";
                        document.getElementById("printwaitok").innerHTML = 'Drukowanie zakończono pomyślnie. <b>Paragony: ' + numery + '</b>';
                     }
                     <?php } else { ?>
                     document.getElementById("printwaitok").style.display = "initial";
                     document.getElementById("printwaitok").innerHTML = 'Drukowanie zakończono pomyślnie. <b>Paragon numer: ' + responseObj.bn + '</b>';
                     <?php } ?>
                  } else {
                     // w przypadku bledy pojedynczego paragonu
                     document.getElementById("printwaiterror").style.display = "initial";
                     document.getElementById("printwaiterror").innerText = 'Błąd: ' + (responseObj.message!=null?responseObj.message:'');
                  }
               }
               document.getElementById("printwait").style.display = "none";
            }
            <?php if(count($orderids)>1) { ?>
            http.send(JSON.stringify(params));
            <?php } else { ?>
            http.send(JSON.stringify(params[0]));
            <?php } ?>
         }
         </script>
         <?php if(count($orderids)>1) { ?>
         <p class="submit">
            <input type="submit" onclick="drukujfiskalny()" name="submit" id="submit" class="button button-primary" value="Rozpocznij drukowanie paragonów do zamówień <?php echo implode (", ", $orderids); ?>">
         </p>
         <?php } else { ?>
         <p class="submit">
            <input type="submit" onclick="drukujfiskalny()" name="submit" id="submit" class="button button-primary" value="Rozpocznij drukowanie paragonu do zamówienia <?php echo $orderids[0]; ?>">
         </p>
         <?php } ?>
         <?php
         
      }
      ?>
      
      
      <h2>Przeglądaj zamówienia</h2>
      <?php /*print_r($_POST);*/ ?>
      <?php 
         $myListTable = new Posnet_Printer_For_Woocommerce_Orders_Table();
         $myListTable->prepare_items();
      ?>
   <form method="post">
      <input type="hidden" name="page" value="posnet-printer-for-woocommerce">
      <?php
         $myListTable->search_box( 'search', 'search_id' );
         $myListTable->display(); 
      ?>
   </form>
   
   <!-- ACTIVE TAB1 -->
   <?php } else { ?>
   
      <h2>Ustawienia</h2>
      <i>Plugin nie jest kompletnym rozwiązaniem a jedynie przykładem wykorzystania komponentu Posnet Server do drukowania paragonów. Posnet Server dodatkowo umożliwia również drukowanie faktur, kodów kreskowych, niestandardowych formatek jak również zarządzania samą drukarką fiskalną. Stąd, plugin często wymaga indywidualnego dopasowania. </br>
      <a href="https://www.youtube.com/channel/UCbX9ECPnLMRq8oMOWT2k8UQ">Our Youtube channel</a></i>
      <form method="post" action="options.php">
         <?php
         settings_fields("woocommerce_posnet_printer_config");
         do_settings_sections("posnet-printer-for-woocommerce");      //Prints out all settings sections added to a particular settings page
         submit_button();
         ?>
      </form>
      
   <?php } ?>
   
   <!-- END OF TABS -->
    
</div>
 
<?php
}

/**
 * Init setting section, Init setting field and register settings page
 *
 * @since 1.0
 */
function posnet_printer_for_woocommerce_settings() {   
   register_setting("woocommerce_posnet_printer_config", "woocommerce_posnet_printer_config");
   add_settings_section("woocommerce_posnet_printer_main_section", "", null, "posnet-printer-for-woocommerce");
   add_settings_field("woocommerce-posnet-printer-serverurl-text", "Posnet Server", "posnet_printer_for_woocommerce_serverurl_options", "posnet-printer-for-woocommerce", "woocommerce_posnet_printer_main_section");
   add_settings_field("woocommerce-posnet-printer-extralines-text", "Linie informacyjne", "posnet_printer_for_woocommerce_extralines_options", "posnet-printer-for-woocommerce", "woocommerce_posnet_printer_main_section");
}
add_action("admin_init", "posnet_printer_for_woocommerce_settings");


function posnet_printer_for_woocommerce_serverurl_options() {
   $configdata = posnet_printer_for_woocommerce_readPluginConfiguration();
   // print_r($configdata);
   $PosnetServerHost = $configdata['PosnetServerHost'];
?>
<div class="postbox" style="width: 65%; padding: 30px;">
	<input type="text" id="woocommerce-posnet-printer-serverurl-text" name="woocommerce_posnet_printer_config[PosnetServerHost]"
		value="<?php echo $PosnetServerHost; ?>" />Podaj URL serwisu Posnet Server (domyślnie: http://127.0.0.1:3050)<br />
      <br /><br />
      Pobierz najnowszą wersję Posnet Server dla swojego systemu operacyjnego: <a href="https://blog.bigdotsoftware.pl/posnet-server-instalacja/">https://blog.bigdotsoftware.pl/posnet-server-instalacja/</a>
</div>
<?php
}

function posnet_printer_for_woocommerce_extralines_options() {
   $configdata = posnet_printer_for_woocommerce_readPluginConfiguration();
   // print_r($configdata);
   $ExtraLines = $configdata['ExtraLines'];
?>
<div class="postbox" style="width: 65%; padding: 30px;">
	<textarea id="woocommerce-posnet-printer-extralines-text" name="woocommerce_posnet_printer_config[ExtraLines]" rows="10" cols="120"><?php echo $ExtraLines; ?></textarea><br/>
   Dodatkowe linie paragonu, dokumentacja: <a href="https://blog.bigdotsoftware.pl/posnet-server-wydruk-paragonu/">https://blog.bigdotsoftware.pl/posnet-server-wydruk-paragonu/</a><br />
</div>
<?php
}

/*
add_filter('the_content', 'woocommerce_posnet_printer_content');
function woocommerce_posnet_printer_content($content) {
	return $content . stripslashes_deep(esc_attr(get_option('woocommerce-posnet-printer-serverurl-text')));
}
*/
