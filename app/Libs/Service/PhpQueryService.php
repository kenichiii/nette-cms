<?php

declare(strict_types=1);

namespace App\Libs\Service;

require_once( __DIR__ . '/../phpQuery/phpQuery/phpQuery.php');

class PhpQueryService
{

	public static int $count;
	public static array $results = [];
	public function __construct()
	{
	}

	public function getFirmy()
	{
		\phpQuery::browserGet('http://www.firmy.cz/?q=plzen', '\App\Libs\Service\PhpQueryService::firmy');
	//	self::getRes("00075370");
	}

	public static function test()
	{
		//print_r(self::$results);
		self::getRes(self::$results[0]['ico']);
	}

	public static function getRes($ico)
	{
		echo ' - http://apl.czso.cz/res/detail?ico='.$ico."\n";
		\phpQuery::browserGet('https://apl.czso.cz/res/detail?ico='.$ico, '\App\Libs\Service\PhpQueryService::res');
	}

	public static function res($browser)
	{
		//print $browser->find('body');
		$ico = $browser->find('.greybox')->find('div')->eq(0)->find('div')->eq(0)->next()->text();
		$employes = trim($browser->find('#map')->prev()
			->find('div')->eq(0)->next()->text());
		echo $ico . '-'. $employes;
	}

	public static function firmy($browser)
	{
		$hrefs = $browser->WebBrowser('\App\Libs\Service\PhpQueryService::detail')
			->find('.companyTitle');
	 	self::$count = count($hrefs);
	echo self::$count."\n\n";
		$hrefs->click();
	}

	public static function detail($browser)
	{
		$test = $browser->find('.detailBusinessInfo');
		$test->find('button')->remove();
		$ico = trim($test->text());
		self::$results[$ico] = [
			'title' => $browser->find('h1')->text(),
			'ico' => $ico,
		];
		print_r(self::$results[$ico]);
		echo $ico . '-detail-';
	    self::getRes($ico);	//self::getRes($ico);
	}
}
