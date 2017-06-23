<?php
namespace assetLink\reading\book\asset;

use Exception;
use Throwable;
use DateTime;
use DateTimeZone;
use bite\data\conversion\SlugConverter;
use bite\data\forging\IPropertyForger;
use bite\data\generating\UuidUtil;
use bite\data\validation\UrlValidator;
use bite\di\DiTarget;
use bite\error\EError;
use bite\error\IError;
use bite\io\db\DatabaseService;
use bite\security\permission\IPermissionService;
use assetLink\reading\book\asset\EbookAssetDatabaseServiceHelper;

/**
 *	@depdendency db
 *	@depdendency assetService
 *	@depdendency lockService
 *	@depdendency systemContext
 *	@depdendency userContext
**/
class EbookAssetDatabaseService extends DatabaseService
{
	const ownership				= 'ownership';
	const lending				= 'lending';
	
	protected $ebookAssetDatabaseServiceHelper		= null;
	
	/**
	 *	Use this as the replacement for the regular constructor
	 *	
	 *	@param		void
	 *	@return		void
	**/
	protected function diConstruct()
	{
		$this->ebookAssetDatabaseServiceHelper = $this->initDi(new EbookAssetDatabaseServiceHelper());
	}
	
	/**
	 *	@note person-/companydetails of already existing owners (new or original) are discarded
	 *	
	 *	@param		string				$downloadLink
	 *	@param		DateTime|null		$acquireDate
	 *	@param		(email:string, personDetails:(firstName:string, lastName:string)|null, companyDetails:(name:string)|null)			$newOwner
	 *	@param		DateTime|null		$previousAcquireDate
	 *	@param		(email:string, personDetails:(firstName:string, lastName:string)|null, companyDetails:(name:string)|null)|null		$previousOwner
	 *	@return		TId		assetOwnership id
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *				@reason		propertyIsInvalid, (propertyName:string, path:array<string>, value:*)
	 *							@reason		tooLong, string
	 *							@reason		tooShort, string
	 *							@reason		emailInvalid, string
	 *	@throws		urlInvalid, string
	 *	@throws		downloadLinkAlreadyExists, string
	**/
	public function registerEbookDownloadLink($downloadLink, DateTime $acquireDate = null, $newOwner, DateTime $previousAcquireDate = null, $previousOwner = null)
	{
		
		
		//webhook?
		
		
		
		$downloadLink = trim($downloadLink);
		
		$urlValidator = new UrlValidator([UrlValidator::schemeRequired, UrlValidator::hostRequired, UrlValidator::pathRequired]);
		if (!$urlValidator->validate($downloadLink)->isValid()) throw new EError('urlInvalid', $downloadLink);
		
		
		
		$downloadLinkUrlParts = null;
		try
		{
			$downloadLinkUrlParts = $this->ebookAssetDatabaseServiceHelper->getDownloadLinkUrlParts($downloadLink);
		}
		catch (IError $e)
		{
			if ($e->match('urlInvalid'))
			{
				$e->rethrow();
			}
			else if ($e->match('downloadLinkInvalid'))
			{
				// allow, $downloadLinkUrlParts remains null, ignore
			}
			else
			{
				$e->noMatch();
			}
		}
		
		
		// @note using unsecure lock and pre-check (secure lock should be used when validating the download links)
		return $this->lockService->lock('registerEbookDownloadLink.' . $downloadLink, 3, 5, function() use ($downloadLink, $acquireDate, $newOwner, $previousAcquireDate, $previousOwner, $downloadLinkUrlParts)
		{
			// pre-check to see if url was already registered before
			// @note also need to check again after 'validating' the actual URL/book (because they could contain redirects to a valid download URL)
			if ($this->ebookAssetDatabaseServiceHelper->urlExists($downloadLink, $downloadLinkUrlParts)) throw new EError('downloadLinkAlreadyExists', $downloadLink);
			$data = [
				'registrationdate'	=> $this->db->expression('UTC_TIMESTAMP()'),
				'url'				=> $downloadLink,
			];
			if ($downloadLinkUrlParts !== null)
			{
				$result = $this->db->select(['id', 'FROM', 'ebookdownloadlinktype', 'WHERE', ['key' => $downloadLinkUrlParts['type']]]);
				if ($result->isEmpty()) throw new EError('ebookDownloadLinkTypeNotFound', $downloadLinkUrlParts['type']);
				$ebookDownloadLinkTypeId = $result->fetchField('id');
				
				$data['ebookdownloadlinktype_id']	= $ebookDownloadLinkTypeId;
				$data['d_uniqueurlparts']			= $downloadLinkUrlParts['uniqueurlparts'];
				$data['isbn']						= $downloadLinkUrlParts['isbn'];
			}
			
			// @todo multiservice transaction (not needed here because we use db directly)
			return $this->db->transaction(function() use ($downloadLink, $acquireDate, $newOwner, $previousAcquireDate, $previousOwner, $data)
			{
				$ebookDownloadLinkId = $this->db->insert('ebookdownloadlink', 'id', $data);
				
				// @todo internal call, pass proper permissions and context
				try {
				$assetOwnershipId = $this->assetService->registerAsset('ebookDownloadLink', 'ebookDownloadLink', $ebookDownloadLinkId, $acquireDate, $newOwner, $previousAcquireDate, $previousOwner);
				} catch (IError $e) { $e->rethrow(IPropertyForger::propertiesError); }
				
				return $assetOwnershipId;
			});
		});
	}
	
