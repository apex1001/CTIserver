<?php

	require_once('../store/dao/DAOFacade.php');
	
	// Init some variables
	$DAOFacade = new DAOFacade(new Controller());
	session_start();
	
	@$userName = $_POST['userName'];
	$user = null;
	$extensionList = null;
	$roleList = null;
	
	// Start the main edit routine
	startUserEdit($userName, $DAOFacade);
	
	/**
	 * The user edit routine
	 *
	 * @param $userName
	 * @param $DAOFacade
	 */
	function startUserEdit($userName, $DAOFacade)
	{
		global $user;
		global $extensionList;
		global $roleList;		
		
		$user = $DAOFacade->getUserDAO()->read($userName);
		$extensionList = $DAOFacade->getExtensionDAO()->getExtensionList($userName);
		$roleList = $DAOFacade->getRoleDAO()->getRoleList();
		
		//var_dump($user);
		//print_r($extensionList);
		//print_r($roleList);
	}	
	
	
	/**
	 * Dummy controller class for DAOFacade
	 *
	 */
	class Controller
	{
		public function getSettingsArray()
		{
			$settingsArray = parse_ini_file('../conf/settings.ini');
			return $settingsArray;
		}
	}
	
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
		<head>
			<title>
				Admin user edit page CTI-application
			</title>
			<link rel="stylesheet" href="styles.css">	
		</head>
		<body>
			<script>
			
				/**
				 * Confirm deletion of a user
				 *
				 */
				function confirmDelete() 
				{
					var r=confirm("Press a button");
				}

			</script>
				
			<h1>Edit user</h1>
			<fieldset style="width:700px">			
			<text><br/>
			You can edit all the user settings here. Any added extensions can't be removed by the user. 
			The user can select an added extension as default extension. Always make sure the pin is correct!
			</text><br/><br/>
			<fieldset style="width:680px">	
				<legend><b>User details</b></legend>	
				<br/>
				<table id="userSettingsTable">
				<?php 
					
					// Show the username
					echo '
						<tr>
							<td>Username</td>
							<td>' . $userName . '</td>
					  	</tr>
						<tr>
							<td>Role</td>
							<td><select>
						';
					
					// Fill the role dropbox
					foreach ($roleList as $item)
					{
						
						if ($user->getRole() == $item[0])
							$selected = "selected";
						else 
							$selected = "";
						
						echo '	<option value="' . $item[0]  . '" ' . $selected . '>' . $item[0] . '</option>';
					}
	 				echo '		</select></td>
					  	</tr>';
	 				
	 				// Show the extension list
	 				echo '
	 					<tr>
							<td>&nbsp;</td>
						</tr>
						<tr>
	 						<td>Extension</td>
	 						<td>Pin</td>
						</tr>';	 				
	 				
	 				// Show the extension list
	 				foreach ($extensionList as $item)
	 				{
	 					echo '
						<tr>
							<td>' .	$item[1] . '</td>
	 						<td>' . $item[4] . '</td>
	 						<td>
	 							<form action="editUser.php" method="post" onsubmit="confirmDelete()">
									<input type="submit" name="delete" value="Delete" /> 
									<input type="hidden" name="extensionNumber" value="' . $item[0] . '" />
									<input type="hidden" name="action" value="deleteExtension" />
								</form>
							</td>
	 					</tr>';
	 				}	
				?>
				
						<tr>
							<form action="editUser.php" method="post">
								<td><input type="text" name="extension" id="extension" size="10"></td>
								<td><input type="text" name="pin" id="pin" size="10"></td>
								<td>
									<input type="submit" name="addExtension" value="Add extension" />
									<input type="hidden" name="action" value="addExtension" />
								</td>
							 </form>	
					 	</tr> 
				</table>
				<br/>
				<form action="saveUserSettings.php" method="post">
					<input type="submit" name="saveSettings" value="Save settings" />		
				</form>		
				<form action="adminMain.php" method="post">
					<input type="submit" name="adminMain" value="Back to main menu" />		
				</form>							
			</fieldset>
			<br/>
		</body>
	</html>
