<?php

/*
 * Copyright (C) 2002-2013 AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE
 *
 */

class_exists('CApi') or die();

class CSimpleDemoPlugin extends AApiPlugin
{
	const DEMO_LOGIN = 'demo@bits.co.id';
	const DEMO_PASSWORD = 'demo';
	
	/**
	 * @param CApiPluginManager $oPluginManager
	 */
	public function __construct(CApiPluginManager $oPluginManager)
	{
		parent::__construct('1.0', $oPluginManager);

		$this->AddHook('api-app-user-data', 'PluginApiAppUserData');
		$this->AddHook('api-app-domain-data', 'PluginApiAppDomainData');
		$this->AddHook('api-integrator-login-to-account', 'PluginIntegratorLoginToAccount');
		$this->AddHook('plugin-is-demo-account', 'PluginIsDemoAccount');
		$this->AddHook('webmail.validate-message-for-send', 'ValidateMessageForSend');
	}

	public function Init()
	{
		parent::Init();

		CApi::SetConf('demo.webmail.enable', true);
		CApi::SetConf('demo.webmail.login', self::DEMO_LOGIN);
		CApi::SetConf('demo.webmail.password', API_DUMMY);

//		CApi::SetConf('labs.google-analytic.account', 'UA-555555-1');
	}

	/**
	 * @param string $sEmail
	 * @return bool
	 */
	protected function isDemoAccount($sEmail)
	{
		return self::DEMO_LOGIN === $sEmail;
	}

	/**
	 * @param CAccount $oAccount
	 * @param bool $bResult
	 * @return void
	 */
	public function ValidateMessageForSend(&$oAccount, &$oMessage)
	{
		if ($oAccount && $oMessage && $this->isDemoAccount($oAccount->Email))
		{
			$oRcpt = $oMessage->GetRcpt();
			if ($oRcpt && 0 < $oRcpt->Count())
			{
				$bExternal = false;
				$sDemoDomain = strtolower(\MailSo\Base\Utils::GetDomainFromEmail(self::DEMO_LOGIN));
				
				$oRcpt->ForeachList(function (/* @var $oItem \MailSo\Mime\Email */ $oItem) use (&$bExternal, $sDemoDomain) {
					if (!$bExternal && $oItem && $sDemoDomain !== strtolower($oItem->GetDomain()))
					{
						$bExternal = true;
					}
				});

				if ($bExternal)
				{
					throw new \ProjectSeven\Exceptions\ClientException(\ProjectSeven\Notifications::DemoAccount);
				}
			}
		}
	}
	
	/**
	 * @param CAccount $oAccount
	 * @param bool $bResult
	 * @return void
	 */
	public function PluginIsDemoAccount(&$oAccount, &$bResult)
	{
		$bResult = $this->isDemoAccount($oAccount->Email);
	}

	/**
	 * @param CAccount $oAccount
	 * @param array $aResult
	 */
	public function PluginApiAppUserData($oAccount, &$aResult)
	{
		if ($oAccount && $this->isDemoAccount($oAccount->Email))
		{
			$aResult['IsDemo'] = true;
		}
	}

	/**
	 * @param CDomain $oDomain
	 * @param array $aResult
	 */
	public function PluginApiAppDomainData($oDomain, &$aResult)
	{
		if ($oDomain && $aResult)
		{
			$aResult['LoginDescription'] = 'This is WebMail live demo.';
		}
	}

	/**
	 * @param string $sEmail
	 * @param string $sPassword
	 * @param string $sLogin
	 * @param string $sLanguage
	 */
	public function PluginIntegratorLoginToAccount(&$sEmail, &$sPassword, &$sLogin, &$sLanguage)
	{
		if (self::DEMO_LOGIN === $sEmail)
		{
			$sPassword = self::DEMO_PASSWORD;
		}
	}
}

return new CSimpleDemoPlugin($this);
