<?php
//Prevent direct script access.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//User Table Settings Page
function User_Table_Meta_Editor_menu() {
	//Load the page.
	add_options_page( 'User Table Meta Editor', 'User Table Meta Editor', 'manage_options', 'User_Table_Meta_Editor_menu_page', 'User_Table_Meta_Editor_menu_page' );
	add_settings_section("User_Table_Meta_Editor_menu_section", "User Table Meta Editor", null, "User_Table_Meta_Editor_menu_page");
  add_settings_field("User_Table_Meta_Editor_menu_options", "Meta Fields(comma seperated):", "User_Table_Meta_Editor_menu_display_options", "User_Table_Meta_Editor_menu_page", "User_Table_Meta_Editor_menu_section");
  register_setting("User_Table_Meta_Editor_menu_section", "User_Table_Meta_Editor_menu_options");
}
//Display options field.
function User_Table_Meta_Editor_menu_display_options()
{
	?>
    	<input type="text" name="User_Table_Meta_Editor_menu_options" id="User_Table_Meta_Editor_menu_options" size="100" value="<?php echo get_option('User_Table_Meta_Editor_menu_options'); ?>" />
    <?php
}
//Generate email menu page.
function User_Table_Meta_Editor_menu_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
	<div class="wrap">
	<form method="post" action="options.php"><table>
		<?php
	            settings_fields("User_Table_Meta_Editor_menu_section");
				      do_settings_sections("User_Table_Meta_Editor_menu_page");
	            submit_button();
	        ?>
	</form>
	</div>
