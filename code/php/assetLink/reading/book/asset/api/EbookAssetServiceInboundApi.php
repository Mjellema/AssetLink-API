<?php
namespace assetLink\reading\book\asset\api;

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
use bite\data\validation\BinaryLengthValidator;
use bite\data\validation\EmailValidator;
use bite\data\validation\NumberRangeValidator;
use bite\data\validation\TextLengthValidator;
use bite\data\validation\UrlValidator;
use bite\data\validation\ValueInListValidator;
use bite\di\DiTarget;
use bite\error\EError;
use bite\error\IError;
use bite\execution\action\ClosureAction;
use bite\security\permission\IPermissionService;

/**
 *	@dependency ebookAssetService
 *	@dependency apiEntityService
 *	@dependency apiContext
 *	@dependency config
 *	@dependency inboundApiIdDecodingConverter
 *	@dependency inboundApiBinaryEncodingConverter
**/
class EbookAssetServiceInboundApi extends AbstractInboundApi
{
	/**
	 *	@param		string		$action
	 *	@return		boolean
	**/
	public function hasAction($action)
	{
		//return in_array($action, ['registerEbookDownloadLink', 'obtainEbookDownloadLinkStatus', 'downloadEbookMetaCover', 'registerNewEbookFile', 'downloadEbook']);
		return in_array($action, ['registerEbookDownloadLink', 'obtainEbookDownloadLinkStatus', 'downloadEbookMetaCover', 'downloadEbook']);
	}
	
