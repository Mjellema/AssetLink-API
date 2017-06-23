<?php
namespace assetLink;

use bite\di\DiTarget;
use bite\error\EError;
use bite\error\IError;
use bite\execution\version\db\IDatabaseVersionHelper;
use bite\execution\version\IVersion;

class V20170401_1_insertAssetType extends DiTarget implements IVersion
{
	public $description = 'Insert asset types';
	
	public function execute()
	{
		$englishId	= $this->dbVersioning->select(['id', 'FROM', 'language', 'WHERE', ['key' => 'english']])->fetchField('id');
		$dutchId	= $this->dbVersioning->select(['id', 'FROM', 'language', 'WHERE', ['key' => 'dutch']])->fetchField('id');
		
		// assetOwnership
		$entityClassId = $this->dbVersioning->insert('entityclass', 'id', [
			'key' => 'assetOwnership',
		]);
		$this->dbVersioning->insert('entityclass__language', null, [
			'id'			=> $entityClassId,
			'language_id'	=> $englishId,
			'name'			=> 'Asset ownership',
		]);
		$this->dbVersioning->insert('entityclass__language', null, [
			'id'			=> $entityClassId,
			'language_id'	=> $dutchId,
			'name'			=> 'Asset eigendom',
		]);
		
		// ebookDownloadLink
		$entityClassId = $this->dbVersioning->insert('entityclass', 'id', [
			'key' => 'ebookDownloadLink',
		]);
		$this->dbVersioning->insert('entityclass__language', null, [
			'id'			=> $entityClassId,
			'language_id'	=> $englishId,
			'name'			=> 'Ebook download link',
		]);
		$this->dbVersioning->insert('entityclass__language', null, [
			'id'			=> $entityClassId,
			'language_id'	=> $dutchId,
			'name'			=> 'Ebook download link',
		]);
		
		
		// ebookDownloadLink
		$assetTypeId = $this->dbVersioning->insert('assettype', 'id', [
			'key' => 'ebookDownloadLink',
		]);
		$this->dbVersioning->insert('assettype__language', null, [
			'id'			=> $assetTypeId,
			'language_id'	=> $englishId,
			'name'			=> 'Ebook download link',
		]);
		$this->dbVersioning->insert('assettype__language', null, [
			'id'			=> $assetTypeId,
			'language_id'	=> $dutchId,
			'name'			=> 'Ebook download link',
		]);
		
		
		$this->insertEbookDownloadLinkInvalidReason('bookHasNoContent', 'Book has no content', 'Boek bevat geen tekst');
		$this->insertEbookDownloadLinkInvalidReason('brokenZip', 'Damaged ZIP file', 'Beschadigd ZIP bestand');
		$this->insertEbookDownloadLinkInvalidReason('downloadFailed', 'Download failed', 'Download mislukt');
		$this->insertEbookDownloadLinkInvalidReason('downloadWriteFailed', 'Download save failed', 'Download opslaan mislukt');
		$this->insertEbookDownloadLinkInvalidReason('drm', 'DRM', 'DRM');
		$this->insertEbookDownloadLinkInvalidReason('duplicateBook', 'Duplicate book', 'Dubbel boek');
		$this->insertEbookDownloadLinkInvalidReason('duplicateUrl', 'Duplicate URL', 'Dubbele URL');
		$this->insertEbookDownloadLinkInvalidReason('fileTooSmall', 'File too small', 'Bestand te klein');
		$this->insertEbookDownloadLinkInvalidReason('invalidDownloadUrl', 'Invalid download URL', 'Ongeldige download URL');
		$this->insertEbookDownloadLinkInvalidReason('invalidEpub', 'Invalid EPUB file', 'Ongeldig EPUB bestand');
		$this->insertEbookDownloadLinkInvalidReason('invalidZip', 'Invalid ZIP file', 'Ongeldig ZIP bestand');
		$this->insertEbookDownloadLinkInvalidReason('tooManyRedirects', 'Too many redirects', 'Te veel redirects');
		$this->insertEbookDownloadLinkInvalidReason('urlFailed', 'Opening of URL failed', 'Openen URL mislukt');
		
		
		$this->insertEbookDownloadLinkType('boekhuis', 'Boekhuis', 'Boekhuis');
		$this->insertEbookDownloadLinkType('boenda', 'Boenda', 'Boenda');
		$this->insertEbookDownloadLinkType('eBoekhuis', 'eBoekhuis', 'eBoekhuis');
		$this->insertEbookDownloadLinkType('eBoekhuisFulfillment', 'eBoekhuis Fulfillment', 'eBoekhuis Fulfillment');
		$this->insertEbookDownloadLinkType('epagine', 'Epagine', 'Epagine');
		$this->insertEbookDownloadLinkType('kobo', 'Kobo', 'Kobo');
		$this->insertEbookDownloadLinkType('leesId', 'LeesID', 'LeesID');
		$this->insertEbookDownloadLinkType('librePrint', 'LibrePrint', 'LibrePrint');
		$this->insertEbookDownloadLinkType('shortcovers', 'Shortcovers', 'Shortcovers');
		$this->insertEbookDownloadLinkType('tagusbooks', 'tagusbooks', 'tagusbooks');
	}
	
	protected function insertEbookDownloadLinkInvalidReason($key, $english, $dutch)
	{
		$englishId	= $this->dbVersioning->select(['id', 'FROM', 'language', 'WHERE', ['key' => 'english']])->fetchField('id');
		$dutchId	= $this->dbVersioning->select(['id', 'FROM', 'language', 'WHERE', ['key' => 'dutch']])->fetchField('id');
		
		$id = $this->dbVersioning->insert('ebookdownloadlinkinvalidreason', 'id', [
			'key' => $key,
		]);
		$this->dbVersioning->insert('ebookdownloadlinkinvalidreason__language', null, [
			'id'			=> $id,
			'language_id'	=> $englishId,
			'name'			=> $english,
		]);
		$this->dbVersioning->insert('ebookdownloadlinkinvalidreason__language', null, [
			'id'			=> $id,
			'language_id'	=> $dutchId,
			'name'			=> $dutch,
		]);
	}
	
	protected function insertEbookDownloadLinkType($key, $english, $dutch)
	{
		$englishId	= $this->dbVersioning->select(['id', 'FROM', 'language', 'WHERE', ['key' => 'english']])->fetchField('id');
		$dutchId	= $this->dbVersioning->select(['id', 'FROM', 'language', 'WHERE', ['key' => 'dutch']])->fetchField('id');
		
		$id = $this->dbVersioning->insert('ebookdownloadlinktype', 'id', [
			'key' => $key,
		]);
		$this->dbVersioning->insert('ebookdownloadlinktype__language', null, [
			'id'			=> $id,
			'language_id'	=> $englishId,
			'name'			=> $english,
		]);
		$this->dbVersioning->insert('ebookdownloadlinktype__language', null, [
			'id'			=> $id,
			'language_id'	=> $dutchId,
			'name'			=> $dutch,
		]);
	}
	
	public function revert()
	{
		$this->dbVersioning->delete('ebookdownloadlinktype');
		$this->dbVersioning->delete('ebookdownloadlinkinvalidreason');
		$this->dbVersioning->delete('assettype', ['key' => 'ebookDownloadLink']);
		$this->dbVersioning->delete('entityclass', ['key' => 'ebookDownloadLink']);
		$this->dbVersioning->delete('entityclass', ['key' => 'assetOwnership']);
	}
}