<?php
namespace assetLink\asset;

use DateTime;
use bite\api\inbound\AbstractInboundApi;
use bite\data\conversion\ConverterChain;
use bite\data\conversion\RegexTextCleaner;
use bite\data\conversion\ReplaceValueConverter;
use bite\data\conversion\TextToDateConverter;
use bite\data\conversion\TextToDecimalConverter;
use bite\data\conversion\TrimConverter;
use bite\data\conversion\ValidatingConverter;
use bite\data\forging\ClosureDecisionForger;
use bite\data\forging\ForgedPropertyRelocator;
use bite\data\forging\IPropertyForger;
use bite\data\forging\ListForger;
use bite\data\forging\StructForger;
use bite\data\forging\ValueForger;
use bite\data\generating\UuidUtil;
use bite\data\validation\BinaryLengthValidator;
use bite\data\validation\EmailValidator;
use bite\data\validation\NumberRangeValidator;
use bite\data\validation\TextLengthValidator;
use bite\data\validation\ValueInListValidator;
use bite\di\DiTarget;
use bite\error\EError;
use bite\error\IError;
use bite\execution\action\ClosureAction;
use bite\international\date\DateUtil;
use bite\io\db\DatabaseService;
use bite\io\db\IDatabase;
use bite\io\stream\codec\Base64EncodingReader;
use bite\security\permission\IPermissionService;

