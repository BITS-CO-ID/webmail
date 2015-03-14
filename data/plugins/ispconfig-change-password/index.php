<?php

/*
 *
 * Distributed under the terms of the license described in COPYING
 * 
 */

class_exists('CApi') or die();

CApi::Inc('common.plugins.change-password');

class CCustomChangePasswordPlugin extends AApiChangePasswordPlugin
{
	/**
	 * @param CApiPluginManager $oPluginManager
	 */
	public function __construct(CApiPluginManager $oPluginManager)
	{
		parent::__construct('1.0', $oPluginManager);
	}

	private function crypt_password($cleartext_password) {
		$salt="$1$";
		$base64_alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
		for ($n=0;$n<8;$n++) {
			$salt.=$base64_alphabet[mt_rand(0,63)];
		}
		$salt.="$";
		return crypt($cleartext_password,$salt);
	}
	/**
	 * @param CAccount $oAccount
	 * @return bool
	 */
	public function validateIfAccountCanChangePassword($oAccount)
	{
		$bResult = false;
		if ($oAccount instanceof CAccount)
		{
			$bResult = true;
		}
		
		return $bResult;
	}

	/**
	 * @param CAccount $oAccount
	 * @return bool
	 */
	public function ChangePasswordProcess($oAccount)
	{
		$bResult = false;
		if (0 < strlen($oAccount->PreviousMailPassword) &&
			$oAccount->PreviousMailPassword !== $oAccount->IncomingMailPassword)
		{
			
			$aISPConfig = [
			"host" => CApi::GetConf('plugins.ispconfig-change-password.config.host', '127.0.0.1'),
			"dbuser" => CApi::GetConf('plugins.ispconfig-change-password.config.dbuser', 'root'),
			"dbpassword" => CApi::GetConf('plugins.ispconfig-change-password.config.dbpassword', ''),
			"dbname" => CApi::GetConf('plugins.ispconfig-change-password.config.dbname', 'dbispconfig'),
			];

			//connect to ispconfig database
			$mysqlcon=mysqli_connect($aISPConfig['host'],$aISPConfig['dbuser'],$aISPConfig['dbpassword'],$aISPConfig['dbname']);

		 	if($mysqlcon){
				//check old pass is correct
				$username = $oAccount->IncomingMailLogin;
				$password = $oAccount->PreviousMailPassword;
				$new_password = $oAccount->IncomingMailPassword;
		
				$sql = "SELECT * FROM mail_user WHERE login='$username'";
				$result = mysqli_query($mysqlcon,$sql);
				$mailuser = mysqli_fetch_array($result);

				//extract salt from password
				$saved_password = stripslashes($mailuser['password']);
				$salt = '$1$'.substr($saved_password,3,8).'$';

				//* Check if mailuser password is correct
				if(crypt(stripslashes($password),$salt) == $saved_password) { 
					//passwords match so set new password
					$mailuser_id = $mailuser['mailuser_id'];
				
					$new_password = $this->crypt_password($new_password);
					$sql = "UPDATE mail_user SET password='$new_password' WHERE mailuser_id='$mailuser_id'";
					$result = mysqli_query($mysqlcon,$sql);

					if (!$result){
						//password update error
						throw new CApiManagerException(Errs::UserManager_AccountNewPasswordUpdateError);
					}
				}else{
					//old and new passwords dont match
					throw new CApiManagerException(Errs::UserManager_AccountOldPasswordNotCorrect);
				}
				//disconnect from database	
				mysqli_close($mysqlcon);

			}else{
				//could not connect to database
				throw new CApiManagerException(Errs::UserManager_AccountNewPasswordUpdateError);
			}
		
		}
		
		return $bResult;
	}
}

return new CCustomChangePasswordPlugin($this);
