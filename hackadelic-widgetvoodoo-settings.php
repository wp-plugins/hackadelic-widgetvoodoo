<?php
if ( !defined('ABSPATH') )
	exit("Sorry, you are not allowed to access this page directly.");
if ( !isset($this) || !is_a($this, HackadelicWidgetVoodoo) )
	exit("Invalid operation context.");

$anySuggestions = $this->getSuggestions($suggestions);

$options = array(
	array(
		'optitle' => 'Widget Selector', 
		'opkey' => 'WIDGET_WRAP_SELECTOR',
		'opval' => $this->WIDGET_WRAP_SELECTOR,
		'ophlp' => 'The CSS-style selector that identifies widgets as a whole.'
		),
	array(
		'optitle' => 'Title Selector', 
		'opkey' => 'WIDGET_TITLE_SELECTOR',
		'opval' => $this->WIDGET_TITLE_SELECTOR,
		'ophlp' => 'The CSS-style selector that identifies widget titles <b>within</b> a widget.'
		),
	array(
		'optitle' => 'Auto-collapsed Widgets Selector', 
		'opkey' => 'AUTOCOLLAPSE_SELECTOR',
		'opval' => $this->AUTOCOLLAPSE_SELECTOR,
		'ophlp' => 'A comma-sepearated list of CSS-style selectors for widgets to automatically collapse.'
		),
);

$slugHome = $slugWP = 'widgetvoodoo';
$pluginTitle = $this->PLUGIN_TITLE;
$admPageTitle = "$pluginTitle " . $this->t('Settings');
include 'hackadelic-widgetvoodoo-admx.php';
?>
<style>
table#sidebar-data td.sidebar-info {
	border: double #ccc;
	padding-top: 1em;
}
</style>

<?php if ($updated) : ?>
<div class="updated fade"><p>Plugin settings saved.</p></div>
<?php endif ?>

<form method="post" action="<?php echo $actionURL ?>">
<input type="hidden" name="action" value="update" />
<?php wp_nonce_field($context); ?>

<table class="form-table" style="clear:none">

<?php foreach ($options as $each) :	extract($each) ?>
<tr>
<th scope="row" style="border:1px solid #eee"><?php $this->e($optitle) ?></th>
<td style="border:1px solid #eee">
	<input type="text" name="<?php echo $opkey ?>" value="<?php echo $opval ?>" style="width:100%" />

	<div><em><?php $this->e($ophlp) ?></em>
<?php $suggested = $suggestions['for'][$optitle]; if ($suggested) : ?>
<?php $suggestionID = "props-$opkey" ?>
		<em><span class="button" 
			onclick="jQuery('#<?php echo $suggestionID ?>').slideToggle('fast')"><?php $this->e('Suggestions') ?>&nbsp;&raquo;</span></em>
		<ol class="hidden" id="<?php echo $suggestionID ?>"
			style="margin: 1em 2em 0 2em; list-style-type: decimal">
			<?php foreach ($suggested as $s) : ?><li><tt><?php echo $s ?></tt></li><?php endforeach ?>
		</ol>
<?php endif ?>
	</div>
</td>
</tr>
<?php endforeach ?>

<tr>
<?php //if ($anySuggestions) : ?>
<?php if ($suggestions['complete']) : ?>
<td colspan="2" style="border:1px solid #eee; background-color: whitesmoke">
<?php $this->e("The above suggestions are based on sidebar data from your current theme, and your currently active widgets.") ?>
<?php else : ?>
<td colspan="2" style="border:1px solid #eee; background-color: navajowhite">
<?php
	$manual = sprintf(
		'<a href="http://hackadelic.com/solutions/wordpress/widgetvoodoo/theme-tweaking-manual">%s</a>',
		$this->t('Widget Voodoo Theme Tweaking Manual')
		);
	$this->e(
"Could not sufficiently derive suggestions from your current configuration.
Your theme does not seem to satisfy the criteria required for Widget Voodoo to operate.
But don't despair. The requirements are minimal, and tweaking your theme
to meet them is a breeze. Let me walk you through it in the ${manual}.");
endif;
?>

<?php $ncols = $odd = 0 ?>
<table id="sidebar-data">
<tr><td colspan="2"><b><?php $this->e('Your current theme\'s sidebar data:') ?></b></td></tr>
<tr valign="top" class="sidebar-info">
<?php foreach ($suggestions['sidebar-data'] as $lines) : ?>
<td class="sidebar-info">
<?php foreach ($lines as $s) : ?>
	<div style="border:1px solid #eee"><?php echo $s ?></div>
<?php endforeach ?>
</td>
<?php if (++$ncols > 1): $ncols = 0; $odd = !$odd; ?>
</tr>
<tr valign="top" class="notfirst sidebar-info">
<?php endif ?>
<?php endforeach ?>
</tr>
</table>

</td>
</tr>

</table>

<p class="submit"><input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" /></p>
</form>

</div>
