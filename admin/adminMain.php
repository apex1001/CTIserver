<?php
	
	require_once('../store/dao/DAOFacade.php');

	// Init some variables
	$DAOFacade = new DAOFacade(new Controller());
	session_start();	
		
	@$userName = $_SESSION['userName'];	
	@$userNameSearch = $_POST['userNameSearch'];
	@$action = $_POST['action'];	
	$searchResult = null;
	
	// Start the main admin routine
	startAdmin($userName, $userNameSearch, $DAOFacade, $action);
	
	/**
	 * The admin page routine
	 *
	 * @param $userName
	 * @param $DAOFacade
	 */
	function startAdmin($userName, $userNameSearch, $DAOFacade, $action)
	{		
		// Check for a given user to search, if present get results
		if ($userNameSearch != "" && $action = "searchUser")
		{
			global $searchResult;			
			$searchResult = getUserList($userNameSearch, $DAOFacade);				
		}
	}
	
	/**
	 * Get the list of users
	 * 
	 * @param $userNameSearch
	 * @param $DAOFacade
	 * @return array with user records
	 * 
	 */	
	function getUserList($userNameSearch, $DAOFacade)
	{
		$result = $DAOFacade->getUserDAO()->getUserList($userNameSearch);
		return $result;
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
				Admin page CTI-application
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
				
			<h1>Admin page CTI-application</h1>
			<fieldset style="width:700px">			
			<text><br/>
				Welcome to the admin page of the CTI application! On this page you can search for users,
				edit or delete them and do some database cleanup. 
			</text><br/><br/>
			<fieldset style="width:680px">	
				<legend><b>Search user</b></legend>	
				<text><br/>Please enter a username to search for. You can use SQL wildcards like %,?.</text>
				<br/><br/>
				<form action="adminMain.php" method="post">
					Username: 
					<input type="text" name="userNameSearch" size = 10 />
					<input type="hidden" name="action" value="searchUser" />
					<input type="submit" value="Search" />
				</form>	
				<br/><br/>
				<text>Current selection:</text>
				<hr/>
				
				<?php 		
					
					// Display the searchResult 
					if ($searchResult != "" && count($searchResult) > 0)
					{
						echo '<table>
								<tr>
								   <th>Username&nbsp;&nbsp;</th>
								   <th>Role&nbsp;&nbsp;</th>
									<th></th>
									<th></th>
									<th></th>
								 </tr>';								
						foreach ($searchResult as $user)
						{
							echo '<tr>
									<td>' . $user[0] . '&nbsp;&nbsp;</td>
									<td>' . $user[1] . '&nbsp;&nbsp;</td>
									<td>
	 									<form action="editUser.php" method="post">
		 									<input type="submit" name="edit" value="Edit" />
		 									<input type="hidden" name="userName" value="' . $user[0] . '" />
										</form>
	 								</td>
									<td>
										<form onsubmit="confirmDelete()">
											<input type="submit" name="delete" value="Delete" /> 
											<input type="hidden" name="userName" value="' . $user[0] . '" />
										</form>
	 								</td>
								 </tr>';							
						}
						echo '</table>';
					}
					else 
						echo 'No search results ..';
				?>
				
			</fieldset>
			<br/>
			<fieldset style="width:680px">	
				<legend><b>Database maintenance</b></legend>
				<text><br/>
					To clean the database just press the clean button. This removes all call history records up to 
					one month from the present. If you need to do this often maybe you should schedule an SQL cleanup 
					job on the history table.
				</text> 
				<br/>
				<br/>
				<form action="cleanDb.php" method="post" >					
					<input type="submit" value="Clean database" />
				</form>	
			</fieldset>		
		</body>
	</html>



	
	