	/**
	 *	@note url is only returned when link was invalid
	 *	
	 *	@param		TId		$assetOwnershipId
	 *	@return		(
		 				isChecked:boolean,
						url:string|null,
						hasBeenValid:boolean,
						isbn:string|null,
						metaAuthor:string|null,
						metaTitle:string|null,
						metaIsbns:array<string>,
						hasMetaCover:boolean,
						invalidReasonKey:string|null,
						//invalidReasonName:string|null,
						isValid:boolean,
						ebookIsProcessed:boolean,
						ebookIsDamaged:boolean
	 				)
	 *	@throws		permissionDenied
	 *	@throws		assetOwnershipNotFound, TId
	**/
	public function obtainEbookDownloadLinkStatus($assetOwnershipId)
	{
		/////webhook: when validated/processed
		
		//provideEbookDownloadLinkIsbn
		
		try {
		$row = $this->_obtainEbookDownloadLinkRow($assetOwnershipId, true);
		} catch (IError $e) { $e->rethrow([IPermissionService::permissionDenied, 'assetOwnershipNotFound']); }
		
		// @note only return url if it has never been valid and is checked
		$status = [
			'isChecked'			=> ($row['ischecked'] === '1'),
			'url'				=> ($row['d_hasbeenvalid'] === '0' && $row['ischecked'] === '1') ? $row['url'] : null,
			'hasBeenValid'		=> ($row['d_hasbeenvalid'] === '1'),
			'isbn'				=> ($row['isbn'] !== '') ? $row['isbn'] : null,
			'metaAuthor'		=> ($row['metaauthor'] !== '') ? $row['metaauthor'] : null,
			'metaTitle'			=> ($row['metatitle'] !== '') ? $row['metatitle'] : null,
			'metaIsbns'			=> $row['metaIsbns'],
			'hasMetaCover'		=> ($row['metacoverstoredfilename'] !== null && $row['metacoverstoredfilename'] !== ''),
			'invalidReasonKey'	=> ($row['invalidReasonKey'] !== null) ? $row['invalidReasonKey'] : null,
			'isValid'			=> ($row['isvalid'] === '1'),
			'ebookIsProcessed'	=> ($row['storedfilename'] !== null && $row['storedfilename'] !== ''),
			'ebookIsDamaged'	=> ($row['isdamaged'] === '1'),
			//'invalidReasonName'=> ($row['invalidReasonName'] !== null) ? $row['invalidReasonName'] : null,
		];
		
		return $status;
	}
	
