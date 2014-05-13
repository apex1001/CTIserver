<?php

	require_once('../store/dao/DAOFacade.php');
	require_once('../store/domain/User.php');
	require_once('../store/domain/Extension.php');
	
	// Init some variables
	$DAOFacade = new DAOFacade(new Controller());
	session_start();
	
	@$userName = $_POST['userName'];	
	@$action = $_POST['action'];
	
	// Start the main edit routine
	if ($_SESSION['authorized'])
		startUserEdit();
	else header('Location: ../www/index.php');
	
	/**
	 * The user edit routine
	 *
	 * @param $userName
	 * @param $DAOFacade
	 */
	function startUserEdit()
	{	
		global $action;
		global $userName;
		global $DAOFacade;		
		
		if (($userName == "" || $userName == null) && $_SESSION['user'] != null)	
			$userName =	$_SESSION['user']->getUsername();		
		
		// Action is edit from main page, load user details
		if ($action == "Edit" || $action == "")
		{		
			getUserDetails($userName, $DAOFacade);
		}
		
		// Delete extension, marks it deleted
		if ($action == "deleteExtension" )
		{
			deleteExtension();
		}
		
		// Add extension to the list
		if ($action == "addExtension" && $_POST['extensionNumber'] != "")
		{
			addExtension();
		}
		
		// Save all items
		if ($action == "saveSettings")
		{
			saveSettings();
			
			// Reload page
			header('Location: editUser.php');
		}		
	}	
	
	/**
	 * Add the extension to the list
	 * 
	 */
	function addExtension()
	{
		// Define extension Array
		$extensionItem = array();
		$extensionItem[0] = null;
		$extensionItem[1] = $_POST['extensionNumber'];
		$extensionItem[2] = 'f';
		$extensionItem[3] = $_SESSION['user']->getUsername();
		$extensionItem[4] = $_POST['pin'];
		$extensionItem[5] = 'new';
			
		// Add to the list
		$_SESSION['extensionList'][] = $extensionItem;
	}
	
	/**
	 * Marks an extension as deleted
	 * 
	 */
	function deleteExtension()
	{
		// Iterate list and mark as deleted.
		foreach ($_SESSION['extensionList'] as $key => $extensionItem)
		{
			if ($extensionItem[1] == $_POST['extensionNumber'])
				$_SESSION['extensionList'][$key][5] = 'deleted';
		}		
	}
	
	/**
	 * Save settings to the database
	 * 
	 */
	function saveSettings()
	{
		global $DAOFacade;
		$userName = $_SESSION['user']->getUsername();
			
		// Update role
		$user = new User();
		$user->setUsername($userName);
		$user->setRole($_POST['role']);
		$DAOFacade->getUserDAO()->update($user);
			
		// Update extensions
		$extensionDAO = $DAOFacade->getExtensionDAO();
			
		foreach ($_SESSION['extensionList'] as $extensionItem)
		{
			// Delete extension
			if ($extensionItem[5] == 'deleted' && $extensionItem[0] != null)
			{
				$extension = new Extension();
				$extension->setUsername($userName);
				$extension->setExtensionNumber($extensionItem[1]);
				$extensionDAO->delete($extension);
			}
		
			// Add new extension
			if ($extensionItem[5] == 'new')
			{
				$extension = new Extension();
				$extension->setUsername($extensionItem[3]);
				$extension->setExtensionNumber($extensionItem[1]);
				$extension->setPin($extensionItem[4]);
				$extension->setPrimaryNumber('f');
				$extension->setUserEdit('f');
				$extensionDAO->write($extension);
			}
		}
	}
	
	/**
	 * Get the userdetails from the database
	 * 
	 * @param $userName
	 * @param $DAOFacade
	 */
	function getUserDetails($userName,  $DAOFacade)
	{
		// Add details to session array
		$_SESSION['user'] = $DAOFacade->getUserDAO()->read($userName);
		$_SESSION['extensionList'] = $DAOFacade->getExtensionDAO()->getExtensionList($userName);
		$_SESSION['roleList'] = $DAOFacade->getRoleDAO()->getRoleList();
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

			<h1>Edit user</h1>
			<fieldset style="width:700px">			
			<text><br/>
			You can edit all the user settings here. Press 'save settings' to finalize the changes. Any added 
			extensions can't be removed by the user. The user can select an added extension as default extension. 
			Always make sure the pin is correct!
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
	 				foreach ($_SESSION['extensionList'] as $item)
	 				{
	 					if ($item[5] == 'deleted')
	 						continue;
	 					echo '
						<tr>
							<td>' .	$item[1] . '</td>
	 						<td>' . $item[4] . '</td>
	 						<td>
	 							<form action="editUser.php" method="post">
									<input type="submit" name="delete" value="Delete" /> 
									<input type="hidden" name="extensionNumber" value="' . $item[1] . '" />
									<input type="hidden" name="action" value="deleteExtension" />
								</form>
							</td>
	 					</tr>';
					}
					echo '
						<tr>
							<form action="editUser.php" method="post">
								<td><input type="text" name="extensionNumber" id="extension" size="10"></td>
								<td><input type="text" name="pin" id="pin" size="10"></td>
								<td>
									<input type="submit" name="addExtension" value="Add extension" />
									<input type="hidden" name="action" value="addExtension" />
								</td>
							 </form>	
					 	</tr> 
	 					<tr>
							<td>&nbsp;</td>
						</tr>					 	
					 	<tr>
					 	<td>Role</td>
					 	<td>
							<form action="editUser.php" method="post">
								<select name="role">';		 						 					
	 					
					// Fill the role dropbox
					foreach ($_SESSION['roleList'] as $item)
					{					 	
						if ($_SESSION['user']->getRole() == $item[0])
							$selected = "selected";
						else
							$selected = "";
					 	
						echo '	<option value="' . $item[0]  . '" ' . $selected . '>' . $item[0] . '</option>';
					}
	 				echo '		</select></td>
					 		 				</tr>';

 		 		?>
 		 				
				</table>
				<br/>				
					<input type="submit" name="saveSettings" value="Save settings" />	
					<input type="hidden" name="action" value="saveSettings" />		
				</form>		
				
				<form action="adminMain.php" method="post">
					<input type="submit" name="adminMain" value="Back to main menu" />		
				</form>							
			</fieldset>
			<br/>
		</body>
	</html>
