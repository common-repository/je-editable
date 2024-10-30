<?php
if($_POST){
	global $wpdb;
	
	if($_POST['jelocal'] == 1){
		
		foreach($_POST['jeval'] as $key => $val){
			
			foreach($val as $k => $v){
				
				if($_POST['repeat'][$k] == 'on'){
					$wpdb->query("UPDATE ".JE_TABLE." SET `repeat` = 'yes' WHERE id = {$k}");
				}
				else{
					$wpdb->query("UPDATE ".JE_TABLE." SET `repeat` = 'no' WHERE id = {$k}");
				}
				
				$wpdb->query("UPDATE ".JE_TABLE." SET jeval = '$v' WHERE id = {$k}");
			}
			
		}
		
	}
	else{
		foreach($_POST as $key => $val){
			if(get_option('je_enable_editable') == 1){
				delete_option($key);
			}
			else{
				update_option($key, $val);
			}
		}
	}
}

?>

<div class="wrap">
	<div id="icon-edit-pages" class="icon32"></div>
	<h2>JE Editable Options</h2>
	<br />
	
	<form action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">
		
	<?php if(get_option('je_enable_editable') == 1): ?>
	<input type="submit" class="button-secondary" value="Disable editable feature" /> Status: <span style="color:green">Enabled</span>
	<?php else: ?>
	<input type="submit" class="button-secondary" value="Enable editable feature" /> Status: <span style="color:red">Disabled</span>
	<?php endif; ?>	
	<input type="hidden" name="je_enable_editable" value="1" />
	</form>
	<br />
	
	<strong>Usage:</strong>
	<p>In order to make a content editable, you will need to add a classname to an elements that wraps the content you wanted to be editable.</p>
	Example: <code>&lt;p class=&quot;editable&quot;&gt;Content you wanted to be editable&lt;/p&gt;</code>
	
	<br /><br />
	
	<strong>Editable Data:</strong>
	
	<form action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">
		<table>
			<?php 
			$ctr = 0;
			foreach(editable_data() as $data): ?>
			<tr>
				<td valign="top"><a href="<?php echo $data->jeurl; ?>#<?php echo $data->jekey; ?>" target="_blank">#<?php echo $data->jekey; ?></a></td>
				<td valign="top"><textarea name="jeval[<?php echo $data->jekey; ?>][<?php echo $data->id; ?>]" id="" cols="50" rows="3"><?php echo $data->jeval; ?></textarea></td>
				<td valign="top"><input type="checkbox" name="repeat[<?php echo $data->id; ?>]" <?php echo ($data->repeat == 'yes' ? 'checked' : ''); ?> id="repeat-<?php echo $ctr; ?>" /> <label for="repeat-<?php echo $ctr; ?>">repeat</label></td>
				<input type="hidden" name="id[<?php echo $data->id; ?>]" value="<?php echo $data->id; ?>" />
			</tr>
			<?php $ctr++; endforeach; ?>
			<?php if($ctr > 0): ?>
			<tr>
				<td>&nbsp;</td>
				<td colspan="2"><input type="submit" class="button-primary" value="Save Changes" /></td>
			</tr>
			<?php else: ?>
				No data found
			<?php endif; ?>
		</table>
		<input type="hidden" name="jelocal" value="1" />
	</form>
	
</div>