	/**
	 *	@param		TId		$assetOwnershipId
	 *	@return		IReader<binary>
	 *	@throws		permissionDenied
	 *	@throws		assetOwnershipNotFound, TId
	 *	@throws		metaCoverNotFound, TId
	**/
	public function downloadEbookMetaCover($assetOwnershipId)
	{
		try {
		$row = $this->_obtainEbookDownloadLinkRow($assetOwnershipId);
		} catch (IError $e) { $e->rethrow([IPermissionService::permissionDenied, 'assetOwnershipNotFound']); }
		
		if ($row['metacoverstoredfilename'] === '') throw new EError('metaCoverNotFound', $assetOwnershipId);
		
		return $this->assetStore->obtainFile($this->config['s3.bucket'], $this->config['s3.directory'] . 'epub-cover/' . $row['metacoverstoredfilename']);
	}
	
	/**
	 *	@param		TId			$assetOwnershipId
	 *	@param		boolean		$includeMetaIsbns
	 *	@return		(?)
	 *	@throws		permissionDenied
	 *	@throws		assetOwnershipNotFound, TId
	**/
	protected function _obtainEbookDownloadLinkRow($assetOwnershipId, $includeMetaIsbns = false)
	{
		// @note function is also used to obtain meta cover, so does the permission 'obtainEbookDownloadLinkStatus' make sense?
		//do both calls needs their own permissions?
		
		// @todo internal call, pass proper permissions and context
		try {
		$this->permissionService->verifyPermission('obtainEbookDownloadLinkStatus', 'assetOwnership', $assetOwnershipId);
		} catch (IError $e) { $e->rethrow(IPermissionService::permissionDenied); }
		
		// @todo $assetOwnershipId may never be null!
		/*
		$result = $this->db->select($this->build([
			'edl.id',
			'edl.ischecked',
			'edl.url',
			'edl.d_hasbeenvalid',
			'edl.isbn',
			'edl.metaauthor',
			'edl.metatitle',
			'edl.metacoverstoredfilename',
			['edlir.key', 'invalidReasonKey'],
			['edlirlang.name', 'invalidReasonName'],
			'edlv.isvalid',
			'eap.storedfilename',
			'e.isdamaged',
			'FROM', 'd_currentassetpossession', 'cap',
			'INNER JOIN', 'asset', 'a', ['a.id' => 'cap.asset_id'],
			'INNER JOIN', 'assettype', 'at', ['at.id' => 'a.assettype_id'],
			'INNER JOIN', 'entityclass', 'ec', ['ec.id' => 'a.entityclass_id'],
			'INNER JOIN', 'ebookdownloadlink', 'edl', ['edl.id' => 'a.entity_id'],
			'LEFT JOIN', 'ebookdownloadlinkvalidation', 'edlv', ['edlv.id' => 'edl.d_latestebookdownloadlinkvalidation_id'],
			'LEFT JOIN', 'ebookdownloadlinkinvalidreason', 'edlir', ['edlir.id' => 'edlv.ebookdownloadlinkinvalidreason_id'],
			'LEFT JOIN', 'd_ebookassetpossession', 'eap', ['eap.assetownership_id' => 'cap.assetownership_id'],
			'LEFT JOIN', 'ebook', 'e', ['e.id' => 'eap.ebook_id'],
			'WHERE', [
				'cap.assetownership_id'	=> $assetOwnershipId,
				'cap.islent'			=> 0, // @todo maybe not necessary (can get status even if it's lent?)
				'at.key'				=> 'ebookDownloadLink',
				'ec.key'				=> 'ebookDownloadLink',
			],
		])
		->addLanguageContext('edlir')
		);
		*/
		$result = $this->db->select([
			'edl.id',
			'edl.ischecked',
			'edl.url',
			'edl.d_hasbeenvalid',
			'edl.isbn',
			'edl.metaauthor',
			'edl.metatitle',
			'edl.metacoverstoredfilename',
			['edlir.key', 'invalidReasonKey'],
			'edlv.isvalid',
			'eap.storedfilename',
			'e.isdamaged',
			'FROM', 'd_currentassetpossession', 'cap',
			'INNER JOIN', 'asset', 'a', ['a.id' => 'cap.asset_id'],
			'INNER JOIN', 'assettype', 'at', ['at.id' => 'a.assettype_id'],
			'INNER JOIN', 'entityclass', 'ec', ['ec.id' => 'a.entityclass_id'],
			'INNER JOIN', 'ebookdownloadlink', 'edl', ['edl.id' => 'a.entity_id'],
			'LEFT JOIN', 'ebookdownloadlinkvalidation', 'edlv', ['edlv.id' => 'edl.d_latestebookdownloadlinkvalidation_id'],
			'LEFT JOIN', 'ebookdownloadlinkinvalidreason', 'edlir', ['edlir.id' => 'edlv.ebookdownloadlinkinvalidreason_id'],
			'LEFT JOIN', 'd_ebookassetpossession', 'eap', ['eap.assetownership_id' => 'cap.assetownership_id'],
			'LEFT JOIN', 'ebook', 'e', ['e.id' => 'eap.ebook_id'],
			'WHERE', [
				'cap.assetownership_id'	=> $assetOwnershipId,
				'cap.islent'			=> 0, // @todo maybe not necessary (can get status even if it's lent?)
				'at.key'				=> 'ebookDownloadLink',
				'ec.key'				=> 'ebookDownloadLink',
			],
		]);
		if ($result->isEmpty()) throw new EError('assetOwnershipNotFound', $assetOwnershipId);
		
		$row = $result->fetchRow();
		
		if ($includeMetaIsbns)
		{
			$result = $this->db->select([
				'metaisbn',
				'FROM', 'ebookdownloadlinkmetaisbn',
				'WHERE', [
					'ebookdownloadlink_id' => $row['id'],
				],
			]);
			
			// @todo to just return the result as is as a 'stream' (to be encoded by the encodingpipe), we need to wrap it in an iterator that returns the 'metaisbn' value from each row instead of the row itself
			//$row['metaIsbns'] = $result;
			
			$metaIsbns = array();
			foreach ($result as $row2)
			{
				$metaIsbns[] = $row2['metaisbn'];
			}
			$row['metaIsbns'] = $metaIsbns;
		}
		
		// @note not absolutely necessary
		unset($row['id']);
		
		return $row;
	}
	
