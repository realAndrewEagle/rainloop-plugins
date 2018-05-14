<?php

class UberspaceChangePasswordDriver implements \RainLoop\Providers\ChangePassword\ChangePasswordInterface
{
	/**
	 * @param \RainLoop\Account $account
	 *
	 * @return bool
	 */
	public function PasswordChangePossibility($account)
	{
		return $account && $account->Email() &&
			!is_null($this->GetAccountName($account->Email()));
	}

	/**
	 * @param \RainLoop\Account $account
	 * @param string $oldPassword
	 * @param string $newPassword
	 *
	 * @return bool
	 */
	public function ChangePassword(\RainLoop\Account $account, $oldPassword, $newPassword)
	{
		$accountName = $this->GetAccountName($account->Email());

		$process = proc_open("vpasswd '$accountName'", array(0 => array("pipe", "r")), $pipes);

		if (!is_resource($process))
			return FALSE;

		fwrite($pipes[0], "$newPassword\n");
		fwrite($pipes[0], "$newPassword\n");
		fclose($pipes[0]);

		return proc_close($process) == 0;
	}

	private function GetAccountName($accountEmail)
	{
		$userName = \MailSo\Base\Utils::GetAccountNameFromEmail($accountEmail);
		$domainName = \MailSo\Base\Utils::GetDomainFromEmail($accountEmail);

		if ($this->IsDirectUberspaceDomain($domainName))
		{
			if ($this->DomainIncludesUberspaceAccountName($domainName))
				return $userName;
			else
				return substr($userName, strpos($userName, "-") + 1);
		}

		$domains = $this->GetDomains();

		if (!array_key_exists($domainName, $domains)) 
			return NULL;

		$namespacePrefix = (is_null($domains[$domainName]) ? "" : $domains[$domainName] . "-");

		return $namespacePrefix . $userName;
	}

	private function IsDirectUberspaceDomain($domainName)
	{
		return $this->EndsWith(strtolower($domainName), ".uberspace.de") &&
			$this->IsInRange(count(explode(".", $domainName)), 3, 4);
	}

	private function DomainIncludesUberspaceAccountName($domainName)
	{
		return count(explode(".", $domainName)) == 4;
	}

	private function GetDomains()
	{
		$domains = [];

		exec("uberspace mail domain list", $domainEntries);

		foreach ($domainEntries as $domainEntry)
		{
			$domainParts = explode(" ", $domainEntry);
			$domainName = $domainParts[0];
			$namespace = (count($domainParts) > 1 ? $domainParts[1] : NULL);
			$domains[$domainName] = $namespace;
		}

		return $domains;
	}



	private function EndsWith($input, $value)
	{
		$length = strlen($value);

		if ($length == 0)
			return true;

		return (substr($input, -$length) === $value);
	}

	private function IsInRange($value, $minValue, $maxValue)
	{
		return $value >= $minValue && $value <= $maxValue;
	}
}
