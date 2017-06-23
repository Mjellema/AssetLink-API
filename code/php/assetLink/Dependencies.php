<?php
namespace assetLink;

/**
 *	
**/
class Dependencies
{
	/**
	 *	@param		string		$configDir
	 *	@param		string		$configName
	 *	@param		string		$configModeFilePath
	 *	@return		IDiMap
	**/
	public static function get($configDir, $configName, $configModeFilePath)
	{
		$di = \bite\Dependencies::get($configDir, $configName, $configModeFilePath);
		
		// @temp
		$di->assignLoaderFunction('router', function() use ($di)
		{
			$router = new \bite\execution\routing\Router();
			
			//if (isset($di->config['forceHttps']) && $di->config['forceHttps']) $router->addPreRouteAction($di->init(new \bite\io\http\routing\ForceHttpsPreRouteAction()));
			//$router->addPreRouteAction($di->init(new \bite\io\http\routing\UrlDomainPreRouteAction((isset($di->config['ignoreForceHttps']) && $di->config['ignoreForceHttps']))));
			//if ($di->config['useSeoUrls']) $router->addPreRouteAction($di->init(new \bite\io\http\routing\UrlRewriterPreRouteAction()));
			//$router->addPreRouteAction($di->init(new \bite\io\http\routing\SetLanguagePreRouteAction()));
			
			return $router;
		});
		$di->assignLoaderFunction('contentLoader', function() use ($di)
		{
			$contentLoader = new \bite\content\ContentLoader($di->config['dataDirectory']);
			//$contentLoader->assignDynamicPart('languageCode', $di->languageService->obtainLanguageCodeByInternationalCodeKey($di->languageContext->getCurrentLanguageId(), 'iso639-2'));
			
			return $contentLoader;
		});
		
		
		
		$di->assignLoaderFunction('inboundApis', function() use ($di) { return $di->contentLoader->load('assetLink/inboundApis'); });
		
		$di->assignLoaderFunction('assetStore', function() use ($di) { return new \bite\io\file\s3\S3FileSystem($di->config['s3.accessKey'], $di->config['s3.secretKey']); });
		
		$di->assignLoaderFunction('assetService', function() { return new \assetLink\asset\AssetDatabaseService(); });
		$di->assignLoaderFunction('ebookAssetService', function() { return new \assetLink\reading\book\asset\EbookAssetDatabaseService(); });
		$di->assignLoaderFunction('epubWatermarkService', function() { return new \assetLink\reading\book\EpubWatermarkService(); });
		
		return $di;
	}
}