	/**
	 *	@param		TId		$assetOwnershipId
	 *	@return		IReader<binary>
	 *	@throws		permissionDenied
	 *	@throws		ebookNotFound, TId
	 *	@throws		assetIsLent, TId
	 *	@throws		ebookNotProcessed, TId
	**/
	public function obtainOwnedEpub($assetOwnershipId)
	{
		// @todo internal call, pass proper permissions and context
		try {
		$this->permissionService->verifyPermission('obtainEpub', 'assetOwnership', $assetOwnershipId);
		} catch (IError $e) { $e->rethrow(IPermissionService::permissionDenied); }
		
		try
		{
			return $this->_obtainEpub($assetOwnershipId, self::ownership);
		}
		catch (IError $e)
		{
			$e->rethrow(['ebookNotFound', 'assetIsLent', 'ebookNotProcessed']);
		}
	}
	
	/**
	 *	@param		TId		$assetBorrowId
	 *	@return		IReader<binary>
	 *	@throws		permissionDenied
	 *	@throws		ebookNotFound, TId
	 *	@throws		assetIsLent, TId
	 *	@throws		ebookNotProcessed, TId
	**/
	public function obtainBorrowedEpub($assetBorrowId)
	{
		// @todo internal call, pass proper permissions and context
		try {
		$this->permissionService->verifyPermission('obtainEpub', 'assetBorrow', $assetBorrowId);
		} catch (IError $e) { $e->rethrow(IPermissionService::permissionDenied); }
		
		try
		{
			return $this->_obtainEpub($assetOwnershipId, self::lending);
		}
		catch (IError $e)
		{
			$e->rethrow(['ebookNotFound', 'assetIsLent', 'ebookNotProcessed']);
		}
		
	}
	
