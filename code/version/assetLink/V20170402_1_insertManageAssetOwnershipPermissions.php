<?php
namespace assetLink;

use bite\di\DiTarget;
use bite\error\EError;
use bite\error\IError;
use bite\execution\version\db\IDatabaseVersionHelper;
use bite\execution\version\IVersion;

class V20170402_1_insertManageAssetOwnershipPermissions extends DiTarget implements IVersion
{
	public $description = 'Insert manageAssetOwnership permissions';
	
	public function execute()
	{
		$englishId	= $this->dbVersioning->select(['id', 'FROM', 'language', 'WHERE', ['key' => 'english']])->fetchField('id');
		$dutchId	= $this->dbVersioning->select(['id', 'FROM', 'language', 'WHERE', ['key' => 'dutch']])->fetchField('id');
		
		$permissionBundleId = $this->dbVersioning->insert('permissionbundle', 'id', [
			'key' => 'manageAssetOwnership',
		]);
		$this->dbVersioning->insert('permissionbundle__language', null, [
			'id'			=> $permissionBundleId,
			'language_id'	=> $englishId,
			'name'			=> 'Manage asset ownership',
		]);
		$this->dbVersioning->insert('permissionbundle__language', null, [
			'id'			=> $permissionBundleId,
			'language_id'	=> $dutchId,
			'name'			=> 'Beheer asset eigendom',
		]);
		
		// providePreviousOwner
		$permissionTaskId = $this->dbVersioning->insert('permissiontask', 'id', [
			'key' => 'providePreviousOwner',
		]);
		$this->dbVersioning->insert('permissiontask__language', null, [
			'id'			=> $permissionTaskId,
			'language_id'	=> $englishId,
			'name'			=> 'Provide previous owner',
		]);
		$this->dbVersioning->insert('permissiontask__language', null, [
			'id'			=> $permissionTaskId,
			'language_id'	=> $dutchId,
			'name'			=> 'Verstrek vorige eigenaar',
		]);
		$this->dbVersioning->insert('permissionbundle_permissiontask', null, [
			'permissionbundle_id' => $permissionBundleId,
			'permissiontask_id' => $permissionTaskId,
		]);
		
		// transfer
		$permissionTaskId = $this->dbVersioning->insert('permissiontask', 'id', [
			'key' => 'transfer',
		]);
		$this->dbVersioning->insert('permissiontask__language', null, [
			'id'			=> $permissionTaskId,
			'language_id'	=> $englishId,
			'name'			=> 'Transfer',
		]);
		$this->dbVersioning->insert('permissiontask__language', null, [
			'id'			=> $permissionTaskId,
			'language_id'	=> $dutchId,
			'name'			=> 'Overdragen',
		]);
		$this->dbVersioning->insert('permissionbundle_permissiontask', null, [
			'permissionbundle_id' => $permissionBundleId,
			'permissiontask_id' => $permissionTaskId,
		]);
		
		// obtainEbookDownloadLinkStatus
		$permissionTaskId = $this->dbVersioning->insert('permissiontask', 'id', [
			'key' => 'obtainEbookDownloadLinkStatus',
		]);
		$this->dbVersioning->insert('permissiontask__language', null, [
			'id'			=> $permissionTaskId,
			'language_id'	=> $englishId,
			'name'			=> 'Obtain ebook download link status',
		]);
		$this->dbVersioning->insert('permissiontask__language', null, [
			'id'			=> $permissionTaskId,
			'language_id'	=> $dutchId,
			'name'			=> 'Verkrijg ebook downloadlink status',
		]);
		$this->dbVersioning->insert('permissionbundle_permissiontask', null, [
			'permissionbundle_id' => $permissionBundleId,
			'permissiontask_id' => $permissionTaskId,
		]);
		
		// obtainEpub
		$permissionTaskId = $this->dbVersioning->insert('permissiontask', 'id', [
			'key' => 'obtainEpub',
		]);
		$this->dbVersioning->insert('permissiontask__language', null, [
			'id'			=> $permissionTaskId,
			'language_id'	=> $englishId,
			'name'			=> 'Obtain epub',
		]);
		$this->dbVersioning->insert('permissiontask__language', null, [
			'id'			=> $permissionTaskId,
			'language_id'	=> $dutchId,
			'name'			=> 'Verkrijg epub',
		]);
		$this->dbVersioning->insert('permissionbundle_permissiontask', null, [
			'permissionbundle_id' => $permissionBundleId,
			'permissiontask_id' => $permissionTaskId,
		]);
		
		// generateManagePermissionPasstoken
		$permissionTaskId = $this->dbVersioning->insert('permissiontask', 'id', [
			'key' => 'generateManagePermissionPasstoken',
		]);
		$this->dbVersioning->insert('permissiontask__language', null, [
			'id'			=> $permissionTaskId,
			'language_id'	=> $englishId,
			'name'			=> 'Generate \'manage\' permission passtoken',
		]);
		$this->dbVersioning->insert('permissiontask__language', null, [
			'id'			=> $permissionTaskId,
			'language_id'	=> $dutchId,
			'name'			=> 'Genereer \'beheer\' permissie passtoken',
		]);
		$this->dbVersioning->insert('permissionbundle_permissiontask', null, [
			'permissionbundle_id' => $permissionBundleId,
			'permissiontask_id' => $permissionTaskId,
		]);
	}
	
	public function revert()
	{
		$permissionBundleId = $this->dbVersioning->select(['id', 'FROM', 'permissionbundle', 'WHERE', ['key' => 'manageAssetOwnership']])->fetchField('id');
		
		$keys = [
			'generateManagePermissionPasstoken',
			'obtainEpub',
			'obtainEbookDownloadLinkStatus',
			'transfer',
			'providePreviousOwner',
		];
		$result = $this->dbVersioning->select(['id', 'FROM', 'permissiontask', 'WHERE', ['key' => $this->dbVersioning->expression('IN (' . $this->db->placeHolders($keys) . ')', $keys)]]);
		$permissionTaskIds = array();
		foreach ($result as $row)
		{
			$permissionTaskIds[] = $row['id'];
		}
		
		$this->dbVersioning->delete('permissionbundle_permissiontask', [
			'permissiontask_id' => $this->dbVersioning->expression('IN (' . $this->db->placeHolders($permissionTaskIds) . ')', $permissionTaskIds),
			'OR',
			'permissionbundle_id' => $permissionBundleId,
		]);
		
		$this->dbVersioning->delete('role_permissiontask_entity', [
			'permissiontask_id' => $this->dbVersioning->expression('IN (' . $this->db->placeHolders($permissionTaskIds) . ')', $permissionTaskIds),
		]);
		
		$this->dbVersioning->delete('role_permissiontask', [
			'permissiontask_id' => $this->dbVersioning->expression('IN (' . $this->db->placeHolders($permissionTaskIds) . ')', $permissionTaskIds),
		]);
		
		$this->dbVersioning->delete('role_permissionbundle_entity', [
			'permissionbundle_id' => $permissionBundleId,
		]);
		
		$this->dbVersioning->delete('role_permissionbundle', [
			'permissionbundle_id' => $permissionBundleId,
		]);
		
		$this->dbVersioning->delete('permissiontask', ['key' => $this->dbVersioning->expression('IN (' . $this->db->placeHolders($keys) . ')', $keys)]);
		$this->dbVersioning->delete('permissionbundle', ['key' => 'manageAssetOwnership']);
	}
}