<?php
}
//Add table page.
function User_Table_Meta_Editor_table_page()
{
  //Load editor page
  add_users_page( "User Table Meta Editor", "User Table Meta Editor", "read", "User_Table_Meta_Editor_table", "User_Table_Meta_Editor_table");
}
//User table page
function User_Table_Meta_Editor_table()
{
		User_Table_Meta_Editor_table_display();
}
function User_Table_Meta_Editor_table_display()
{
	//Get users.
	$users = get_users();
	$USERS = array();
	$COLUMS = explode(",",get_option('User_Table_Meta_Editor_menu_options'));
	//Generate $USERS array.
	foreach($users as $user)
	{
		$USER["ID"] = $user->ID;
		foreach($COLUMS as $COLUM)
		{
			$USER[$COLUM] = get_user_meta($user->ID, $COLUM, true);
		}
		array_push($USERS, $USER);
	}
	?>
	<!-- Basic table styling. -->
	<style>
	.usergrid
	{
		background-color: white;
	}
	.usergrid th {
		background-color: white;
	}
	.usergrid th, td {
		padding: 15px;
		text-align: left;
	}
	</style>
	<!-- Base Html -->
	<br>
	<button onclick="exportCSV()" type="button">Export CSV</button>
	<br>
	<input type="search" class="light-table-filter" data-table="usergrid" placeholder="Filter">
	<div id="usertable"></div>
	<p id="updates"></p>
	<script>
	//Filter table function.
	(function(document) {
	'use strict';

	var LightTableFilter = (function(Arr) {

		var _input;

		function _onInputEvent(e) {
			_input = e.target;
			var tables = document.getElementsByClassName(_input.getAttribute('data-table'));
			Arr.forEach.call(tables, function(table) {
				Arr.forEach.call(table.tBodies, function(tbody) {
					Arr.forEach.call(tbody.rows, _filter);
				});
			});
		}

		function _filter(row) {
			var text = row.textContent.toLowerCase(), val = _input.value.toLowerCase();
			row.style.display = text.indexOf(val) === -1 ? 'none' : 'table-row';
		}

		return {
			init: function() {
				var inputs = document.getElementsByClassName('light-table-filter');
				Arr.forEach.call(inputs, function(input) {
					input.oninput = _onInputEvent;
				});
			}
		};
	})(Array.prototype);

	document.addEventListener('readystatechange', function() {
		if (document.readyState === 'complete') {
			LightTableFilter.init();
		}
	});

})(document);
	//Load table data.
	window.onload = function() {
				// Set header and data variables.
				var metadata = [];
				var data = [];
				<?php
				//Add id colum.
				echo 'metadata.push({ name: "ID", label: "ID", datatype: "string", editable: false});';
				//Add all other colums.
				foreach($COLUMS as $COLUM)
				{
					$edit = "true";
					echo 'metadata.push({ name: "'.$COLUM.'", label: "'.$COLUM.'", datatype: "string", editable: '.$edit.'});';
				}
				//Add data.
				foreach($USERS as $USER)
				{
					//The intial id colum.
					echo 'data.push({id: '.$USER["ID"].', values:['.$USER["ID"].',';
					$x = 0;
					//Add all the other colums.
					foreach($COLUMS as $COLUM)
					{
						$x++;
						//If meta is a array convert it to string.
						if(is_array($USER[$COLUM]))
						{
							echo '"';
							$y = 0;
							foreach ($USER[$COLUM] as $key => $value)
							{
								$y++;
								echo $key.": ".$value;
								if($y < count($USER[$COLUM]))
								{
									echo ",";
								}
							}
							echo '"';
						}
						else
						{
							echo '"'.$USER[$COLUM].'"';
						}
						if($x < count($COLUMS))
						{
							echo ',';
						}
					}
					echo ']});';
				}
				?>
				editableGrid = new EditableGrid("DemoGridJsData", {
					//Cell edit function.
					modelChanged: function(rowIdx, colIdx, oldValue, newValue, row)
						{
							//document.getElementById("updates").innerHTML = "Updated "+rowIdx+", "+colIdx+": from "+oldValue+" to "+newValue+".";
							xhr = new XMLHttpRequest();
							xhr.open('POST',encodeURI('<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php'));
							xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							xhr.onload = function() {
							    if (xhr.status !== 200) {
							        alert('Request failed.  Returned status of ' + xhr.status);
							    }
									else {
										document.getElementById("updates").innerHTML = xhr.responseText;
									}
							};
							data = "";
							data = data + "&oldValue="+oldValue;
							data = data + "&newValue="+newValue;
							data = data + "&field="+metadata[colIdx]["name"];
							data = data + "&user="+row.cells[0].innerHTML;
							xhr.send(encodeURI('action=' + 'User_Table_Meta_Editor_table_edit' + data));
						}
				});
				editableGrid.load({"metadata": metadata, "data": data});
				editableGrid.renderGrid("usertable", "usergrid");
			}
			function exportCSV()
			{
				window.open('<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php?action=User_Table_Meta_Editor_table_save_csv');
			}
		</script>
	<?php
}
//Update user meta.
function User_Table_Meta_Editor_table_edit()
{
	$old = get_user_meta($_POST["user"], $_POST["field"], true);
	if(is_array($old))
	{
		$new = explode(",",$_POST["newValue"]);
		foreach($new as $row)
		{
			$values = explode(": ",$row);
			$newArray[$values[0]] = $values[1];
		}
		if(update_user_meta( $_POST["user"], $_POST["field"], $newArray, $old ))
		{
			echo "Updated ".$_POST["field"].": from ".$_POST["oldValue"]." to ".$_POST["newValue"]." on user: ".$_POST["user"].".";
		}
		else {
			echo "Update failed.";
		}
	}
	else {
		if(update_user_meta( $_POST["user"], $_POST["field"], $_POST["newValue"], $_POST["oldValue"] ))
		{
			echo "Updated ".$_POST["field"].": from ".$_POST["oldValue"]." to ".$_POST["newValue"]." on user: ".$_POST["user"].".";
		}
		else {
			echo "Update failed.";
		}
	}
	wp_die();
}
//Export csv
function User_Table_Meta_Editor_table_save_csv()
{
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=users.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	$COLUMS = explode(",",get_option('User_Table_Meta_Editor_menu_options'));
	echo "ID,".get_option('User_Table_Meta_Editor_menu_options')."\n";
	$users = get_users();
	foreach($users as $user)
	{
		echo $user->ID.",";
		$x = 0;
		foreach($COLUMS as $COLUM)
		{
			$x++;
			$field = get_user_meta($user->ID, $COLUM, true);
			if(is_array($field))
			{
				$y = 0;
				foreach($field as $key => $value)
				{
					$y++;
					echo $key.": ".$value;
					if($y < count($field))
					{
						echo "|";
					}
				}
			}
			else
			{
				echo $field;
			}
			if($x < count($COLUMS))
			{
				echo ",";
			}
			else
			{
				echo "\n";
			}
		}
	}
	wp_die();
}
//Load scripts.
function User_Table_Meta_Editor_scripts()
{
    wp_register_script( 'editablegrid', plugins_url( '/js/editablegrid.js', __FILE__ ) );
    wp_enqueue_script( 'editablegrid' );
    wp_register_script( 'editablegrid_editors', plugins_url( '/js/editablegrid_editors.js', __FILE__ ) );
    wp_enqueue_script( 'editablegrid_editors' );
    wp_register_script( 'editablegrid_renderers', plugins_url( '/js/editablegrid_renderers.js', __FILE__ ) );
    wp_enqueue_script( 'editablegrid_renderers' );
    wp_register_script( 'editablegrid_validators', plugins_url( '/js/editablegrid_validators.js', __FILE__ ) );
    wp_enqueue_script( 'editablegrid_validators' );
    wp_register_script( 'editablegrid_utils', plugins_url( '/js/editablegrid_utils.js', __FILE__ ) );
    wp_enqueue_script( 'editablegrid_utils' );
    wp_register_script( 'editablegrid_charts', plugins_url( '/js/editablegrid_charts.js', __FILE__ ) );
    wp_enqueue_script( 'editablegrid_charts' );
    wp_register_style( 'custom-style', plugins_url( '/css/editablegrid.css', __FILE__ ), array(), '20120208', 'all' );
    wp_enqueue_style( 'editablegrid' );
}