	/**
	 *	@param		TId			$assetOwnershipOrLendingId
	 *	@param		const		$type
	 *	@return		IReader<binary>
	 *	@throws		ebookNotFound, TId
	 *	@throws		assetIsLent, TId
	 *	@throws		ebookNotProcessed, TId
	**/
	protected function _obtainEpub($assetOwnershipOrLendingId, $type = self::ownership)
	{
		$conditionsDsl = [];
		if ($type === self::lending)
		{
			$conditionsDsl['assetborrow_id'] = $assetOwnershipOrLendingId;
		}
		else
		{
			$conditionsDsl['assetownership_id'] = $assetOwnershipOrLendingId;
		}
		
		// @todo $assetOwnershipOrLendingId may never be null!
		
		$result = $this->db->select([
			'islent',
			'storedfilename',
			'FROM', 'd_ebookassetpossession',
			'WHERE', $conditionsDsl,
		]);
		if ($result->isEmpty()) throw new EError('ebookNotFound', $assetOwnershipOrLendingId);
		$row = $result->fetchRow();
		
		if ($row['islent'] === '1') throw new EError('assetIsLent', $assetOwnershipOrLendingId);
		if ($row['storedfilename'] === '') throw new EError('ebookNotProcessed', $assetOwnershipOrLendingId);
		
		return $this->assetStore->obtainFile($this->config['s3.bucket'], $this->config['s3.directory'] . 'epub-possession/' . $row['storedfilename']);
	}
	
	/**
	 *	@param		integer		$limit
	 *	@return		void
	**/
	public function validateEbookDownloadLinks($limit = 20)
	{
		// @todo only allow internal call
		
		$this->ebookAssetDatabaseServiceHelper->validateEbookDownloadLinks($limit);
	}
	
	/**
	 *	@param		integer		$limit
	 *	@return		void
	**/
	public function processEbookAssets($limit = 20)
	{
		// @todo only allow internal call
		
		$limit = (int)$limit;
		if ($limit < 1) throw new EError('limitInvalid', $limit);
		
		// @todo we don't actually need this lock if multiple servers want to process different downloadLinks
		// @note without soft lock the transaction of the lock spans all calls inside, so any locks created inside will block others until the end of this giant lock
		$this->lockService->softLockOrSkip('processEbookAssets', 20, function() use ($limit)
		{
			$result = $this->db->select([
				'eap.id',
				'eap.assetownership_id',
				'eap.assetborrow_id',
				'eap.assetkeeper_id',
				'a.registrationdate',
				'a.uuid',
				[$this->db->expression('IFNULL(' . $this->db->quoteIdentifier('ak.email') . ', ' . $this->db->quoteIdentifier('ak.oldemail') . ')'), 'email'],
				'e.isbn',
				'e.author',
				'e.title',
				'e.originalstoredfilename',
				
				[$this->db->expression('IFNULL(' . $this->db->quoteIdentifier('p.firstname') . ', ' . $this->db->quoteIdentifier('dp.firstname') . ')'), 'firstname'],
				[$this->db->expression('IFNULL(' . $this->db->quoteIdentifier('p.lastname') . ', ' . $this->db->quoteIdentifier('dp.lastname') . ')'), 'lastname'],
				[$this->db->expression('IFNULL(' . $this->db->quoteIdentifier('c.officialname') . ', ' . $this->db->quoteIdentifier('dc.officialname') . ')'), 'officialname'],
				
				'FROM', 'd_ebookassetpossession', 'eap',
				'INNER JOIN', 'asset', 'a', ['a.id' => 'eap.asset_id'],
				'INNER JOIN', 'assetkeeper', 'ak', ['ak.id' => 'eap.assetkeeper_id'],
				'INNER JOIN', 'ebook', 'e', ['e.id' => 'eap.ebook_id'],
				
				'LEFT JOIN', 'user', 'u', ['u.id' => 'ak.user_id'],
				'LEFT JOIN', 'person', 'p', ['p.id' => 'u.person_id'],
				'LEFT JOIN', 'company', 'c', ['c.id' => 'ak.company_id'],
				
				'LEFT JOIN', 'person', 'dp', ['dp.id' => 'ak.detailsperson_id'],
				'LEFT JOIN', 'company', 'dc', ['dc.id' => 'ak.detailscompany_id'],
				
				'WHERE', [
					'eap.storedfilename' => '',
				],
				'LIMIT', $limit,
			]);
			foreach ($result as $row)
			{
				$this->lockService->extendLock('processEbookAssets', 20);
				$this->processEbookAsset($row);
			}
		});
	}
	
