
<?php
/*
 * Index page for the CTI-application admin tool.
 * 
 * @author V. Vogelesang
 * 
 */
		
	require_once('../store/domain/User.php');
	require_once('../store/dao/DAOFacade.php');

	// Init some variables
	$DAOFacade = new DAOFacade(new Controller());
		
	@$userName = $_POST['username'];
	
	if (checkIsAdmin($userName, $DAOFacade))
	{
		session_start();
		$_SESSION['userName'] = $userName;
		$_SESSION['authorized'] = true;
		header('Location: ../admin/adminMain.php');		
	}
	else 
		echo 'You are not an authorized administrator!';		
		 
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
	
	/**
	 * Check if the user is admin
	 *
	 * @param $userName
	 * @param $DAOFacade
	 * @return boolean
	 *
	 */
	function checkIsAdmin($userName, $DAOFacade)
	{
		$user = $DAOFacade->getUserDAO()->read($userName);
		if ($user != null)
		{
			return $user->getRole() == "admin";
		}
	}
	
?>
