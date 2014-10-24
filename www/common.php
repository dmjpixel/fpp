<?php 
require_once("config.php");

function check($var, $var_name = "", $function_name = "")
{
	if ( empty($function_name) )
	{
		global $args;
		$function_name = $args['command'];
	}

	if ( empty($var) )
		error_log("WARNING: Variable '$var_name' in function '$function_name' was empty");
}


function ReadSettingFromFile($settingName, $plugin = "")
{
	global $settingsFile;
	global $settings;
	$filename = $settingsFile;

	if ($plugin != "") {
		$filename = $settings["configDirectory"] . "/plugin." . $plugin;
	}

	$settingsStr = file_get_contents($filename);
  if ( !empty($settingsStr) )
  {
		if (preg_match("/^" . $settingName . "/m", $settingsStr))
		{
      $result = preg_match("/^" . $settingName . "\s*=(\s*\S*\w*)/m", $settingsStr, $output_array);
      if($result == 0)
      {
        error_log("The setting " . $settingName . " could not be found in " . $filename);
        return false;
      }
      return trim($output_array[1]);
		}
		else
		{
      error_log("The setting " . $settingName . " could not be found in " . $filename);
			return false;
		}
  }
	else
	{
    error_log("The setting file:" . $filename . " could not be found." );
		return false;
	}
}

function WriteSettingToFile($settingName, $setting, $plugin = "")
{
	global $settingsFile;
	global $settings;
	$filename = $settingsFile;

	if ($plugin != "") {
		$filename = $settings['configDirectory'] . "/plugin." . $plugin;
	}

	$settingsStr = "";
	$tmpSettings = parse_ini_file($filename);
	$tmpSettings[$settingName] = $setting;
	foreach ($tmpSettings as $key => $value) {
		$settingsStr .= $key . " = " . $value . "\n";
	}
	file_put_contents($filename, $settingsStr);
}

function IfSettingEqualPrint($setting, $value, $print, $pluginName = "")
{
	global $settings;
	global $pluginSettings;

	if ($pluginName != "") {
		if ((isset($pluginSettings[$setting])) &&
			($pluginSettings[$setting] == $value ))
			echo $print;
	} else {
		if ((isset($settings[$setting])) &&
			($settings[$setting] == $value ))
			echo $print;
	}
}

function PrintSettingCheckbox($title, $setting, $checkedValue, $uncheckedValue, $pluginName = "", $callbackName = "")
{
	global $settings;
	global $pluginSettings;

	$plugin = "";
	$settingsName = "settings";

	if ($pluginName != "") {
		$plugin = "Plugin";
		$settingsName = "pluginSettings";
	}

	if ($callbackName != "")
		$callbackName = $callbackName . "();";

	echo "
<script>
function " . $setting . "Changed() {
	var value = '$uncheckedValue';
	var checked = 0;
	if ($('#$setting').is(':checked')) {
		checked = 1;
		value = '$checkedValue';
	}

	$.get('fppjson.php?command=set" . $plugin . "Setting&plugin=$pluginName&key=$setting&value=' + value)
		.success(function() {
			if (checked)
				$.jGrowl('$title Enabled');
			else
				$.jGrowl('$title Disabled');
			$settingsName" . "['$setting'] = value;
			$callbackName
		}).fail(function() {
			if (checked) {
				DialogError('$title', 'Failed to Enable $title');
				$('#$setting').prop('checked', false);
			} else {
				DialogError('$title', 'Failed to Disable $title');
				$('#$setting').prop('checked', true);
			}
		});
}
</script>

<input type='checkbox' id='$setting' ";

	IfSettingEqualPrint($setting, $checkedValue, "checked", $pluginName);

	echo " onChange='" . $setting . "Changed();'>\n";
}

function PrintSettingSelect($title, $setting, $defaultValue, $values, $pluginName = "", $callbackName = "")
{
	global $settings;
	global $pluginSettings;

	$plugin = "";
	$settingsName = "settings";

	if ($pluginName != "") {
		$plugin = "Plugin";
		$settingsName = "pluginSettings";
	}

	if ($callbackName != "")
		$callbackName = $callbackName . "();";

	echo "
<script>
function " . $setting . "Changed() {
	var value = $('#$setting').val();

	$.get('fppjson.php?command=set" . $plugin . "Setting&plugin=$pluginName&key=$setting&value=' + value)
		.success(function() {
			$.jGrowl('$title saved');
			$settingsName" . "['$setting'] = value;
			$callbackName
		}).fail(function() {
			DialogError('$title', 'Failed to save $title');
		});
}
</script>

<select id='$setting' onChange='" . $setting . "Changed();'>\n";

	foreach ( $values as $key => $value )
	{
		echo "<option value='$value'";

		if (isset($pluginSettings[$setting]))
			IfSettingEqualPrint($setting, $value, " selected", $pluginName);
		else if ($value == $defaultValue)
			echo " selected";

		echo ">$key</option>\n";
	}

	echo "</select>\n";
}

function PrintSettingText($setting, $maxlength = 32, $size = 32, $pluginName = "")
{
	global $settings;
	global $pluginSettings;

	$plugin = "";
	$settingsName = "settings";

	if ($pluginName != "") {
		$plugin = "Plugin";
		$settingsName = "pluginSettings";
	}

	echo "
<input type='text' id='$setting' maxlength='$maxlength' size='$size' value='";

	if (isset($settings[$setting]))
		echo $settings[$setting];
	elseif (isset($pluginSettings[$setting]))
		echo $pluginSettings[$setting];

	echo "'>\n";
}

function PrintSettingSave($title, $setting, $pluginName = "", $callbackName = "")
{
	global $settings;
	global $pluginSettings;

	$plugin = "";
	$settingsName = "settings";

	if ($pluginName != "") {
		$plugin = "Plugin";
		$settingsName = "pluginSettings";
	}

	if ($callbackName != "")
		$callbackName = $callbackName . "();";

	echo "
<script>
function save" . $setting . "() {
	var value = $('#$setting').val();

	$.get('fppjson.php?command=set" . $plugin . "Setting&plugin=$pluginName&key=$setting&value=' + value)
		.success(function() {
			$.jGrowl('$title saved');
			$settingsName" . "['$setting'] = value;
			$callbackName
		}).fail(function() {
			DialogError('$title', 'Failed to save $title');
			$('#$setting').prop('checked', false);
		});
}
</script>

<input type='button' class='buttons' id='save$setting' ";

	IfSettingEqualPrint($setting, $checkedValue, "checked", $pluginName);

	echo " onClick='save" . $setting . "();' value='Save'>\n";
}

?>

