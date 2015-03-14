<?php

/*
 * Copyright (C) 2002-2013 AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in LICENSE
 *
 */

class_exists('CApi') or die();

class COverrideDefaultDomainSettingsPlugin extends AApiPlugin
{
	/**
	 * @param CApiPluginManager $oPluginManager
	 */
	public function __construct(CApiPluginManager $oPluginManager)
	{
		parent::__construct('1.0', $oPluginManager);

		$this->AddHook('api-pre-create-account-process-call', 'PluginApiPreCreateAccountProcessCall');
	}

	/**
	 * @param CAccount $oAccountToCreate
	 */
	public function PluginApiPreCreateAccountProcessCall(&$oAccountToCreate)
	{
		if ($oAccountToCreate instanceof CAccount && $oAccountToCreate->Domain && $oAccountToCreate->Domain->IsDefaultDomain)
		{
			$sEmailDomain = api_Utils::GetDomainFromEmail($oAccountToCreate->Email);
			if (0 < strlen($sEmailDomain))
			{
				$oAccountToCreate->IncomingMailServer = 'imap.'.$sEmailDomain;
				$oAccountToCreate->OutgoingMailServer = 'smtp.'.$sEmailDomain;
			}
		}
	}
}

return new COverrideDefaultDomainSettingsPlugin($this);
