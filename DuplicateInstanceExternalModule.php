<?php
// namespace defined in the config file
namespace InstitutBergonie\DuplicateInstanceExternalModule;

class DuplicateInstanceExternalModule extends \ExternalModules\AbstractExternalModule {

	function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance) {
		$instruments = $this->getProjectSetting("instruments", $project_id); // Array of instruments that allow duplication
		$index_inst = array_search($_GET['page'], $instruments);
		if ($index_inst !== false) {
			/* Duplicate button */
			?>
			<form method="post">
				<div style="max-width:800px;" class="yellow">
					<table width="100%">
						<tr>
							<td width="59%">Dupliquer cette instance</td>
							<td width="41%"><input type="submit" name="dupliquer" value="Dupliquer" /></td>
						</tr>
					</table>
				</div>
			</form>
			<?php

			if(isset($_POST['dupliquer'])) {
				/* Duplicate function */
				$fields = $this->getProjectSetting("fields", $project_id)[$index_inst]; // Array of fields to copy
				$data = \REDCap::getData($project_id, 'array', $record, $fields); // Get record data with only the fields to copy
				$new_inst = max(array_keys($data[$record]["repeat_instances"][$event_id][$instrument])) + 1; // New instance number
				$new_data[$record]["repeat_instances"][$event_id][$instrument][$new_inst] = $data[$record]["repeat_instances"][$event_id][$instrument][$repeat_instance];
				$response = \REDCap::saveData($project_id, 'array', $new_data); // Save of the new instance
				
				/* Redirection to the new instance page */
				$current_url = "http";
				$current_url .= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "s" : "";
				$current_url .= "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				$new_url = \preg_replace('/instance=\d+/', 'instance='.$new_inst, $current_url);
				header("Location: ".$new_url);
			}
		}
	}
}
