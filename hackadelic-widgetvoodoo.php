<?php 
/*
Plugin Name: Hackadelic WidgetVoodoo
Version: 1.0.4
Plugin URI: http://hackadelic.com/solutions/wordpress/widgetvoodoo
Description: Morphs sidebar widgets into cool, collapsible AJAX-type citizens.
Author: Hackadelic
Author URI: http://hackadelic.com
*/
//---------------------------------------------------------------------------------------------

class HackadelicWidgetVoodooContext
{
	function CTXID() { return get_class($this); }

	// I18N -------------------------------------------------------------------------------

	function t($s) { return __($s, $this->CTXID());	}
	function e($s) { _e($s, $this->CTXID());	}

	// Option Access ----------------------------------------------------------------------

	function fullname($name) {
		return $this->CTXID() . '__' . $name;
	}
	function load_option(&$option, $name, $eval=null) {
		$name = $this->fullname($name);
		$value = get_option($name);
		if ($value == null) return false;
		$option = ($eval == null) ? $value : call_user_func($eval, $value);
		return true;
	}
	function save_option(&$option, $name) {
		$name = $this->fullname($name);
		update_option($name, $option);
	}
	function erase_option(&$option, $name) {
		$name = $this->fullname($name);
		delete_option($name);
		$option = null;
	}
}

//---------------------------------------------------------------------------------------------

class HackadelicWidgetVoodoo extends HackadelicWidgetVoodooContext
{
	var $PLUGIN_TITLE = 'Hackadelic Widget Voodoo';
	var $VERSION = '1.0.4';
	
	var $WIDGET_WRAP_SELECTOR = '.widget';
	var $WIDGET_TITLE_SELECTOR = '.widgettitle';
	var $AUTOCOLLAPSE_SELECTOR = '';

	function setup() {
		$trim = array(&$this, 'trim');
		$this->load_option($this->WIDGET_WRAP_SELECTOR, 'WIDGET_WRAP_SELECTOR', $trim);
		$this->load_option($this->WIDGET_TITLE_SELECTOR, 'WIDGET_TITLE_SELECTOR', $trim);
		$this->load_option($this->AUTOCOLLAPSE_SELECTOR, 'AUTOCOLLAPSE_SELECTOR', $trim);

		if (is_admin()):
			add_action('admin_menu', array(&$this, 'addAdminMenu'));
		elseif ($this->WIDGET_WRAP_SELECTOR && $this->WIDGET_TITLE_SELECTOR) :
			add_action('wp_print_scripts', array(&$this, 'embedScripts'));
		endif;
	}

	//-------------------------------------------------------------------------------------

	function trim($s) {
		return preg_replace('/^\s+|\s+$/', '', $s);
	}

	//-------------------------------------------------------------------------------------

	function embedScripts() {
		wp_enqueue_script('jquery');

		$pluginURL = WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__));
		$pluginSlug = substr(basename(__FILE__), 0, -4);
		$basePath = "${pluginURL}/${pluginSlug}";
		//$cssPath = $basePath . '.css';
		$jsPath = $basePath . '.js';

		wp_enqueue_script($pluginSlug, $jsPath);
		wp_localize_script($pluginSlug, 'WidgetVoodooSelectors', array(
			'widget' => $this->WIDGET_WRAP_SELECTOR,
			'title' => $this->WIDGET_TITLE_SELECTOR,
			'autocollapse' => $this->AUTOCOLLAPSE_SELECTOR,
		));
	}

	//=====================================================================================
	// ADMIN
	//=====================================================================================

	function addAdminMenu() {
		$title = $this->PLUGIN_TITLE;
		add_options_page($title, $title, 10, __FILE__, array(&$this, 'displayOptionsPage'));
	}

	//-------------------------------------------------------------------------------------

	function suggestFor($subject, $what, &$suggestions) {
		$target =& $suggestions['for'][$subject];
		if ($target && in_array($what, $target)) return false;
		$target[] = $what;
	}

	//-------------------------------------------------------------------------------------

	function getSuggestions(&$suggestions) {
		$count = 0;

		global $wp_registered_sidebars;
		foreach ($wp_registered_sidebars as $sbname => $sb) :
			unset($s);
			foreach ($sb as $k => $v) :
				$k = htmlentities($k);
				$v = htmlentities($v);
				$s[] =  "<tt>'$k' => '$v'</tt>";
			endforeach;
			$suggestions['sidebar-data'][] = $s;

			$sbid = $sb['id'];
			
			$got1 = $got2 = 0;

			$found = preg_match('@<(\w+)\s+.*?class="(.+?)".*?>@i', $sb['before_widget'], $matches);
			if ($found) :
				$widgetTag = $matches[1]; $widgetClasses = $matches[2];
				$found = preg_match_all('@[A-Z][0-9A-Z_\-]+@i', $widgetClasses, $matches);
				if ($found) :
					foreach ($matches[0] as $widgetClass):
						$this->suggestFor('Widget Selector', ".$widgetClass", $suggestions);
						$this->suggestFor('Widget Selector', "$widgetTag.$widgetClass", $suggestions);
						$this->suggestFor('Widget Selector', "#$sbid .$widgetClass", $suggestions);
						$this->suggestFor('Widget Selector', "#$sbid $widgetTag.$widgetClass", $suggestions);
						$count += 4;
					endforeach;
					$got1 = true;
				endif;
			endif;
			$found = preg_match('@<(\w+)\s+.*?class="(.*?)">@i', $sb['before_title'], $matches);
			if ($found) :
				$titleTag = $matches[1]; $titleClasses = $matches[2];
				$found = preg_match_all('@[A-Z][0-9A-Z_\-]+@i', $titleClasses, $matches);
				if ($found) :
					foreach ($matches[0] as $titleClass):
						$this->suggestFor('Title Selector', ".$titleClass", $suggestions);
						$this->suggestFor('Title Selector', "$titleTag.$titleClass", $suggestions);
						$this->suggestFor('Title Selector', "$titleTag", $suggestions);
						$count += 3;
					endforeach;
					$got2 = true;
				endif;
			endif;
		endforeach;

		if ($count) :
			global $wp_registered_widgets;
			foreach ($wp_registered_widgets as $name => $widget) :
				$wid = $widget['id'];
				if (!is_active_widget( $widget['callback'], $wid)) continue;
				$this->suggestFor('Auto-collapsed Widgets Selector', "#$wid", $suggestions);
				$count += 1;
			endforeach;
		endif;
		$suggestions['complete'] = $got1 && $got2;
		//exit('$got1 = ' . $got1 . '; $got2 = ' . $got2 . '; $got1 && $got2 = ' . ($got1 && $got2) . ';');
		return $count;
	}

	//-------------------------------------------------------------------------------------

	function displayOptionsPage() {
		include 'hackadelic-widgetvoodoo-settings.php';
	}
}

//---------------------------------------------------------------------------------------------

$hackadelicWidgetVoodoo = new HackadelicWidgetVoodoo();
$hackadelicWidgetVoodoo->setup();

?>