	/**
	 *	@param		(?)		$row
	 *	@return		void
	**/
	protected function processEbookAsset($row)
	{
		// use explicit lock so we can skip instead of block
		$this->lockService->lockOrSkip('processEbookAsset.' . $row['id'], 20, function() use ($row)
		{
			$keeperName = ($row['firstname'] !== null) ? $row['firstname'] . ' ' . $row['lastname'] : $row['officialname'];
			$storedFilename = $this->generateFilename($keeperName, $row['isbn'], $row['author'], $row['title']);
			
			$transactionReference = ($row['assetownership_id'] !== null) ? $row['assetownership_id'] : $row['assetborrow_id'];
			
			// download original epub
			$epub = $this->assetStore->obtainFile($this->config['s3.bucket'], $this->config['s3.directory'] . 'epub-original/' . $row['originalstoredfilename']);
			
			// watermark
			$watermarkedEpub = $this->epubWatermarkService->watermarkFile($epub, $transactionReference, $row['uuid'], new DateTime($row['registrationdate'], new DateTimeZone('UTC')), $row['assetkeeper_id'], $keeperName, $row['email']);
			
			// upload epub to live
			$this->assetStore->assignFile($this->config['s3.bucket'], $this->config['s3.directory'] . 'epub-possession/' . $storedFilename, $watermarkedEpub);
			
			$this->db->update('d_ebookassetpossession', [
				'storedfilename' => $storedFilename,
			], [
				'assetownership_id'	=> $row['assetownership_id'],
				'assetborrow_id'	=> $row['assetborrow_id'],
			]);
		});
	}
	
	/**
	 *	@param		string		$keeperName
	 *	@param		string		$isbn
	 *	@param		string		$metaAuthor
	 *	@param		string		$metaTitle
	 *	@return		void
	**/
	protected function generateFilename($keeperName = '', $isbn = '', $metaAuthor = '', $metaTitle = '')
	{
		// @note the database field is max 191 characters (config['maxTextFieldLenth'])
		// the filename is composed of the following parts:
		// cu/customer-name/90/customer-name_9781234567890_21751006e1fa4d6b9ad6d4c360d216b2_author_title.epub
		// so the minimum number of characters is: 61
		// @example: cu//90/_9781234567890_21751006e1fa4d6b9ad6d4c360d216b2__.epub
		// the largest S3 directory we use is: assetLink/preview/epub-possession/
		// (which we don't store in the filename yet, but might in the future)
		// which is another 34 characters, making the total minimum: 61 + 34 = 95
		// which leaves: 191 - 95 = 96 characters for the customer name, author and title
		
		//$maxCharacterCount		= $this->config['maxTextFieldLenth'];
		//$usedCharacterCount		= strlen('cu//90/_9781234567890_21751006e1fa4d6b9ad6d4c360d216b2__.epub') + strlen('assetLink/preview/epub-possession/');
		$maxPartCharacterCount	= 24; // takes * 4 charachters (so 96) in the final string
		
		if ($isbn === '') $isbn = 'unknown';
		
		$metaAuthor = SlugConverter::convertSlug($metaAuthor);
		$metaAuthor = mb_substr($metaAuthor, 0, $maxPartCharacterCount);
		if ($metaAuthor === '') $metaAuthor = 'unknown';
		
		$metaTitle = SlugConverter::convertSlug($metaTitle);
		$metaTitle = mb_substr($metaTitle, 0, $maxPartCharacterCount);
		if ($metaTitle === '') $metaTitle = 'unknown';
		
		// last 2 numbers of the isbn
		$isbnDirectory = substr($isbn, -2);
		
		
		$keeperName = SlugConverter::convertSlug($keeperName);
		$keeperName = mb_substr($keeperName, 0, $maxPartCharacterCount);
		if ($keeperName === '') $keeperName = 'unknown';
		
		$keeperDirectory = strtr($keeperName, '-', '');
		$keeperDirectory = substr($keeperDirectory, 0, 2);
		$keeperDirectory = str_pad($keeperDirectory, 2, 'a');
		
		return $keeperDirectory . '/' . $keeperName . '/' . $isbnDirectory . '/' . $keeperName . '_' . $isbn . '_' . UuidUtil::v4Hex() . '_' . $metaAuthor . '_' . $metaTitle . '.epub';
	}
	
	////////// list all ebooks of user > also from other merchantsssss (popup > grant permission to merchant)
	
}