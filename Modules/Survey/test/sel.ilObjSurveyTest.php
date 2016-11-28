<?php

require_once("../../../libs/composer/vendor/autoload.php");

//class createDefaultSurveyTest extends PHPUnit_Extensions_Selenium2TestCase
class createDefaultSurveyTest extends PHPUnit_Framework_TestCase
{
	public $webdriver;

	public $url = 'http://localhost/~leifos/ILIAS_trunk/trunk/';

	public function setup()
	{
		$capabilities = [
			\WebDriverCapabilityType::BROWSER_NAME=>'chrome',
			\WebDriverCapabilityType::BROWSER_NAME=>'firefox'
		];

		$host = 'http://localhost:4444/wd/hub';

		$this->webdriver = RemoteWebDriver::create($host, $capabilities);

		$this->webdriver->get($this->url);

	}

	public function test()
	{
		$user = $this->webdriver->findElement(WebDriverBy::id('username'));
		$user->click();
		$user->sendKeys("root");

		$user = $this->webdriver->findElement(WebDriverBy::id('password'));
		$user->click();
		$user->sendKeys("homer");

		$log_button = $this->webdriver->findElement(WebDriverBy::name('cmd[doStandardAuthentication]'));
		$log_button->click();

		$this->webdriver->wait(10,1000);

		//HERE I NEED TO WAIT THE BROWSER GOES TO THE NEXT PAGE BEFORE CONTINUE THE TEST.

		//$this->assertEquals('http://localhost/~leifos/ILIAS_trunk/trunk/login.php?target=&client_id=clean&auth_stat=', $this->webdriver->getCurrentURL());


		//$this->webdriver->wait(10,1000);
		sleep(5);

		//$this->assertEquals('http://localhost/~leifos/ILIAS_trunk/trunk/goto.php?target=root_1', $this->webdriver->getCurrentURL());


		$log_button = $this->webdriver->findElement(WebDriverBy::id('mm_rep_tr'));
		$log_button->click();
		sleep(2);

		$repository = $this->webdriver->findElement(WebDriverBy::partialLinkText("Repository - Home"));
		$repository->click();

		sleep(3);

		$log_button = $this->webdriver->findElement(WebDriverBy::id('ilAdvSelListAnchorText_asl'));
		$log_button->click();

		$log_button = $this->webdriver->findElement(WebDriverBy::name('svy'));
		$log_button->click();

	}
	public function tearDown()
	{
		//$this->webdriver->quit();
	}
}