/**
 *	@depdendency db
 *	@depdendency lockService
 *	@depdendency userOrCompanyEmailAddressService
 *	@depdendency userContext
 *	@depdendency systemContext
**/
class AssetDatabaseService extends DatabaseService
{
	/**
	 *	@param		string			$assetTypeKey
	 *	@param		string			$entityClassKey
	 *	@param		TId				$entityId
	 *	@param		DateTime|null	$acquireDate
	 *	@param		string			$email
	 *	@param		(email:string, personDetails:(firstName:string, lastName:string)|null, companyDetails:(name:string)|null)			$newOwner
	 *	@param		DateTime|null	$previousAcquireDate
	 *	@param		(email:string, personDetails:(firstName:string, lastName:string)|null, companyDetails:(name:string)|null)|null		$previousOwner
	 *	@return		TId		assetOwnership id
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *				@reason		propertyIsInvalid, (propertyName:string, path:array<string>, value:*)
	 *							@reason		tooLong, string
	 *							@reason		tooShort, string
	 *							@reason		emailInvalid, string
	 *	@throws		assetTypeNotFound, string
	 *	@throws		entityClassNotFound, string
	 *	@throws		entityAlreadyRegistered, ($entityClassId:TId, entityId:TId)
	 *	@throws		assetOwnershipAlreadyHasPreviousOwner, TId
	**/
	public function registerAsset($assetTypeKey, $entityClassKey, $entityId, DateTime $acquireDate = null, $newOwner, DateTime $previousAcquireDate = null, $previousOwner = null)
	{
		$trimConverter = new TrimConverter();
		$textConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new TextLengthValidator(1, $this->config['maxTextFieldLenth']))]);
		$emailValidatingConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new EmailValidator())]);
		
		$newOwnerForger = new StructForger(null, IPropertyForger::required, null, [
			new ValueForger('email', IPropertyForger::required, null, $emailValidatingConverter),
			new ClosureDecisionForger(function($partialRequestData)
			{
				if (isset($newOwner['personDetails']))
				{
					return new StructForger('personDetails', IPropertyForger::required, null, [
						new ValueForger('firstName', IPropertyForger::required, null, $textConverter),
						new ValueForger('lastName', IPropertyForger::required, null, $textConverter),
					]);
				}
				else if (isset($newOwner['companyDetails']))
				{
					return new StructForger('companyDetails', IPropertyForger::required, null, [
						new ValueForger('name', IPropertyForger::required, null, $textConverter),
					]);
				}
			}),
		]);
		
		try {
		$newOwner = $newOwnerForger->forge($newOwner);
		} catch (IError $e) { $e->rethrow(IPropertyForger::propertiesError); }
		
		$result = $this->db->select(['id', 'FROM', 'assettype', 'WHERE', ['key' => $assetTypeKey]]);
		if ($result->isEmpty()) throw new EError('assetTypeNotFound', $assetTypeKey);
		$assetTypeId = $result->fetchField('id');
		
		$result = $this->db->select(['id', 'FROM', 'entityclass', 'WHERE', ['key' => $entityClassKey]]); // @todo use entityclassservice?
		if ($result->isEmpty()) throw new EError('entityClassNotFound', $entityClassKey);
		$entityClassId = $result->fetchField('id');
		
		
		
		
		// @todo check if you have permission to register
		
		
		
		// need an explicit lock because the record might not exist yet
		return $this->lockService->lock('assetEntity.' . $entityClassId . '.' . $entityId, 3, 5, function() use ($assetTypeId, $entityClassId, $entityId, $acquireDate, $newOwner, $previousAcquireDate, $previousOwner)
		{
			$result = $this->db->select(['FROM', 'asset', 'WHERE', ['entityclass_id' => $entityClassId, 'entity_id' => $entityId]]);
			if ($result->hasRows()) throw new EError('entityAlreadyRegistered', ['entityClassId' => $entityClassId, 'entityId' => $entityId]);
			
			return $this->db->transaction(function() use ($assetTypeId, $entityClassId, $entityId, $acquireDate, $newOwner, $previousAcquireDate, $previousOwner)
			{
				//what is the use of verifying assetkeeper email? nothing really, but assetkeeper does need to accept ownership (via email, which automatically verifies email of assetkeeper)
				
				$assetKeeperId = $this->obtainOrCreateAssetKeeperByEmailAddress($newOwner['email']);
				
				
				//////////////////////personDetails/companyDetails
				
				
				$assetId = $this->db->insert('asset', 'id', [
					'creatorsystem_id'	=> $this->systemContext->getCurrentSystemId(),
					'creatoruser_id'	=> $this->userContext->getCurrentUserId(),
					'assettype_id'		=> $assetTypeId,
					'entityclass_id'	=> $entityClassId,
					'entity_id'			=> $entityId,
					'registrationdate'	=> $this->db->expression('UTC_TIMESTAMP()'),
					'uuid'				=> UuidUtil::v4Binary(),
					'isvalid'			=> 0, // @todo specify isvalid in func arguments
				]);
				
				
				if ($acquireDate !== null)
				{
					$acquireDate = DateUtil::copy($acquireDate);
					$acquireDate->setTimeZone(new DateTimeZone('UTC'));
					$acquireDate = $acquireDate->format('Y-m-d H:i:s');
				}
				$assetOwnershipId = $this->db->insert('assetownership', 'id', [
					'creatorsystem_id'	=> $this->systemContext->getCurrentSystemId(),
					'creatoruser_id'	=> $this->userContext->getCurrentUserId(),
					'asset_id'			=> $assetId,
					'assetkeeper_id'	=> $assetKeeperId,
					'registrationdate'	=> $this->db->expression('UTC_TIMESTAMP()'),
					'acquiredate'		=> $acquireDate,
				]);
				
				$this->db->insert('d_currentassetpossession', 'id', [
					'assetownership_id'	=> $assetOwnershipId,
					'assetkeeper_id'	=> $assetKeeperId,
					'asset_id'			=> $assetId,
				]);
				
				
				// @todo internal call, pass proper permissions and context
				$this->permissionService->grantPermissionBundle('manageAssetOwnership', 'assetOwnership', $assetOwnershipId);
				// includes permissions: providePreviousOwner, transfer, obtainEbookDownloadLinkStatus, obtainEpub, generateTransferPermissionToken, generateManagePermissionToken
				
				try {
				if ($previousOwner !== null) $this->provideNewPreviousOwner($assetOwnershipId, $previousAcquireDate, $previousOwner);
				} catch (IError $e) { $e->rethrow([IPropertyForger::propertiesError, 'assetOwnershipAlreadyHasPreviousOwner']); }
				
				return $assetOwnershipId;
			});
		});
	}
	
	/**
	 *	@param		TId					$assetOwnershipId
	 *	@param		DateTime|null		$previousAcquireDate
	 *	@param		(email:string, personDetails:(firstName:string, lastName:string)|null, companyDetails:(name:string)|null)			$previousOwner
	 *	@return		TId		previous assetOwnership id
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *				@reason		propertyIsInvalid, (propertyName:string, path:array<string>, value:*)
	 *							@reason		tooLong, string
	 *							@reason		tooShort, string
	 *							@reason		emailInvalid, string
	 *	@throws		permissionDenied
	 *	@throws		assetOwnershipNotFound, TId
	 *	@throws		assetOwnershipAlreadyHasPreviousOwner, TId
	**/
	public function provideNewPreviousOwner($assetOwnershipId, DateTime $previousAcquireDate = null, $previousOwner)
	{
		$trimConverter = new TrimConverter();
		$textConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new TextLengthValidator(1, $this->config['maxTextFieldLenth']))]);
		$emailValidatingConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new EmailValidator())]);
		
		$previousOwnerForger = new StructForger(null, IPropertyForger::required, null, [
			new ValueForger('email', IPropertyForger::required, null, $emailValidatingConverter),
			new ClosureDecisionForger(function($partialRequestData)
			{
				if (isset($previousOwner['personDetails']))
				{
					return new StructForger('personDetails', IPropertyForger::required, null, [
						new ValueForger('firstName', IPropertyForger::required, null, $textConverter),
						new ValueForger('lastName', IPropertyForger::required, null, $textConverter),
					]);
				}
				else if (isset($previousOwner['companyDetails']))
				{
					return new StructForger('companyDetails', IPropertyForger::required, null, [
						new ValueForger('name', IPropertyForger::required, null, $textConverter),
					]);
				}
			}),
		]);
		
		try {
		$previousOwner = $previousOwnerForger->forge($previousOwner);
		} catch (IError $e) { $e->rethrow(IPropertyForger::propertiesError); }
		
		// @todo internal call, pass proper permissions and context
		try {
		$this->permissionService->verifyPermission('providePreviousOwner', 'assetOwnership', $assetOwnershipId);
		} catch (IError $e) { $e->rethrow(IPermissionService::permissionDenied); } // @todo specific which permission you where denied
		
		$result = $this->db->select([
			'asset_id',
			'FROM', 'assetownership',
			'WHERE', [
				'id' => $assetOwnershipId,
			],
		]);
		if ($result->isEmpty()) throw new EError('assetOwnershipNotFound', $assetOwnershipId);
		$assetId = $result->fetchField('asset_id');
		
		return $this->db->transaction(function() use ($assetId, $assetOwnershipId, $previousAcquireDate, $previousOwner)
		{
			// lock asset
			$this->db->lockRecord('asset', 'id', $assetId);
			
			$result = $this->db->select([
				'acquiredate',
				'FROM', 'assetownership',
				'WHERE', [
					'id'						=> $assetOwnershipId,
					'previousassetownership_id'	=> null,
				],
			]);
			if ($result->isEmpty()) throw new EError('assetOwnershipAlreadyHasPreviousOwner', $assetOwnershipId);
			
			$transferDate = $result->fetchField('acquiredate');
			
			$assetKeeperId = $this->obtainOrCreateAssetKeeperByEmailAddress($previousOwner['email']);
			
			//////////////////////personDetails/companyDetails
			
			
			if ($previousAcquireDate !== null)
			{
				$previousAcquireDate = DateUtil::copy($previousAcquireDate);
				$previousAcquireDate->setTimeZone(new DateTimeZone('UTC'));
				$previousAcquireDate = $previousAcquireDate->format('Y-m-d H:i:s');
			}
			$previousAssetOwnershipId = $this->db->insert('assetownership', 'id', [
				'creatorsystem_id'			=> $this->systemContext->getCurrentSystemId(),
				'creatoruser_id'			=> $this->userContext->getCurrentUserId(),
				'asset_id'					=> $assetId,
				'assetkeeper_id'			=> $assetKeeperId,
				'd_nextassetownership_id'	=> $assetOwnershipId,
				'registrationdate'			=> $this->db->expression('UTC_TIMESTAMP()'),
				'acquiredate'				=> $previousAcquireDate,
				'd_transferdate'			=> $transferDate,
			]);
			
			$this->db->update('assetownership', [
				'previousassetownership_id' => $previousAssetOwnershipId,
			], [
				'id' => $assetOwnershipId,
			]);
			
			return $previousAssetOwnershipId;
		});
	}
	
	/**
	 *	@param		TId		$assetOwnershipId
	 *	@param		(email:string, personDetails:(firstName:string, lastName:string)|null, companyDetails:(name:string)|null)		$newOwner
	 *	@return		TId		new assetOwnership id
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *				@reason		propertyIsInvalid, (propertyName:string, path:array<string>, value:*)
	 *							@reason		tooLong, string
	 *							@reason		tooShort, string
	 *							@reason		emailInvalid, string
	 *	@throws		permissionDenied
	 *	@throws		assetOwnershipNotFound, TId
	 *	@throws		assetOwnershipAlreadyTransfered, TId
	**/
	public function transferAssetOwnership($assetOwnershipId, $newOwner)
	{
		$trimConverter = new TrimConverter();
		$textConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new TextLengthValidator(1, $this->config['maxTextFieldLenth']))]);
		$emailValidatingConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new EmailValidator())]);
		
		$newOwnerForger = new StructForger(null, IPropertyForger::required, null, [
			new ValueForger('email', IPropertyForger::required, null, $emailValidatingConverter),
			new ClosureDecisionForger(function($partialRequestData)
			{
				if (isset($newOwner['personDetails']))
				{
					return new StructForger('personDetails', IPropertyForger::required, null, [
						new ValueForger('firstName', IPropertyForger::required, null, $textConverter),
						new ValueForger('lastName', IPropertyForger::required, null, $textConverter),
					]);
				}
				else if (isset($newOwner['companyDetails']))
				{
					return new StructForger('companyDetails', IPropertyForger::required, null, [
						new ValueForger('name', IPropertyForger::required, null, $textConverter),
					]);
				}
			}),
		]);
		
		try {
		$newOwner = $newOwnerForger->forge($newOwner);
		} catch (IError $e) { $e->rethrow(IPropertyForger::propertiesError); }
		
		
		
