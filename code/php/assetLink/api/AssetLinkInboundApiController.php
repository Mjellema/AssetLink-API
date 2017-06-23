<?php
namespace assetLink\api;

use bite\di\DiTarget;
use bite\error\EError;
use bite\error\IError;
use bite\api\inbound\GenericInboundApiController;

/**
 *	@dependency request
 *	@dependency config
 *	@dependency inboundApis
**/
class AssetLinkInboundApiController extends GenericInboundApiController
{
	/**
	 *	@temp for development
	 *	
	 *	@param		void
	 *	@return		(*)|null
	**/
	protected function getMockRequestData()
	{
		return null;
		
		
		$request = [
			'responseFormat'	=> 'json',
			//'responseFormat'	=> 'binary',
			'api'				=> 'assetLink',
			'version'			=> '0.1',
			
			//'service'			=> 'apiService',
			//'action'			=> 'obtainApiResponse',
			
			//'service'			=> 'ebookAssetService',
			//'action'			=> 'registerEbookDownloadLink',
			//'action'			=> 'obtainEbookDownloadLinkStatus',
			//'action'			=> 'downloadEbookMetaCover',
			//'action'			=> 'downloadEbook',
			
			//'service'			=> 'assetService',
			//'action'			=> 'transferAssetOwnership',
			//'action'			=> 'generateManagePermissionPasstoken',
			//'action'			=> 'grantManagePermissionUsingPasstoken',
			
			'loginName'			=> 'loginName',
			'password'			=> 'password',
			
			//'passtoken'			=> 'xxxxxxxxxx:yyyyyyyyyy',
			
			//userauthenticationtoken??
			
			'requestIdentifier'	=> '123',
			
			// contextual input for a lot of service actions (e.g. obtainContentItem() < languageId isn't a direct function argument, but is used to return the contentItem in the currently chosen language)
			'context' => [
				//'languageId'	=> '',
				//'systemId' < not allowed, at least not in the way that makes this the current logged in system id
			],
			
			'data' => [
				'requestIdentifier'	=> '123',
				
				'downloadLink'	=> 'http://merchant.tld/?download=4.epub',
				'newOwner'	=> [
					'email'			=> 'info@merchant.tld',
				],
				'previousOwner'	=> [
					'email'			=> 'john@doe.tld',
					'personDetails'	=> ['firstName' => 'John', 'lastName' => 'Doe'],
				],
				/*
				*/
				
				'assetOwnershipId' => hex2bin('EB5F117C27744C75B9A900B53FF24D38'),
				//'assetOwnershipId' => hex2bin('f1b351adfa094127adc4d1fcfa9a68cf'),
				
				//'passtoken' => '2fee58289fe1489c93eedc5d6b8e8669:095f6113556d45c0bdeed21ec59c81dd',
				
				
				/*
				'newOwner' => [
					'email' => 'jane@doe.tld',
					'personDetails' => [
						'firstName' => 'Jane',
						'lastName' => 'Doe',
					],
				],
				*/
			],
			
		];
		
		//$request['signature'] = hash_hmac('sha1', http_build_query($request), 'shared');
		$request['signature'] = hash_hmac('sha1', '', 'shared'); // @note mock http request body is empty
		
		// @todo time < expiration & nonce
		
		return $request;
	}
	
	/**
	 *	@param		void
	 *	@return		boolean
	**/
	protected function hasUnauthenticatedAccess()
	{
		// don't allow unauthenticated access to this API
		return false;
	}
}