<?php
namespace assetLink;

use bite\di\DiTarget;
use bite\error\EError;
use bite\error\IError;
use bite\execution\version\db\IDatabaseVersionHelper;
use bite\execution\version\IVersion;

class V20170330_1_createAsset extends DiTarget implements IVersion
{
	public $description = 'Create asset tables';
	
	public function execute()
	{
		$this->dbVersionHelper1->createTable('assettype', [
			'id'				=> $this->dbVersionHelper1->idField(),
			'key'				=> "VARCHAR(191) NOT NULL COMMENT 'e.g. ebookDownloadLink, ebookFile'",
			], ['id']
		);
		$this->dbVersionHelper1->createTable('assettype__language', [
			'id'			=> $this->dbVersionHelper1->foreignIdField(),
			'language_id'	=> $this->dbVersionHelper1->foreignIdField(),
			'name'			=> "VARCHAR(191) NOT NULL",
			], ['id', 'language_id'], [
				'id'			=> ['assettype.id', IDatabaseVersionHelper::onDeleteCascade],
				'language_id'	=> 'language.id',
			]
		);
		
		$this->dbVersionHelper1->createTable('asset', [
			'id'				=> $this->dbVersionHelper1->idField(),
			'creatorsystem_id'	=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'creatoruser_id'	=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'assettype_id'		=> $this->dbVersionHelper1->foreignIdField(),
			'entityclass_id'	=> $this->dbVersionHelper1->foreignIdField(),
			'entity_id'			=> $this->dbVersionHelper1->foreignIdField(),
			'registrationdate'	=> "DATETIME NOT NULL",
			'uuid'				=> "BINARY(16) NOT NULL",
			'isvalid'			=> "TINYINT(1) UNSIGNED NOT NULL DEFAULT 0",
			], ['id'], [
				'creatorsystem_id'	=> 'system.id',
				'creatoruser_id'	=> 'user.id',
				'assettype_id'		=> 'assettype.id',
				'entityclass_id'	=> 'entityclass.id',
			]
		);
		$this->dbVersionHelper1->addIndex('asset', ['entityclass_id', 'entity_id'], 'assetentity', IDatabaseVersionHelper::uniqueIndex);
		$this->dbVersionHelper1->addIndex('asset', ['uuid'], 'uuid', IDatabaseVersionHelper::uniqueIndex);
		
		$this->dbVersionHelper1->createTable('assetkeeper', [
			'id'							=> $this->dbVersionHelper1->idField(),
			'detailsperson_id'				=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull, 'assetkeeper is either a single person or a single company (entailing multiple people, could be a non official company like a club or household)'),
			'detailscompany_id'				=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'user_id'						=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull, 'when user or company has verified their email, assetkeeper will \'become\' the user or company'),
			'company_id'					=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'userorcompanyerificationdate'	=> "DATETIME NULL DEFAULT NULL",
			'email'							=> "VARCHAR(191) NULL COMMENT 'only null when assetkeeper is linked to user or company'",
			'emailverificationdate'			=> "DATETIME NULL DEFAULT NULL",
			'oldemail'						=> "VARCHAR(191) NOT NULL DEFAULT '' COMMENT 'email switches to oldemail when user or company verifies emailaddress (use null instead of empty string to keep unique constraint)'",
			], ['id'], [
				'detailsperson_id'	=> 'person.id',
				'detailscompany_id'	=> 'company.id',
				'user_id'			=> 'user.id',
				'company_id'		=> 'company.id',
			]
		);
		$this->dbVersionHelper1->addIndex('assetkeeper', ['email'], 'email', IDatabaseVersionHelper::uniqueIndex);
		
		$this->dbVersionHelper1->createTable('assetownership', [
			'id'								=> $this->dbVersionHelper1->idField(),
			'creatorsystem_id'					=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'creatoruser_id'					=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull, 'user that initiated the transfer'),
			'asset_id'							=> $this->dbVersionHelper1->foreignIdField(),
			'assetkeeper_id'					=> $this->dbVersionHelper1->foreignIdField(),
			'previousassetownership_id'			=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'd_nextassetownership_id'			=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'd_currentlylenttoassetborrow_id'	=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'registrationdate'					=> "DATETIME NOT NULL",
			'acquiredate'						=> "DATETIME NULL DEFAULT NULL COMMENT 'acquiredate can be unknown for the original owner'",
			'acceptdate'						=> "DATETIME NULL DEFAULT NULL",
			'denydate'							=> "DATETIME NULL DEFAULT NULL",
			'd_transferdate'					=> "DATETIME NULL DEFAULT NULL",
			], ['id'], [
				'creatorsystem_id'					=> 'system.id',
				'creatoruser_id'					=> 'user.id',
				'asset_id'							=> 'asset.id',
				'assetkeeper_id'					=> 'assetkeeper.id',
				'previousassetownership_id'			=> 'assetownership.id',
				'd_nextassetownership_id'			=> 'assetownership.id',
				//'d_currentlylenttoassetborrow_id'	=> 'assetborrow.id', 	@note delay creating this circular foreign key
			]
		);
		
		$this->dbVersionHelper1->createTable('assetborrow', [
			'id'								=> $this->dbVersionHelper1->idField(),
			'creatorsystem_id'					=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'creatoruser_id'					=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull, 'user that initiated the transfer'),
			'd_asset_id'						=> $this->dbVersionHelper1->foreignIdField(),
			'd_currentassetownership_id'		=> $this->dbVersionHelper1->foreignIdField(),
			'assetkeeper_id'					=> $this->dbVersionHelper1->foreignIdField(),
			'borrowedfromassetborrow_id'		=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'd_currentlylenttoassetborrow_id'	=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'registrationdate'					=> "DATETIME NOT NULL",
			'borrowdate'						=> "DATETIME NOT NULL",
			'returndate'						=> "DATETIME NULL DEFAULT NULL",
			'finalreturndate'					=> "DATETIME NULL DEFAULT NULL",
			'canlend'							=> "TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'specifies if borrower can lend the asset to others'",
			'cangivelendpermission'				=> "TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'lender can restrict the lending depth (i.e. borrower can lend to someone, but can\'t give that someone permission to lend themselves)'",
			], ['id'], [
				'creatorsystem_id'					=> 'system.id',
				'creatoruser_id'					=> 'user.id',
				'd_asset_id'						=> 'asset.id',
				'd_currentassetownership_id'		=> 'assetownership.id',
				'assetkeeper_id'					=> 'assetkeeper.id',
				'borrowedfromassetborrow_id'		=> 'assetborrow.id',
				'd_currentlylenttoassetborrow_id'	=> 'assetborrow.id',
			]
		);
		
		// delayed creation of circular foreign key
		$this->dbVersionHelper1->addForeignKey('assetownership', 'd_currentlylenttoassetborrow_id', 'assetborrow.id');
		
		$this->dbVersionHelper1->createTable('assetborrow_assetownership', [
			'assetborrow_id'	=> $this->dbVersionHelper1->foreignIdField(),
			'assetownership_id'	=> $this->dbVersionHelper1->foreignIdField(),
			'startdate'			=> "DATETIME NOT NULL",
			'enddate'			=> "DATETIME NULL DEFAULT NULL",
			], ['assetborrow_id', 'assetownership_id'], [
				'assetborrow_id'	=> ['assetborrow.id', IDatabaseVersionHelper::onDeleteCascade],
				'assetownership_id'	=> 'assetownership.id',
			]
		);
		
		$this->dbVersionHelper1->createTable('d_currentassetpossession', [
			'id'				=> $this->dbVersionHelper1->idField(), // only because PK cannot be null
			'assetownership_id'	=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'assetborrow_id'	=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'assetkeeper_id'	=> $this->dbVersionHelper1->foreignIdField(),
			'asset_id'			=> $this->dbVersionHelper1->foreignIdField(),
			'islent'			=> "TINYINT(1) UNSIGNED NOT NULL DEFAULT 0",
			], ['id'], [
				'assetborrow_id'	=> 'assetborrow.id',
				'assetownership_id'	=> 'assetownership.id',
				'assetkeeper_id'	=> 'assetkeeper.id',
				'asset_id'			=> 'asset.id',
			]
		);
		$this->dbVersionHelper1->addIndex('d_currentassetpossession', ['assetownership_id', 'assetborrow_id'], 'ownershipborrow', IDatabaseVersionHelper::uniqueIndex);
	}
	
	public function revert()
	{
		$this->dbVersionHelper1->dropTable('d_currentassetpossession');
		$this->dbVersionHelper1->dropTable('assetborrow_assetownership');
		
		// drop circular foreign key before dropping referenced table
		$this->dbVersionHelper1->dropForeignKey('assetownership', 'd_currentlylenttoassetborrow_id');
		
		$this->dbVersionHelper1->dropTable('assetborrow');
		$this->dbVersionHelper1->dropTable('assetownership');
		$this->dbVersionHelper1->dropTable('assetkeeper');
		$this->dbVersionHelper1->dropTable('asset');
		$this->dbVersionHelper1->dropTable('assettype__language');
		$this->dbVersionHelper1->dropTable('assettype');
	}
}