	/**
	 *	@note person-/companydetails of already existing owners (new or previous) are discarded
	 *	
	 *	@param		(
	 					downloadLink:string,
	 					acquireDate:string|null,
	 					newOwner:(email:string, personDetails:(firstName:string, lastName:string)|null, companyDetails:(name:string)|null),
	 					previousAcquireDate:string|null,
	 					previousOwner:(email:string, personDetails:(firstName:string, lastName:string)|null, companyDetails:(name:string)|null)|null
	 				)		$rawRequestData
	 *	@param		string		$requestIdentifier
	 *	@return		TEncodedId		assetOwnership id
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *				@reason		propertyIsInvalid, (propertyName:string, path:array<string>, value:*)
	 *							@reason		tooLong, string
	 *							@reason		tooShort, string
	 *							@reason		emailInvalid, string
	 *							@reason		dateInvalid, string
	 *	@throws		urlInvalid, string @note is duplicate check (also in propertiesError)
	 *	@throws		downloadLinkAlreadyExists, string
	**/
	public function handleRegisterEbookDownloadLink($rawRequestData, $requestIdentifier = null)
	{
		$trimConverter = new TrimConverter();
		$textConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new TextLengthValidator(1, $this->config['maxTextFieldLenth']))]);
		$urlValidatingConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new UrlValidator([UrlValidator::schemeRequired, UrlValidator::hostRequired, UrlValidator::pathRequired]))]);
		$emailValidatingConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new EmailValidator())]);
		
		$dataForger = new StructForger(null, IPropertyForger::required, null, [
			new ValueForger('downloadLink', IPropertyForger::required, null, $urlValidatingConverter),
			new ValueForger('acquireDate', IPropertyForger::optional, null, new TextToDateConverter()),
			new StructForger('newOwner', IPropertyForger::required, null, [
				new ValueForger('email', IPropertyForger::required, null, $emailValidatingConverter),
				new ClosureDecisionForger(function($partialRequestData) use ($rawRequestData, $textConverter)
				{
					if (isset($rawRequestData['newOwner']['personDetails']))
					{
						return new StructForger('personDetails', IPropertyForger::required, null, [
							new ValueForger('firstName', IPropertyForger::required, null, $textConverter),
							new ValueForger('lastName', IPropertyForger::required, null, $textConverter),
						]);
					}
					else if (isset($rawRequestData['newOwner']['companyDetails']))
					{
						return new StructForger('companyDetails', IPropertyForger::required, null, [
							new ValueForger('name', IPropertyForger::required, null, $textConverter),
						]);
					}
				}),
			]),
			new ValueForger('previousAcquireDate', IPropertyForger::optional, null, new TextToDateConverter()),
			new StructForger('previousOwner', IPropertyForger::optional, null, [
				new ValueForger('email', IPropertyForger::required, null, $emailValidatingConverter),
				new ClosureDecisionForger(function($partialRequestData) use ($rawRequestData, $textConverter)
				{
					if (isset($rawRequestData['previousOwner']['personDetails']))
					{
						return new StructForger('personDetails', IPropertyForger::required, null, [
							new ValueForger('firstName', IPropertyForger::required, null, $textConverter),
							new ValueForger('lastName', IPropertyForger::required, null, $textConverter),
						]);
					}
					else if (isset($rawRequestData['previousOwner']['companyDetails']))
					{
						return new StructForger('companyDetails', IPropertyForger::required, null, [
							new ValueForger('name', IPropertyForger::required, null, $textConverter),
						]);
					}
				}),
			]),
		]);
		
		try {
		$requestData = $dataForger->forge($rawRequestData);
		} catch (IError $e) { $e->rethrow(IPropertyForger::propertiesError); }
		
		// @todo multiservice transaction (only needed if requestIdentifier !== null)
		
		try {
		$assetOwnershipId = $this->ebookAssetService->registerEbookDownloadLink($requestData['downloadLink'], $requestData['acquireDate'], $requestData['newOwner'], $requestData['previousAcquireDate'], $requestData['previousOwner']);
		} catch (IError $e) { $e->rethrow('downloadLinkAlreadyExists'); }
		
		if ($requestIdentifier !== null) $this->apiEntityService->assignApiResponse($this->apiContext->getCurrentApiId(), $this->apiContext->getCurrentApiVersion(), 'ebookAssetService', 'registerEbookDownloadLink', $requestIdentifier, $assetOwnershipId);
		
		return $this->encodeRegisterEbookDownloadLink($assetOwnershipId);
	}
	
	/**
	 *	@param		TId
	 *	@return		TEncodedId
	**/
	public function encodeRegisterEbookDownloadLink($assetOwnershipId)
	{
		return $this->inboundApiIdEncodingConverter->convert($assetOwnershipId);
	}
	
	/**
	 *	@note url is only returned when link was invalid
	 *	
	 *	@param		(assetOwnershipId:TEncodedId)		$rawRequestData
	 *	@return		(
		 				isChecked:boolean,
						url:string|null,
						hasBeenValid:boolean,
						isbn:string,
						metaAuthor:string,
						metaTitle:string,
						hasMetaCover:boolean,
						invalidReasonKey:string|null,
						//invalidReasonName:string|null,
						isValid:boolean,
						ebookIsProcessed:boolean,
						ebookIsDamaged:boolean
	 				)
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *				@reason		propertyIsInvalid, (propertyName:string, path:array<string>, value:*)
	 *							@reason		??notANumber, string
	 *							@reason		??notAWholeNumber, string
	 *							@reason		??tooLow, string
	 *							@note all these are injected by the inboundApiIdDecodingConverter
	 *	@throws		permissionDenied
	 *	@throws		assetOwnershipNotFound, TEncodedId
	**/
	public function handleObtainEbookDownloadLinkStatus($rawRequestData)
	{
		$dataForger = new StructForger(null, IPropertyForger::required, null, [
			new ValueForger('assetOwnershipId', IPropertyForger::required, null, $this->inboundApiIdDecodingConverter),
		]);
		
		try {
		$requestData = $dataForger->forge($rawRequestData);
		} catch (IError $e) { $e->rethrow(IPropertyForger::propertiesError); }
		
		try {
		return $this->ebookAssetService->obtainEbookDownloadLinkStatus($requestData['assetOwnershipId']);
		} catch (IError $e) { $e->rethrow([IPermissionService::permissionDenied, 'assetOwnershipNotFound']); }
	}
	
	/**
	 *	@param		(assetOwnershipId:TEncodedId)		$rawRequestData
	 *	@return		TEncodedBinary		cover file
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *				@reason		propertyIsInvalid, (propertyName:string, path:array<string>, value:*)
	 *							@reason		??notANumber, string
	 *							@reason		??notAWholeNumber, string
	 *							@reason		??tooLow, string
	 *							@note all these are injected by the inboundApiIdDecodingConverter
	 *	@throws		permissionDenied
	 *	@throws		assetOwnershipNotFound, TEncodedId
	 *	@throws		metaCoverNotFound, TEncodedId
	**/
	public function handleDownloadEbookMetaCover($rawRequestData)
	{
		$dataForger = new StructForger(null, IPropertyForger::required, null, [
			new ValueForger('assetOwnershipId', IPropertyForger::required, null, $this->inboundApiIdDecodingConverter),
		]);
		
		try {
		$requestData = $dataForger->forge($rawRequestData);
		} catch (IError $e) { $e->rethrow(IPropertyForger::propertiesError); }
		
		try {
		$cover = $this->ebookAssetService->downloadEbookMetaCover($requestData['assetOwnershipId']);
		} catch (IError $e) { $e->rethrow([IPermissionService::permissionDenied, 'assetOwnershipNotFound', 'metaCoverNotFound']); }
		
		return $this->inboundApiBinaryEncodingConverter->convert($cover);
	}
	
	/**
	 *	@param		(file:binary|null, downloadUrl:string|null, email:string, PUBLISHER?????)		$rawRequestData
	 *	@param		string			$requestIdentifier
	 *	@return		TEncodedId		assetOwnership id
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *				@reason		propertyIsInvalid, (propertyName:string, path:array<string>, value:*)
	 *							@reason		tooLong, string
	 *							@reason		tooShort, string
	 *							@reason		emailInvalid, string
	 *	@throws		permissionDenied
	**/
	public function handleRegisterNewEbookFile($rawRequestData, $requestIdentifier = null)
	{
		// @todo cannot be called by everyone, needs trusted merchant because of legality of file (should only be files originally owned by the merchant)
		
		$trimConverter = new TrimConverter();
		$urlValidatingConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new UrlValidator([UrlValidator::schemeRequired, UrlValidator::hostRequired, UrlValidator::pathRequired]))]);
		
		$dataForger = new StructForger(null, IPropertyForger::required, null, [
			
			/////////ISBN
			
			new ClosureDecisionForger(function($partialRequestData) use ($rawRequestData, $urlValidatingConverter)
			{
				if (isset($rawRequestData['downloadUrl']))
				{
					return new ValueForger('downloadUrl', IPropertyForger::required, null, $urlValidatingConverter);
				}
				else
				{
					return new ValueForger('file', IPropertyForger::required, null, new ValidatingConverter(new BinaryLengthValidator(20))); // @todo there's a maximum for POST variables
				}
			}),
			
			//PUBLISHER?????
			//PUBLISHER?????
			//PUBLISHER?????
		]);
		
		try {
		$requestData = $dataForger->forge($rawRequestData);
		} catch (IError $e) { $e->rethrow(IPropertyForger::propertiesError); }
		
		//- in ebookAssetService >
		//download/save epub
		//validate epub
		
		//try {
		//$assetOwnershipId = $this->ebookAssetService->registerNewEbookFile($requestData['file'], $requestData['downloadUrl'], $requestData['email'], $requestData['PUBLISHER???']);
		//$assetOwnershipId = $this->ebookAssetService->registerNewEbookFileDownload($requestData['downloadUrl'], $requestData['downloadUrl'], $requestData['email'], $requestData['PUBLISHER???']);
		//} catch (IError $e) { $e->rethrow('downloadLinkAlreadyExists'); }
		
		//IPermissionService::permissionDenied < newfile only allowed by trusted publishers
		
		if ($requestIdentifier !== null) $this->apiEntityService->assignApiResponse($this->apiContext->getCurrentApiId(), $this->apiContext->getCurrentApiVersion(), 'ebookAssetService', 'registerNewEbookFile', $requestIdentifier, $assetOwnershipId);
		
		return $this->encodeRegisterNewEbookFile($assetOwnershipId);
	}
	
	/**
	 *	@param		TId
	 *	@return		TEncodedId
	**/
	public function encodeRegisterNewEbookFile($assetOwnershipId)
	{
		return $this->inboundApiIdEncodingConverter->convert($assetOwnershipId);
	}
	
	/**
	 *	@param		(assetOwnershipId:TEncodedId|null, assetBorrowId:TEncodedId|null)		$rawRequestData
	 *	@return		TEncodedBinary		epub file
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *				@reason		propertyIsInvalid, (propertyName:string, path:array<string>, value:*)
	 *							@reason		notANumber, string
	 *							@reason		notAWholeNumber, string
	 *							@reason		tooLow, string
	 *	@throws		permissionDenied
	 *	@throws		assetOwnershipNotFound, TEncodedId
	 *	@throws		assetBorrowNotFound, TEncodedId
	 *	@throws		assetIsLent, TEncodedId
	 *	@throws		ebookNotFound, TEncodedId
	 *	@throws		ebookNotProcessed, TEncodedId
	**/
	public function handleDownloadEbook($rawRequestData)
	{
		$dataForger = new StructForger(null, IPropertyForger::required, null, [
			new ClosureDecisionForger(function($partialRequestData) use ($rawRequestData)
			{
				if (isset($rawRequestData['assetBorrowId']))
				{
					return new ValueForger('assetBorrowId', IPropertyForger::required, null, $this->inboundApiIdDecodingConverter);
				}
				else
				{
					return new ValueForger('assetOwnershipId', IPropertyForger::required, null, $this->inboundApiIdDecodingConverter);
				}
			}),
		]);
		
		try {
		$requestData = $dataForger->forge($rawRequestData);
		} catch (IError $e) { $e->rethrow(IPropertyForger::propertiesError); }
		
		$epub = null;
		if (isset($requestData['assetBorrowId']))
		{
			try {
			$epub = $this->ebookAssetService->obtainBorrowedEpub($requestData['assetBorrowId']);
			} catch (IError $e) { $e->rethrow([IPermissionService::permissionDenied, 'assetBorrowNotFound', 'assetIsLent', 'ebookNotFound', 'ebookNotProcessed']); }
		}
		else
		{
			try {
			$epub = $this->ebookAssetService->obtainOwnedEpub($requestData['assetOwnershipId']);
			} catch (IError $e) { $e->rethrow([IPermissionService::permissionDenied, 'assetOwnershipNotFound', 'assetIsLent', 'ebookNotFound', 'ebookNotProcessed']); }
		}
		
		return $this->inboundApiBinaryEncodingConverter->convert($epub);
	}
}