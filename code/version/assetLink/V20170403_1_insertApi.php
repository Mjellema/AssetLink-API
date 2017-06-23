<?php
namespace assetLink;

use bite\di\DiTarget;
use bite\error\EError;
use bite\error\IError;
use bite\execution\version\db\IDatabaseVersionHelper;
use bite\execution\version\IVersion;

class V20170403_1_insertApi extends DiTarget implements IVersion
{
	public $description = 'Insert API';
	
	public function execute()
	{
		$englishId	= $this->dbVersioning->select(['id', 'FROM', 'language', 'WHERE', ['key' => 'english']])->fetchField('id');
		$dutchId	= $this->dbVersioning->select(['id', 'FROM', 'language', 'WHERE', ['key' => 'dutch']])->fetchField('id');
		
		$apiId = $this->dbVersioning->insert('api', 'id', [
			'key' => 'assetLink',
		]);
		$this->dbVersioning->insert('api__language', null, [
			'id'			=> $apiId,
			'language_id'	=> $englishId,
			'name'			=> 'AssetLink',
		]);
		$this->dbVersioning->insert('api__language', null, [
			'id'			=> $apiId,
			'language_id'	=> $dutchId,
			'name'			=> 'AssetLink',
		]);
	}
	
	public function revert()
	{
		$this->dbVersioning->delete('api', ['key' => 'assetLink']);
	}
}