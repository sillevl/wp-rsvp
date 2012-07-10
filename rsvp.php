<?php
/*
Plugin Name: Sille's RSVP Plugin
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: The Plugin's Version Number, e.g.: 1.0
Author: Sille Van Landschoot
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

require_once('RsvpItem.php');
require_once('RsvpDb.php');


class RSVP_SILLE {

	//public $tablename_rsvp_item = "";
	private $rsvpDb;

	function RSVP_SILLE(){
		$this->rsvpDb = new RsvpDb();
		add_action( 'admin_menu', array($this,'my_plugin_menu') );
		register_activation_hook(__FILE__, array($this,'db_install'));
		add_shortcode( 'foobar', array($this, 'foobar_func' ));
		add_shortcode( 'rsvp', array($this, 'rsvp_page' ));
		add_action('wp_print_styles', array($this,'add_css'));
	}

	function my_plugin_menu() {
		add_menu_page('RSVP', 'RSVP', 'manage_options', 'rsvp_handle', array($this, 'my_plugin_options'));
		add_submenu_page( 'rsvp_handle', 'Overzicht', 'Overzicht', 'manage_options', 'overview', array($this, 'my_plugin_options'));
		add_submenu_page( 'rsvp_handle', 'Instellingen', 'Instellingen', 'manage_options', 'settings', array($this, 'my_plugin_options'));
	}

	function my_plugin_options() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		echo '<div class="wrap">';
		echo '<p>Here is where the form would go if I actually had options.</p>';
		echo '</div>';
	}

	function db_install(){
		$this->rsvpDb->install();
	}

		//[foobar]
	function foobar_func( $atts ){
	 return "<h1>boe ba en boef</h1>foo and bar and test";
	}

	function add_css(){
		$myStyleFile =  plugin_dir_url(__FILE__) . 'style.css';
		wp_register_style('RsvpCSS', $myStyleFile);
		wp_enqueue_style( 'RsvpCSS');
		wp_register_style('Tipsy', plugin_dir_url(__FILE__) . "tipsy/tipsy.css");
		wp_enqueue_style('Tipsy');
	}

	function rsvp_page($atts){
		wp_register_script( 'rsvp_sille', plugins_url('/script.js', __FILE__) );
		wp_enqueue_script("rsvp_sille");
		wp_enqueue_script("jquery");

		wp_register_script('Tipsy', plugins_url('tipsy/jquery.tipsy.js', __FILE__));
		wp_enqueue_script('Tipsy');

		$oops = "";

		if(!isset($_POST['step'])){
			$step = "init";

		} else {
			$step = $_POST['step'];
		}

		$validRsvps = true;

		if($_POST['submit']){
			if(count($_POST['item']) == 0){$validRsvps = false;} 
			
			foreach ($_POST['item'] as $key => $value) {
				$item = new RsvpItem();
				$item->name = esc_attr(strip_tags($value['forname']));
				$item->prename = esc_attr(strip_tags($value['prename']));
				$item->attending = $value['attending'];
				$item->vegitarian = $value['vegi'];
				$item->comment = esc_attr(strip_tags($value['comment']));

				$list[] = $item;

				$validRsvps = $validRsvps && $item->isValid();
				
				if(!$item->isValid()){
					$oops .= "Oeps, er is iets fout";
				}
			}

			if($validRsvps){
				$this->rsvpDb->saveRequests($list);
			}

		} 

		if($step == "init"){
			$list = array(new RsvpItem());
			$output = $this->getForm($list);
		} elseif ($step == "first") {
			if($validRsvps){
				$output = $this->confirmMessage($list);
			} else {
				$output = $this->getForm($list, $oops);
			}
		}
		
		return $output;
	}

	function getForm($list, $oops = ""){
		$output = "<p><span class=\"drop-caps\">H</span>ier kunnen jullie zich inschrijven voor onze fantastische dag. Vul voor elke genodigde de naam en voornaam in, en bevestig of deze al dan niet zal komen. Extra opmerkingen of een vegetarische maaltijd kunnen ook aangegeven worden zodat wij daar ook rekening mee kunnen houden.</p><p>Extra personen kunnen geregistreerd worden door onderaan op de (+) knop te drukken.</p><p>De dagindeling en agenda kan je <a href=\"/15-september-2012/\">hier</a> nog eens bekijken</p>";
		$output .= "<form method=\"post\" class=\"rsvp-form\" id=\"rsvp-form\">";
		$output .= "<input type=\"hidden\" name=\"step\" value=\"first\" />";
		$displayMessage = ($oops == "") ? "style=\"display: none;\" " :  "";
		$output .= "<div id=\"message-box\" class=\"error-box\" ".$displayMessage.">".$oops."</div>";
		$output .= "<div style=\"clear:both;\"></div>";
		$output .= "<span class=\"double-line\" style=\"clear: both;\">&nbsp;</span>";
		foreach($list as $key => $item){
			$output .= "<div class=\"personItem\" id=\"personRegistration".$key."\">";
			$output .= "<div  style=\"float: left; width: 350px; height:157px;\">";
			$output .= "<p>";
			$output .= "<label>Achternaam:</label>";
			$output .= "<input type=\"text\" name=\"item[".$key."][forname]\" value=\"".stripslashes($item->name)."\" />";
			$output .= "</p>";
			$output .= "<p>";
			$output .= "<label>Voornaam:</label>";
			$output .= "<input type=\"text\" name=\"item[".$key."][prename]\" value=\"".stripslashes($item->prename)."\" />";
			$output .= "</p>";

			$attendingyes = ($item->attending == "yes") ? "checked=\"checked\"" : "";
			$attendingno = ($item->attending == "no") ? "checked=\"checked\"" : "";
			$output .= "<p>";
			$output .= "<label>Zal aanwezig zijn:</label>";
			$output .= "<span id=\"attendBox".$key."\" style=\"padding:3px;\" >";
			$output .= "<input type=\"radio\" name=\"item[".$key."][attending]\" value=\"yes\" class=\"extrainfo".$key."\" ".$attendingyes.">Ja</input>";
			$output .= "<input type=\"radio\" name=\"item[".$key."][attending]\" value=\"no\" class=\"extrainfo".$key."\" ".$attendingno.">Nee</input>";
			$output .= "</span>";
			$output .= "</p>";

			$output .= "</div>";
			$output .= "<div style=\"float: left; width: 150px;\" id=\"extrainfo".$key."\" class=\"extrainfo\">";

			$vegichecked = ($item->vegitarian == "yes") ? "checked=\"checked\"" : "";
			$output .= "<p>";
			$output .= "<label id=\"vegilabel\"></label>";
			$output .= "<input type=\"checkbox\" name=\"item[".$key."][vegi]\" value=\"yes\" ".$vegichecked.">Vegetarisch</input>";
			$output .= "</p>";

			$output .= "<p>";
			$output .= "<label>Opmerkingen:</label>";
			$output .= "<textarea rows=\"3\" name=\"item[".$key."][comment]\" value=\"yes\" tooltip=\"Kom je wat later, vroeger of wil je nog iets melden, doe het gerust hier.\">".stripslashes($item->comment)."</textarea>";
			$output .= "</p>";

			$output .= "</div>";
			$output .= "<div class=\"number\" style=\"float: right; font-style: italic; padding: 5px;\"># 1</div>";
			$output .= "<span class=\"double-line\" style=\"clear: both; margin-bottom: 10px;\">&nbsp;</span>";
			$output .= "</div>";
		}

		$output .= "<p>";
		$output .= "<input type=\"button\" value=\"+\" name=\"add\" id=\"buttonAdd\" class=\"button\" tooltip=\"Nieuwe persoon toevoegen.\"/>";
		$output .= "<input type=\"button\" value=\"-\" name=\"remove\" id=\"buttonRemove\" class=\"button\" tooltip=\"Laatste persoon verwijderen.\"/>";
		$output .= "Aantal inschrijvingen bijwerken.";
		$output .= "</p>";
		$output .= "<p style=\"text-align: right;\">";
		$output .= "Alles en voor iedereen ingevuld? Druk dan op verzenden.";
		$output .= "<input type=\"submit\" value=\"verzenden\" name=\"submit\" class=\"button\"/>";
		$output .= "</p>";
		$output .= "</form>";
		return $output;
	}

	function confirmMessage($list){
		$output = '';
		//$output .= '<span class="double-line">&nbsp;</span>';
		$output .= '<div id="intro" style="margin-bottom: 0px;"><h1>Bedankt voor de bevestiging</h1></div>';
		$output .= '<span class="double-line">&nbsp;</span>';
		$output .= '<p>Even samenvatten:</p>';
		$attendingList = array();
		$excusedList = array();
		foreach ($list as $key => $item) {
			if($item->isAttending()){
				$attendingList[] = $item;
			} else {
				$excusedList[] = $item;
			}
		}
		if(count($attendingList) != 0){
			$output .= "<p>Volgende personen zullen aanwezig zijn:</p>";
			$output .= '<ul class="bullet_arrow2 imglist">';
			foreach ($attendingList as $key => $item) {
				$output .= '<li>'.$item->prename.' '.$item->name.'</li>';
			}
			$output .= '</ul>';
			$output .= '<p>We kijken er naar uit jullie te mogen ontvangen.</p>';
		}
		if(count($excusedList) != 0){
			$output .= "<p>Volgende personen zullen niet aanwezig zijn:</p>";
			$output .= '<ul class="bullet_arrow2 imglist">';
			foreach ($excusedList as $key => $item) {
				$output .= '<li>'.$item->prename.' '.$item->name.'</li>';
			}
			$output .= '</ul>';
			$output .= '<p>We vinden het jammer dat jullie er niet bij zullen zijn, maar toch bedankt.</p>';
		}

		$output .= '<p>Cadeau Tip ! + Overzicht dag</p>';
		return $output;
	}

}

global $rsvp_sille;
if (class_exists("RSVP_SILLE") && !$rsvp_sille) {
    $rsvp_sille = new RSVP_SILLE();	
}



?>