<?php
namespace assetLink;

use bite\di\DiTarget;
use bite\error\EError;
use bite\error\IError;
use bite\execution\version\db\IDatabaseVersionHelper;
use bite\execution\version\IVersion;

class V20170331_1_createEbookAsset extends DiTarget implements IVersion
{
	public $description = 'Create ebook asset tables';
	
	public function execute()
	{
		$this->dbVersionHelper1->createTable('ebookdownloadlinktype', [
			'id'				=> $this->dbVersionHelper1->idField(),
			'key'				=> "VARCHAR(191) NOT NULL COMMENT 'e.g. eBoekhuis, leesId, kobo'",
			], ['id']
		);
		$this->dbVersionHelper1->createTable('ebookdownloadlinktype__language', [
			'id'			=> $this->dbVersionHelper1->foreignIdField(),
			'language_id'	=> $this->dbVersionHelper1->foreignIdField(),
			'name'			=> "VARCHAR(191) NOT NULL",
			], ['id', 'language_id'], [
				'id'			=> ['ebookdownloadlinktype.id', IDatabaseVersionHelper::onDeleteCascade],
				'language_id'	=> 'language.id',
			]
		);
		
		$this->dbVersionHelper1->createTable('ebookdownloadlinkinvalidreason', [
			'id'				=> $this->dbVersionHelper1->idField(),
			'key'				=> "VARCHAR(191) NOT NULL COMMENT 'e.g. brokenZip, drm, duplicateUrl'",
			], ['id']
		);
		$this->dbVersionHelper1->createTable('ebookdownloadlinkinvalidreason__language', [
			'id'			=> $this->dbVersionHelper1->foreignIdField(),
			'language_id'	=> $this->dbVersionHelper1->foreignIdField(),
			'name'			=> "VARCHAR(191) NOT NULL",
			], ['id', 'language_id'], [
				'id'			=> ['ebookdownloadlinkinvalidreason.id', IDatabaseVersionHelper::onDeleteCascade],
				'language_id'	=> 'language.id',
			]
		);
		
		$this->dbVersionHelper1->createTable('ebook', [
			'id'						=> $this->dbVersionHelper1->idField(),
			'isbn'						=> "CHAR(13) NOT NULL DEFAULT '' COMMENT 'can be empty'",
			'author'					=> "VARCHAR(191) NOT NULL DEFAULT ''",
			'title'						=> "VARCHAR(191) NOT NULL DEFAULT ''",
			'ismetadata'				=> "TINYINT(1) UNSIGNED NOT NULL DEFAULT 1",
			'originalstoredfilename'	=> "VARCHAR(191) NOT NULL DEFAULT ''",
			'drmstoredfilename'			=> "VARCHAR(191) NOT NULL DEFAULT ''",
			'isdamaged'					=> "TINYINT(1) UNSIGNED NOT NULL DEFAULT 0",
			], ['id']
		);
		
		
		$this->dbVersionHelper1->createTable('ebookdownloadlink', [
			'id'											=> $this->dbVersionHelper1->idField(),
			'ebookdownloadlinktype_id'						=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'd_latestebookdownloadlinkvalidation_id'		=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'd_latestvalidebookdownloadlinkvalidation_id'	=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'registrationdate'								=> "DATETIME NOT NULL",
			'ischecked'										=> "TINYINT(1) UNSIGNED NOT NULL DEFAULT 0",
			'checkcount'									=> "TINYINT(3) NOT NULL DEFAULT 0",
			'url'											=> "VARCHAR(1024) NOT NULL",
			'd_eventualurl'									=> "VARCHAR(1024) NOT NULL DEFAULT ''",
			'd_uniqueurlparts'								=> "VARCHAR(1024) NOT NULL DEFAULT ''",
			'd_hasbeenvalid'								=> "TINYINT(1) UNSIGNED NOT NULL DEFAULT 0",
			'isbn'											=> "CHAR(13) NOT NULL DEFAULT '' COMMENT 'can be empty'",
			'isbnisprovidedmanually'						=> "TINYINT(1) UNSIGNED NOT NULL DEFAULT 0",
			'metaauthor'									=> "VARCHAR(191) NOT NULL DEFAULT ''",
			'metatitle'										=> "VARCHAR(191) NOT NULL DEFAULT ''",
			'metacoverstoredfilename'						=> "VARCHAR(191) NOT NULL DEFAULT ''",
			], ['id'], [
				'ebookdownloadlinktype_id'						=> 'ebookdownloadlinktype.id',
				//'d_latestebookdownloadlinkvalidation_id'		=> 'ebookdownloadlinkvalidation.id',	@note delay creating this circular foreign key
				//'d_latestvalidebookdownloadlinkvalidation_id'	=> 'ebookdownloadlinkvalidation.id',	@note delay creating this circular foreign key
			]
		);
		
		$this->dbVersionHelper1->createTable('ebookdownloadlinkvalidation', [
			'id'								=> $this->dbVersionHelper1->idField(),
			'ebookdownloadlink_id'				=> $this->dbVersionHelper1->foreignIdField(),
			'ebookdownloadlinkinvalidreason_id'	=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'ebook_id'							=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'validationdate'					=> "DATETIME NOT NULL",
			'isvalid'							=> "TINYINT(1) UNSIGNED NOT NULL",
			'eventualurl'						=> "VARCHAR(1024) NOT NULL",
			'uniqueurlparts'					=> "VARCHAR(1024) NOT NULL DEFAULT ''",
			'filehash'							=> "VARBINARY(64) NOT NULL DEFAULT ''",
			'unzippedfilehash'					=> "VARBINARY(64) NOT NULL DEFAULT ''",
			'contenthash'						=> "VARBINARY(64) NOT NULL DEFAULT ''",
			'drmtoken'							=> "TEXT NOT NULL",
			], ['id'], [
				'ebookdownloadlink_id'				=> 'ebookdownloadlink.id',
				'ebookdownloadlinkinvalidreason_id'	=> 'ebookdownloadlinkinvalidreason.id',
				'ebook_id'							=> 'ebook.id',
			]
		);
		
		// delayed creation of circular foreign key
		$this->dbVersionHelper1->addForeignKey('ebookdownloadlink', 'd_latestebookdownloadlinkvalidation_id', 'ebookdownloadlinkvalidation.id');
		$this->dbVersionHelper1->addForeignKey('ebookdownloadlink', 'd_latestvalidebookdownloadlinkvalidation_id', 'ebookdownloadlinkvalidation.id');
		
		$this->dbVersionHelper1->createTable('ebookdownloadlinkredirect', [
			'id'								=> $this->dbVersionHelper1->idField(),
			'ebookdownloadlinkvalidation_id'	=> $this->dbVersionHelper1->foreignIdField(),
			'position'							=> "TINYINT(3) UNSIGNED NOT NULL",
			'url'								=> "VARCHAR(1024) NOT NULL",
			], ['id'], [
				'ebookdownloadlinkvalidation_id' => 'ebookdownloadlinkvalidation.id',
			]
		);
		
		$this->dbVersionHelper1->createTable('ebookdownloadlinkmetaisbn', [
			'id'					=> $this->dbVersionHelper1->idField(),
			'ebookdownloadlink_id'	=> $this->dbVersionHelper1->foreignIdField(),
			'metaisbn'				=> "CHAR(13) NOT NULL",
			], ['id'], [
				'ebookdownloadlink_id' => 'ebookdownloadlink.id',
			]
		);
		$this->dbVersionHelper1->addIndex('ebookdownloadlinkmetaisbn', ['ebookdownloadlink_id', 'metaisbn'], 'ebookdownloadlinkisbn', IDatabaseVersionHelper::uniqueIndex);
		
		
		$this->dbVersionHelper1->createTable('d_ebookassetpossession', [
			'id'					=> $this->dbVersionHelper1->idField(), // only because PK cannot be null
			'assetownership_id'		=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'assetborrow_id'		=> $this->dbVersionHelper1->foreignIdField(IDatabaseVersionHelper::allowNull),
			'assetkeeper_id'		=> $this->dbVersionHelper1->foreignIdField(),
			'asset_id'				=> $this->dbVersionHelper1->foreignIdField(),
			'ebook_id'				=> $this->dbVersionHelper1->foreignIdField(),
			'storedfilename'		=> "VARCHAR(191) NOT NULL DEFAULT '' COMMENT 'transfered/lent asset is not 100% identical to the original, i.e. it has a watermark. empty means not processed'",
			'islent'				=> "TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'record is kept until assetkeeper no longer possesses the asset, but if that\'s currently lent, it may not access the asset'",
			], ['id'], [
				'assetownership_id'		=> 'assetownership.id',
				'assetborrow_id'		=> 'assetborrow.id',
				'assetkeeper_id'		=> 'assetkeeper.id',
				'asset_id'				=> 'asset.id',
				'ebook_id'				=> 'ebook.id',
			]
		);
		$this->dbVersionHelper1->addIndex('d_ebookassetpossession', ['assetownership_id', 'assetborrow_id'], 'ownershipborrow', IDatabaseVersionHelper::uniqueIndex);
	}
	
	public function revert()
	{
		$this->dbVersionHelper1->dropTable('d_ebookassetpossession');
		$this->dbVersionHelper1->dropTable('ebookdownloadlinkmetaisbn');
		$this->dbVersionHelper1->dropTable('ebookdownloadlinkredirect');
		
		// drop circular foreign key before dropping referenced table
		$this->dbVersionHelper1->dropForeignKey('ebookdownloadlink', 'd_latestebookdownloadlinkvalidation_id');
		$this->dbVersionHelper1->dropForeignKey('ebookdownloadlink', 'd_latestvalidebookdownloadlinkvalidation_id');
		
		$this->dbVersionHelper1->dropTable('ebookdownloadlinkvalidation');
		$this->dbVersionHelper1->dropTable('ebookdownloadlink');
		$this->dbVersionHelper1->dropTable('ebook');
		$this->dbVersionHelper1->dropTable('ebookdownloadlinkinvalidreason__language');
		$this->dbVersionHelper1->dropTable('ebookdownloadlinkinvalidreason');
		$this->dbVersionHelper1->dropTable('ebookdownloadlinktype__language');
		$this->dbVersionHelper1->dropTable('ebookdownloadlinktype');
	}
}