// @todo how to use/inject permissionToken? using permission(Token)Context?
		// @todo internal call, pass proper permissions and context
		try {
		$this->permissionService->verifyPermission('transfer', 'assetOwnership', $assetOwnershipId);
		} catch (IError $e) { $e->rethrow(IPermissionService::permissionDenied); }
///////////you automatically have permission if you (current userContext) are (linked to) the assetkeeper (owner) < but this is a custom check, multiple users can be linked to assetkeeper (shared ownership)
//OWNER role
		
//can't transfer to the same assetkeeper?
		
		// @todo check if asset is valid?
		
		$result = $this->db->select([
			'asset_id',
			'FROM', 'assetownership',
			'WHERE', [
				'id' => $assetOwnershipId,
			],
		]);
		if ($result->isEmpty()) throw new EError('assetOwnershipNotFound', $assetOwnershipId);
		$assetId = $result->fetchField('asset_id');
		
		return $this->db->transaction(function() use ($assetId, $assetOwnershipId, $newOwner)
		{
			// lock asset
			$this->db->lockRecord('asset', 'id', $assetId);
			
			// @todo maybe lock on assetownership instead?
			$result = $this->db->select([
				'FROM', 'assetownership',
				'WHERE', [
					'id'				=> $assetOwnershipId,
					'd_transferdate'	=> null,
				],
			]);
			if ($result->isEmpty()) throw new EError('assetOwnershipAlreadyTransfered', $assetOwnershipId);
			
			$assetKeeperId = $this->obtainOrCreateAssetKeeperByEmailAddress($newOwner['email']);
			
			//////////////////////personDetails/companyDetails
			
			$nextAssetOwnershipId = $this->db->insert('assetownership', 'id', [
				'creatorsystem_id'			=> $this->systemContext->getCurrentSystemId(),
				'creatoruser_id'			=> $this->userContext->getCurrentUserId(),
				'asset_id'					=> $assetId,
				'assetkeeper_id'			=> $assetKeeperId,
				'previousassetownership_id'	=> $assetOwnershipId,
				'registrationdate'			=> $this->db->expression('UTC_TIMESTAMP()'),
				'acquiredate'				=> $this->db->expression('UTC_TIMESTAMP()'),
			]);
			$transferDate = $this->db->select(['acquiredate', 'FROM', 'assetownership', 'WHERE', ['id' => $nextAssetOwnershipId]])->fetchField('acquiredate');
			
			$this->db->update('assetownership', [
				'd_transferdate'			=> $transferDate,
				'd_nextassetownership_id'	=> $nextAssetOwnershipId,
			], [
				'id' => $assetOwnershipId,
			]);
			
			$this->db->insert('d_currentassetpossession', 'id', [
				'assetownership_id'	=> $nextAssetOwnershipId,
				'assetkeeper_id'	=> $assetKeeperId,
				'asset_id'			=> $assetId,
			]);
			
			$this->db->delete('d_currentassetpossession', [
				'assetownership_id'	=> $assetOwnershipId,
			]);
			
			
			// @todo update d_ebookassetpossession < hook???
			
			
			
			
			////////@todo add permissions? (depends on the new owner?)
			// @todo internal call, pass proper permissions and context
			$this->permissionService->grantPermissionBundle('manageAssetOwnership', 'assetOwnership', $nextAssetOwnershipId);
			
			
			
			// @todo add new ownership record to assetborrow_assetownership if asset was lent
			
			return $nextAssetOwnershipId;
		});
	}
	
	/**
	 *	@param		string		$email
	 *	@return		TId		assetKeeper id
	**/
	protected function obtainOrCreateAssetKeeperByEmailAddress($email)
	{
		return $this->db->transaction(function() use ($email)
		{
			// @todo verifying email address should also lock on this, so if it isnt verified yet, assetkeeper gets created, and verification can claim assetkeeper to user
			// need an explicit lock because the record might not exist yet
			return $this->lockService->lock('emailAddress.' . $email, 3, 5, function() use ($email)
			{
				$assetKeeperId = null;
				
				$userOrCompany = null;
				try
				{
					// @todo internal call, pass proper permissions and context
					$userOrCompany = $this->userOrCompanyEmailAddressService->obtainUserIdOrCompanyIdByVerifiedEmailAddress($email);
				}
				catch (IError $e)
				{
					$e->allow('emailNotFound');
					
					$result = $this->db->select(['id', 'FROM', 'assetkeeper', 'WHERE', ['email' => $email]]);
					$assetKeeperId = $result->hasRows() ? $result->fetchField('id') : $this->db->insert('assetkeeper', 'id', ['email' => $email]);
				}
				
				if ($userOrCompany !== null)
				{
					// user or company found
					$userOrCompanyField	= null;
					$userOrCompanyId		= null;
					if ($userOrCompany['userId'] !== null)
					{
						$userOrCompanyField	= 'user_id';
						$userOrCompanyId	= $userOrCompany['userId'];
					}
					else if ($userOrCompany['companyId'] !== null)
					{
						$userOrCompanyField	= 'company_id';
						$userOrCompanyId	= $userOrCompany['companyId'];
					}
					else
					{
						// not possible
						throw new EError('bug');
					}
						
					// find user's or company's assetkeeper with oldemail (can be multiple!)
					$result = $this->db->select([
						'id',
						'FROM', 'assetkeeper',
						'WHERE', [
							$userOrCompanyField	=> $userOrCompanyId,
							'oldemail'			=> $email,
						],
						'ORDER BY',
							'userorcompanyverificationdate', 'ASC',
						'LIMIT', 1,
					]);
					if ($result->hasRows())
					{
						$assetKeeperId = $result->fetchField('id');
					}
					else
					{
						$assetKeeperId = $this->db->insert('assetkeeper', 'id', [
							$userOrCompanyField				=> $userOrCompanyId,
							'oldemail'						=> $email,
							'userorcompanyverificationdate'	=> $this->db->expression('UTC_TIMESTAMP()'),
						]);
					}
				}
				
				return $assetKeeperId;
			});
		});
	}
	
	
	
	
	
	
	
	//transferUsingPermissionToken (+ option to not immediately transfer, but give blijvende permission using the token)
	
	
	/**
	 *	@param		TId		$assetOwnershipId
	 *	@return		string		passtoken
	 *	@throws		permissionDenied
	 *	@throws		assetOwnershipNotFound, TId
	**/
	public function generateManagePermissionPasstoken($assetOwnershipId)
	{
		// @todo internal call, pass proper permissions and context
		try {
		$this->permissionService->verifyPermission('generateManagePermissionPasstoken', 'assetOwnership', $assetOwnershipId);
		} catch (IError $e) { $e->rethrow(IPermissionService::permissionDenied); }
		
		$result = $this->db->select(['FROM', 'assetownership', 'WHERE', ['id' => $assetOwnershipId]]);
		if ($result->isEmpty()) throw new EError('assetOwnershipNotFound', $assetOwnershipId);
		
		//useonce is part of the passtoken type? or specified per passtoken?
		return $this->permissionPasstokenService->generateGrantPermissionBundlePasstoken('manageAssetOwnership', 'assetOwnership', $assetOwnershipId);
	}
	
	/**
	 *	@param		string		$passtoken
	 *	@return		TId		asset ownership
	 *	@throws		passtokenFormatInvalid, string
	 *	@throws		timeout, (loginName:string, timeout:DateInterval)
	 *	@throws		passtokenInvalid, (passtoken:string, timeout:DateInterval)
	 *	@throws		passtokenTypeInvalid, string
	**/
	public function grantManagePermissionUsingPasstoken($passtoken)
	{
		try {
		return $this->permissionPasstokenService->grantPermissionBundleUsingPasstoken('manageAssetOwnership', 'assetOwnership', $passtoken);
		} catch (IError $e) { $e->rethrow(['passtokenFormatInvalid', 'timeout', 'passtokenInvalid', 'passtokenTypeInvalid']); }
	}
}