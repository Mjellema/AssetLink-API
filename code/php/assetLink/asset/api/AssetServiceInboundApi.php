<?php
namespace assetLink\asset\api;

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
use bite\data\validation\ValueInListValidator;
use bite\di\DiTarget;
use bite\error\EError;
use bite\error\IError;
use bite\execution\action\ClosureAction;
use bite\io\stream\codec\Base64EncodingReader;
use bite\security\permission\IPermissionService;

/**
 *	@todo maybe rename to assetOwnershipServiceInboundApi/assetOwnershipService?
 *	
 *	@dependency apiEntityService
 *	@dependency apiContext
 *	@dependency config
 *	@dependency inboundApiIdDecodingConverter
 *	@dependency inboundApiIdEncodingConverter
**/
class AssetServiceInboundApi extends AbstractInboundApi
{
	/**
	 *	@param		string		$action
	 *	@return		boolean
	**/
	public function hasAction($action)
	{
		return in_array($action, ['provideNewPreviousOwner', 'transferAssetOwnership', 'generateManagePermissionPasstoken', 'grantManagePermissionUsingPasstoken']);
	}
	
	/**
	 *	@param		(assetOwnershipId:TEncodedId, previousAcquireDate:string|null, previousOwner:(email:string, personDetails:(firstName:string, lastName:string)|null, companyDetails:(name:string)|null))		$rawRequestData
	 *	@return		TEncodedId		previous assetOwnership id
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *				@reason		propertyIsInvalid, (propertyName:string, path:array<string>, value:*)
	 *							@reason		tooLong, string
	 *							@reason		tooShort, string
	 *							@reason		emailInvalid, string
	 *							@reason		dateInvalid, string
	 *	@throws		permissionDenied
	 *	@throws		assetOwnershipNotFound, TEncodedId
	 *	@throws		assetOwnershipAlreadyHasPreviousOwner, TEncodedId
	**/
	public function handleProvideNewPreviousOwner($rawRequestData)
	{
		$trimConverter = new TrimConverter();
		$textConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new TextLengthValidator(1, $this->config['maxTextFieldLenth']))]);
		$emailValidatingConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new EmailValidator())]);
		
		// @todo separate previousOwner forger (same as above)
		$dataForger = new StructForger(null, IPropertyForger::required, null, [
			new ValueForger('assetOwnershipId', IPropertyForger::required, null, $this->inboundApiIdDecodingConverter),
			new ValueForger('previousAcquireDate', IPropertyForger::optional, null, new TextToDateConverter()),
			new StructForger('previousOwner', IPropertyForger::required, null, [
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
		
		try {
		$previousAssetOwnershipId = $this->assetService->provideNewPreviousOwner($requestData['assetOwnershipId'], $requestData['previousAcquireDate'], $requestData['previousOwner']);
		} catch (IError $e) { $e->rethrow([IPermissionService::permissionDenied, 'assetOwnershipNotFound', 'assetOwnershipAlreadyHasPreviousOwner', 'permissionDenied?']); }
		
		return $this->inboundApiIdEncodingConverter->convert($previousAssetOwnershipId);
	}
	
	
	
	
	
	//provideAdditionalOwnerInfo(assetOwnershipId, (firstName: 'John', lastName: 'Doe'))
	//provideAdditionalPreviousOwnerInfo(assetOwnershipId, (firstName: 'Jane', lastName: 'Doe'))
	
	
	
	
	
	
	/**
	 *	@param		(assetOwnershipId:TEncodedId, newOwner:(email:string, personDetails:(firstName:string, lastName:string)|null, companyDetails:(name:string)|null))		$rawRequestData
	 *	@return		TEncodedId		new assetOwnership id
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *				@reason		propertyIsInvalid, (propertyName:string, path:array<string>, value:*)
	 *							@reason		tooLong, string
	 *							@reason		tooShort, string
	 *							@reason		emailInvalid, string
	 *							@reason		valueNotInList, string
	 *	@throws		permissionDenied
	 *	@throws		assetOwnershipNotFound, TEncodedId
	 *	@throws		assetOwnershipAlreadyTransfered, TEncodedId
	**/
	public function handleTransferAssetOwnership($rawRequestData)
	{
		$trimConverter = new TrimConverter();
		$textConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new TextLengthValidator(1, $this->config['maxTextFieldLenth']))]);
		$emailValidatingConverter = new ConverterChain([$trimConverter, new ValidatingConverter(new EmailValidator())]);
		
		$dataForger = new StructForger(null, IPropertyForger::required, null, [
			new ValueForger('assetOwnershipId', IPropertyForger::required, null, $this->inboundApiIdDecodingConverter),
			new StructForger('newOwner', IPropertyForger::required, null, [
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
		
		try {
		$assetOwnershipId = $this->assetService->transferAssetOwnership($requestData['assetOwnershipId'], $requestData['newOwner']);
		} catch (IError $e) { $e->rethrow([IPermissionService::permissionDenied, 'assetOwnershipNotFound', 'assetOwnershipAlreadyTransfered']); }
		
		return $this->inboundApiIdEncodingConverter->convert($assetOwnershipId);
	}
	
	/**
	 *	@param		(assetOwnershipId:TEncodedId)		$rawRequestData
	 *	@return		string		passtoken
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *				@reason		propertyIsInvalid, (propertyName:string, path:array<string>, value:*)
	 *	@throws		permissionDenied
	 *	@throws		assetOwnershipNotFound, TEncodedId
	 *	@throws		assetOwnershipAlreadyTransfered, TEncodedId
	**/
	public function handleGenerateManagePermissionPasstoken($rawRequestData)
	{
		$dataForger = new StructForger(null, IPropertyForger::required, null, [
			new ValueForger('assetOwnershipId', IPropertyForger::required, null, $this->inboundApiIdDecodingConverter),
		]);
		
		try {
		$requestData = $dataForger->forge($rawRequestData);
		} catch (IError $e) { $e->rethrow(IPropertyForger::propertiesError); }
		
		try {
		return $this->assetService->generateManagePermissionPasstoken($requestData['assetOwnershipId']);
		} catch (IError $e) { $e->rethrow([IPermissionService::permissionDenied, 'assetOwnershipNotFound']); }
	}
	
	/**
	 *	@param		(passtoken:string)		$rawRequestData
	 *	@return		TEncodedId		assetOwnership id
	 *	@throws		propertiesError
	 *				@reason		propertyIsRequired, (propertyName:string, path:array<string>)
	 *	@throws		passtokenFormatInvalid, string
	 *	@throws		timeout, (loginName:string, timeout:DateInterval)
	 *	@throws		passtokenInvalid, (passtoken:string, timeout:DateInterval)
	 *	@throws		passtokenTypeInvalid, string
	**/
	public function handleGrantManagePermissionUsingPasstoken($rawRequestData)
	{
		$dataForger = new StructForger(null, IPropertyForger::required, null, [
			new ValueForger('passtoken', IPropertyForger::required, null),
		]);
		
		try {
		$requestData = $dataForger->forge($rawRequestData);
		} catch (IError $e) { $e->rethrow(IPropertyForger::propertiesError); }
		
		try {
		$assetOwnershipId = $this->assetService->grantManagePermissionUsingPasstoken($requestData['passtoken']);
		} catch (IError $e) { $e->rethrow([IPermissionService::permissionDenied, 'passtokenFormatInvalid', 'timeout', 'passtokenInvalid', 'passtokenTypeInvalid']); }
		
		return $this->inboundApiIdEncodingConverter->convert($assetOwnershipId);
	}
}