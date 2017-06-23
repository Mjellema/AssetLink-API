<?php
namespace assetLink\reading\book\asset;

use Exception;
use Throwable;
use ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use bite\data\conversion\SlugConverter;
use bite\data\generating\UuidUtil;
use bite\di\DiTarget;
use bite\error\EError;
use bite\error\IError;
use bite\io\file\FileSystemUtil;
use bite\io\file\s3\S3FileSystem; // @temp
use bite\io\stream\StreamResourceReader;

/**
 *	@depdendency db
 *	@depdendency lockService
**/
class EbookAssetDatabaseServiceHelper extends DiTarget
{
	const MAX_REDIRECT_COUNT = 10;
	
	/**
	 *	@param		string		$url
	 *	@return		(url:string, type:string, uniqueurlparts:string, isbn:string)
	 *	@throws		urlInvalid, string
	 *	@throws		downloadLinkUrlInvalid, string
	**/
	public function getDownloadLinkUrlParts($url)
	{
		$downloadLinkUrlParts = [
			'url' => $url,
		];
		
		$parsedUrl = @parse_url($url);
		if ($parsedUrl === false) throw new EError('urlInvalid', $url);
		
		if (empty($parsedUrl['host'])) throw new EError('downloadLinkInvalid', $url);
		if (empty($parsedUrl['path'])) throw new EError('downloadLinkInvalid', $url);
		if (!isset($parsedUrl['query'])) $parsedUrl['query'] = '';
		
		parse_str($parsedUrl['query'], $queryResult);
		// parse_str quotes the values if magic_quotes is on
		//if (get_magic_quotes_gpc() === 1) $queryResult = $this->stripSlashes($queryResult);
		// parse_str automatically urldecodes everything in the query to make the array with urldecoded values
		
		
		/*
		http://www.boeken.com/?action=DownloadEbook&id=3kCqVfJZZK-3064
		> https://www.boeken.com/files/ebooks/downloads/ROBB201312.epub
		*/
		
		
		if ($parsedUrl['host'] === 'ep1.eboekhuis.nl' || $parsedUrl['host'] === 'ep2.eboekhuis.nl')
		{
			// @example: http://ep2.eboekhuis.nl/wmDwld.php?uid=551aebcb64a82&ean=9780000000000&r=1
			
			if ($parsedUrl['path'] !== '/wmDwld.php') throw new EError('downloadLinkInvalid', $url);
			if (empty($queryResult['uid'])) throw new EError('downloadLinkInvalid', $url);
			if (empty($queryResult['ean'])) throw new EError('downloadLinkInvalid', $url);
			
			$downloadLinkUrlParts['type']			= 'eBoekhuis';
			$downloadLinkUrlParts['uniqueurlparts']	= 'uid=' . rawurlencode($queryResult['uid']) . '&isbn=' . rawurlencode($queryResult['ean']);
			$downloadLinkUrlParts['isbn']			= $queryResult['ean'];
		}
		else if ($parsedUrl['host'] === 'watermark.epagine.nl')
		{
			// @example: http://watermark.epagine.nl/wmDwld.php?uid=4fbe384c77b49&ean=9780000000000
			
			if ($parsedUrl['path'] !== '/wmDwld.php') throw new EError('downloadLinkInvalid', $url);
			if (empty($queryResult['uid'])) throw new EError('downloadLinkInvalid', $url);
			if (empty($queryResult['ean'])) throw new EError('downloadLinkInvalid', $url);
			
			$downloadLinkUrlParts['type']			= 'epagine';
			$downloadLinkUrlParts['uniqueurlparts']	= 'uid=' . rawurlencode($queryResult['uid']) . '&isbn=' . rawurlencode($queryResult['ean']);
			$downloadLinkUrlParts['isbn']			= $queryResult['ean'];
		}
		else if ($parsedUrl['host'] === 'leesid.nu')
		{
			// @example: https://leesid.nu/ebooks/23cbba245704326d3fdd19175c19b4_9780000000000.epub
			
			if (preg_match('(^/ebooks/([a-z0-9]+)_([0-9]+)\\.epub$)', $parsedUrl['path'], $match) !== 1) throw new EError('downloadLinkInvalid', $url);
			
			$downloadLinkUrlParts['type']			= 'leesId';
			$downloadLinkUrlParts['uniqueurlparts']	= $match[1] . '_' . $match[2];
			$downloadLinkUrlParts['isbn']			= $match[2];
		}
		else if ($parsedUrl['host'] === 'storedownloads.kobo.com')
		{
			// @example: http://storedownloads.kobo.com/download?downloadToken=NST1C2zp0pTb1QWMmknDzCWNaaHj%2BugstvCLIn%2B2CzoJxgINZwnJtjPuFrAj2EvURJavyBTMDfqi1r7K0Cm%2BkYjyvad3cO8n1o%2BbKyNamqF3gr2lYJSOL2tEpG7yQGKlZiUlBhOK36ZM2q%2BxSXD0c9nNAKrSTgwhPHWIZZpHn2JEaNgp%2F91UgeqMrvfrZhTf08jbFHg6%2Fb0aDreqbP0A3tta3U1kDRX%2Fqv1F6AOmyfvLEZQxC303Zzker98oSOMGzxph22%2B5mzckdnA1R9TjjnWAkFsncRIpwKXwppRtnhc4cvm452%2BxnWZ1TuDaB931dmB7TQ%2BHIhSxUiUTasFHbg%3D%3D
			
			if ($parsedUrl['path'] !== '/download') throw new EError('downloadLinkInvalid', $url);
			if (empty($queryResult['downloadToken'])) throw new EError('downloadLinkInvalid', $url);
			
			// @note is downloadToken is not always the same for the same book / purchase, so need to check the contentHash later!
			$downloadLinkUrlParts['type']			= 'kobo';
			$downloadLinkUrlParts['uniqueurlparts']	= rawurlencode($queryResult['downloadToken']);
			$downloadLinkUrlParts['isbn']			= '';
		}
		else if ($parsedUrl['host'] === 'e.boekhuis.nl')
		{
			// @example: http://e.boekhuis.nl/books/9780000000000_1350396000783.epub
			
			if (preg_match('(^/books/([0-9]+)_([a-z0-9]+)\\.epub$)', $parsedUrl['path'], $match) === 1)
			{
				$downloadLinkUrlParts['type']			= 'boekhuis';
				$downloadLinkUrlParts['uniqueurlparts']	= $match[1] . '_' . $match[2];
				$downloadLinkUrlParts['isbn']			= $match[1];
			}
			else if (preg_match('(^/books/([a-z0-9-]+)\\.epub$)', $parsedUrl['path'], $match) === 1)
			{
				$downloadLinkUrlParts['type']			= 'boekhuis';
				$downloadLinkUrlParts['uniqueurlparts']	= $match[1];
				$downloadLinkUrlParts['isbn']			= '';
			}
			else
			{
				throw new EError('downloadLinkInvalid', $url);
			}
		}
		else if ($parsedUrl['host'] === 'ecimages.shortcovers.com')
		{
			// @example: http://ecimages.shortcovers.com/36722bb7-13b0-4b83-965b-9cb5f9a0d51e.epub
			
			if (preg_match('(^/([a-z0-9-]+)\\.epub$)', $parsedUrl['path'], $match) !== 1) throw new EError('downloadLinkInvalid', $url);
			
			$downloadLinkUrlParts['type']			= 'shortcovers';
			$downloadLinkUrlParts['uniqueurlparts']	= $match[1];
			$downloadLinkUrlParts['isbn']			= '';
		}
		else if ($parsedUrl['host'] === 'acs.tagusbooks.com')
		{
			// @example: http://acs.tagusbooks.com/ebooks/36722bb7-13b0-4b83-965b-9cb5f9a0d51e.epub
			
			if (preg_match('(^/ebooks/([a-z0-9-]+)\\.epub$)', $parsedUrl['path'], $match) !== 1) throw new EError('downloadLinkInvalid', $url);
			
			$downloadLinkUrlParts['type']			= 'tagusbooks';
			$downloadLinkUrlParts['uniqueurlparts']	= $match[1];
			$downloadLinkUrlParts['isbn']			= '';
		}
		else if ($parsedUrl['host'] === 'fulfillment.eboekhuis.nl')
		{
			// @example: http://fulfillment.eboekhuis.nl/CH/Fulfillment.aspx?downloadToken=e9Op67j1PUVTEmg%2bTyXtR%2bSISUauqrqBP9U3rd5qJ6ffyfIDaKkgGoWJaHzykVmhM2o69CfmaXTKG2gcbc2XL%2bsFiG3HqwzZ7ayprq2tPpcG8JDl9kGHWiRi4EfehCAW2%2bYfSGTi2Mo46T7nZ351x0qFVVjSTzj3%2flumJRggmuFbxC3c07ndj%2f3MxqDr%2fEhtIcuWctBqJuY%2b25XDzOx3%2bNiqN9wBHWy1Sn5GkvEqPBziJj%2fjp%2fTU4%2fmAplP06vWJNyGcAhbyu8zTrSrRIf6hfMQmkzOgjOmlBWIWp6fqVQHx%2bu4E7BcLbrPtsWnyf3XD6j24E6pS%2ftgyTyxxxdXvOw%3d%3d
			
			// @note fulfillment.eboekhuis.nl links sometimes redirects to http://e.boekhuis.nl/fulfillment/URLLink.acsm?etc
			// but since we check the download urls only after following all redirects, this should be fine
			
			if ($parsedUrl['path'] !== '/CH/Fulfillment.aspx') throw new EError('downloadLinkInvalid', $url);
			if (empty($queryResult['downloadToken'])) throw new EError('downloadLinkInvalid', $url);
			
			// @note is downloadToken is not always the same for the same book / purchase, so need to check the contentHash later!
			$downloadLinkUrlParts['type']			= 'eBoekhuisFulfillment';
			$downloadLinkUrlParts['uniqueurlparts']	= rawurlencode($queryResult['downloadToken']);
			$downloadLinkUrlParts['isbn']			= '';
		}
		else if ($parsedUrl['host'] === 'boenda.nl')
		{
			// @example: http://boenda.nl/boenda/file_download.php?f=d2ViLXNlcnZpY2UvY2JfZG93bmxvYWQvNDQ4OTc4MDAwMDAwMDAwMC5lcHVi
			
			if ($parsedUrl['path'] !== '/boenda/file_download.php') throw new EError('downloadLinkInvalid', $url);
			if (empty($queryResult['f'])) throw new EError('downloadLinkInvalid', $url);
			
			// @note we need to decode the data, because boenda accepts different inputs for the same download URL, so we need to get the single unique property
			$filePath = base64_decode($queryResult['f']);
			// @example decoded 'f': web-service/cb_download/4489780000000000.epub
			if (preg_match('(^' . preg_quote('web-service/cb_download/') . '([0-9]+)\\.epub$)', $filePath, $match) !== 1) throw new EError('downloadLinkInvalid', $url);
			
			$downloadLinkUrlParts['type']			= 'boenda';
			$downloadLinkUrlParts['uniqueurlparts']	= $match[1];
			$downloadLinkUrlParts['isbn']			= $this->findIsbnOrEmptyString($match[1]);
		}
		else if ($parsedUrl['host'] === 'cb.libreprint.com')
		{
			// @example: https://cb.libreprint.com/Download/?id=67e1f418-eff9-48b3-8472-cd9025ef448b
			
			if ($parsedUrl['path'] !== '/Download/') throw new EError('downloadLinkInvalid', $url);
			if (empty($queryResult['id'])) throw new EError('downloadLinkInvalid', $url);
				
			// @note this is the new download URL that eBoekhuis uses
			$downloadLinkUrlParts['type']			= 'librePrint';
			$downloadLinkUrlParts['uniqueurlparts']	= rawurlencode($queryResult['id']);
			$downloadLinkUrlParts['isbn']			= '';
			
			// @note this is the old download url of libreprint
			// @example: https://cb.libreprint.com/Download/GetDownloadBook?bookID=37789f1b-d98d-42b6-b355-eaf4703305fc&bookType=epub&bookName=9780000000000&styling=LibrePrint.css&image=/Images/neutral_logo.png
			//if ($parsedUrl['path'] !== '/Download/GetDownloadBook') throw new EError('downloadLinkInvalid', $url);
			//if (empty($queryResult['bookID'])) throw new EError('downloadLinkInvalid', $url);
			//
			//// @note 'bookName' ISBN variable is not checked by libreprint, so it will download a valid epub with any ISBN
			//$downloadLinkUrlParts['type']			= 'librePrint';
			//$downloadLinkUrlParts['uniqueurlparts']	= rawurlencode($queryResult['bookID']);
			//$downloadLinkUrlParts['isbn']			= empty($queryResult['bookName']) ? '' : $this->findIsbnOrEmptyString($queryResult['bookName']);
		}
		else
		{
			throw new EError('downloadLinkInvalid', $url);
		}
		
		return $downloadLinkUrlParts;
	}
	
	/**
	 *	@param		string		$url
	 *	@return		string
	 *	@throws		urlInvalid, string
	 *	@throws		rewriteUrlInvalid, string
	**/
	public function rewriteUrl($url)
	{
		$parsedUrl = @parse_url($url);
		if ($parsedUrl === false) throw new EError('urlInvalid', $url);
		
		if (empty($parsedUrl['host'])) throw new EError('rewriteUrlInvalid', $url);
		if (empty($parsedUrl['path'])) throw new EError('rewriteUrlInvalid', $url);
		if (!isset($parsedUrl['query'])) $parsedUrl['query'] = '';
		
		parse_str($parsedUrl['query'], $queryResult);
		// parse_str quotes the values if magic_quotes is on
		//if (1 === get_magic_quotes_gpc()) $queryResult = $this->stripSlashes($queryResult);
		// parse_str automatically urldecodes everything in the query to make the array with urldecoded values
		
		if ($parsedUrl['host'] === 'm.bol.com')
		{
			if ($parsedUrl['path'] !== '/nl/ebook.epub') throw new EError('rewriteUrlInvalid', $url);
			if (empty($queryResult['url'])) throw new EError('rewriteUrlInvalid', $url);
			
			$url = $queryResult['url'];
		}
		
		return $url;
	}
	
	/**
	 *	@param		string		$url
	 *	@return		string
	 *	@throws		urlInvalid, string
	 *	@throws		intermediaryUrlInvalid, string
	**/
	public function obtainIsbnFromIntermediaryUrl($url)
	{
		$isbn = null;
		
		$parsedUrl = @parse_url($url);
		if ($parsedUrl === false) throw new EError('urlInvalid', $url);
		
		if (empty($parsedUrl['host'])) throw new EError('intermediaryUrlInvalid', $url);
		if (empty($parsedUrl['path'])) throw new EError('intermediaryUrlInvalid', $url);
		if (!isset($parsedUrl['query'])) $parsedUrl['query'] = '';
		
		parse_str($parsedUrl['query'], $queryResult);
		// parse_str quotes the values if magic_quotes is on
		//if (1 === get_magic_quotes_gpc()) $queryResult = $this->stripSlashes($queryResult);
		// parse_str automatically urldecodes everything in the query to make the array with urldecoded values
		
		
		if ($parsedUrl['host'] === 'www.ebook.nl')
		{
			if ($parsedUrl['path'] !== '/store/epub_download.php') throw new EError('intermediaryUrlInvalid', $url);
			if (empty($queryResult['BookID'])) throw new EError('intermediaryUrlInvalid', $url);
			
			$isbn = $queryResult['BookID'];
		}
		
		if ($isbn === null) throw new EError('intermediaryUrlInvalid', $url);
		
		return $isbn;
	}
	
	/**
	 *	@param		string			$url
	 *	@param		resource		$fp
	 *	@return		(redirect:string, isbn:string, drmToken:string)
	 *	@throws		urlInvalid, string
	 *	@throws		endpointUrlInvalid, string
	**/
	protected function getEndpointContentData($url, $fp)
	{
		$data = [
			'redirect'	=> '',
			'isbn'		=> '',
			'drmToken'	=> '',
		];
		
		$parsedUrl = @parse_url($url);
		if ($parsedUrl === false) throw new EError('urlInvalid', $url);
		
		if (empty($parsedUrl['host'])) throw new EError('endpointUrlInvalid', $url);
		if (empty($parsedUrl['path'])) throw new EError('endpointUrlInvalid', $url);
		if (!isset($parsedUrl['query'])) $parsedUrl['query'] = '';
		
		parse_str($parsedUrl['query'], $queryResult);
		// parse_str quotes the values if magic_quotes is on
		//if (1 === get_magic_quotes_gpc()) $queryResult = $this->stripSlashes($queryResult);
		// parse_str automatically urldecodes everything in the query to make the array with urldecoded values
		
		
		$adobeDrmHostsToEpubSrcUrl = [
			'e.boekhuis.nl'			=> 'http://e.boekhuis.nl/books/',
			'acs4.shortcovers.com'	=> 'http://ecimages.shortcovers.com/',
			'acs4.epagine.nl'		=> 'http://ecimages.shortcovers.com/',
			'acs.tagusbooks.com'	=> 'http://acs.tagusbooks.com/ebooks/',
		];
		if (isset($adobeDrmHostsToEpubSrcUrl[$parsedUrl['host']]))
		{
			if ($parsedUrl['path'] !== '/fulfillment/URLLink.acsm') throw new EError('endpointUrlInvalid', $url);
			
			$content = stream_get_contents($fp);
			$xml = $this->parseXml($content);
			if ($xml !== false)
			{
				$data['drmToken'] = $content;
				// @example: http://e.boekhuis.nl/books/9789460912394_1288610100442.epub
				// @example: http://ecimages.shortcovers.com/1a83c790-738d-4f82-b58e-a978610d1a04.epub
				// @example: http://acs.tagusbooks.com/ebooks/34a13033-c66e-4c91-b456-a6da18ae2df2.epub
				
				if (isset($xml->resourceItemInfo->src))
				{
					$data['redirect'] = (string)$xml->resourceItemInfo->src; // @note could throw?
				}
				else
				{
					$epubSrcUrl = $adobeDrmHostsToEpubSrcUrl[$parsedUrl['host']];
					// @example: urn:uuid:34a13033-c66e-4c91-b456-a6da18ae2df2
					$uuid = (string)$xml->resourceItemInfo->resource; // @note could throw?
					$uuid = str_replace('urn:uuid:', '', $uuid);
					$data['redirect'] = $epubSrcUrl . $uuid . '.epub';
				}
				
				try
				{
					$data['isbn'] = $this->getIsbnFromMetaData($xml);
				}
				catch (IError $e)
				{
					$e->allow(['metaDataNotFound', 'isbnNotFound']);
				}
			}
		}
		else if ($parsedUrl['host'] === 'ebook.lightningsource.com')
		{
			if ($parsedUrl['path'] === '/eBookDownload/eBookOPDownload.dll')
			{
				/*
				function GetBook()
				{
					location.href='https://ebook.lightningsource.com/eBookDownload/eBookOPDownload.dll/EpigeneticsRevolutionHowModer.acsm?vId=076&MTA0IHYCv1zoTSkCbO2RZutpAM5irV+H3bbzLFwUo403Pj9JUDPAqb+yuChErroJH+SqW3EMVmothT7glt6E0NkDxZSDLD9XGLofdvvAFZ3Dy+81tpCFyzk72b0S5tu2rEO/irYJhmTYAa7+';
					// or
					// location.href='https://ebook.lightningsource.com/eBookDownload/eBookOPDownload.dll/Destiltevandeheldruk1.acsm?vId=086&MTA0IHYCv1zoTSkCbO2RZutpAM5irV+H3bbzLBXdq+10xMKiLgwwkg1K/N7z4cuLXcApW/mITQ9FIFHbnRg2jiNbm6m4qB74FGM9Xhmcm+v0YS7/ME/xWpJ/JvMx6nE648XBsjW/erQU8YDm';
					// name of the *.acsm file is the name of the book
				}
				*/
				
				$content = stream_get_contents($fp);
				if (preg_match('(' . preg_quote('https://ebook.lightningsource.com/eBookDownload/eBookOPDownload.dll/') . '[^\']+)', $content, $match) !== 1) throw new EError('endpointUrlInvalid', $url);
				
				$data['redirect'] = $match[0];
			}
			else if (preg_match('(' . preg_quote('/eBookDownload/eBookOPDownload.dll/') . '[^.]+\.acsm)', $parsedUrl['path'], $match) === 1)
			{
				/*
				has <src> element with download URL, but each call to the *.acsm yields a different download URL to the same file, so we can't uniquely identify it
				http://ebook.lightningsource.com/TitleDownload/LSCFD.dll?hdlr=ADEPT&amp;oid=MTIwIOXsg1Sgv4MAYM/UwT+6zfcN+nU4ssd/xNwLSe+jvGGK59uRN3flILcGUxL8nrr6EA1pL+N2ITbptnbBZ3h8hre82jI+BIrshxZlGCUqKkk3BzYktaAayYHUJQt0jto0NdL6m3/mcoLqKQObkfE+mf0GaDdEKFmwyQ==
				http://ebook.lightningsource.com/TitleDownload/LSCFD.dll?hdlr=ADEPT&amp;oid=MTIwIEB2PJ8bTFpbfpCVDMFrf680Uk1hOghaZRpyy5jnIQyfpRFLYSyXRPLSzxVSRscNfh+azXkg4CykT3vK2gkEFtEoYUSR+TF0ydyjdlfsJnrqb45uQd2USB2cHsP9O25P8LmONoPW/K4iWySbmoEpYpz8jd/rqTaxsQ==
				*/
				
				$content = stream_get_contents($fp);
				$xml = $this->parseXml($content);
				if ($xml !== false)
				{
					if (isset($xml->resourceItemInfo->src))
					{
						$data['redirect'] = (string)$xml->resourceItemInfo->src; // @note could throw?
					}
					
					try
					{
						$data['isbn'] = $this->getIsbnFromMetaData($xml);
					}
					catch (IError $e)
					{
						$e->allow(['metaDataNotFound', 'isbnNotFound']);
					}
				}
			}
			else
			{
				throw new EError('endpointUrlInvalid', $url);
			}
		}
		else if ($parsedUrl['host'] === 'www.eci.nl')
		{
			if (strpos($parsedUrl['path'], '/INTERSHOP/web/WFS/shop-eci_nl-Site/nl_NL/-/EUR/ViewEBooks-Download') === 0)
			{
				if (empty($queryResult['CurrentEBookDownloadURL'])) throw new EError('endpointUrlInvalid', $url);
				
				$data['redirect'] = $queryResult['CurrentEBookDownloadURL'];
			}
			else if ($parsedUrl['path'] === '/INTERSHOP/web/WFS/shop-eci_nl-Site/nl_NL/-/EUR/ViewEBooks-ShowDownload')
			{
				$content = stream_get_contents($fp);
				
				if (preg_match('(' . preg_quote('http://www.eci.nl/INTERSHOP/web/WFS/shop-eci_nl-Site/nl_NL/-/EUR/ViewEBooks-Download') . '[^?]*' . preg_quote('?CurrentEBookDownloadURL=') . '([^"]+))', $content, $match) !== 1) throw new EError('endpointUrlInvalid', $url);
				
				$data['redirect'] = rawurldecode($match[1]);
			}
			else
			{
				throw new EError('endpointUrlInvalid', $url);
			}
		}
		//@note libreprint is modified and now returns the epub immediately from the https://cb.libreprint.com/Download/?id=696979a4-0e6f-4395-9d7b-f645da19d0e1 link
		//instead of showing a download page
		//
		//else if ($parsedUrl['host'] === 'cb.libreprint.com')
		//{
		//	if ($parsedUrl['path'] !== '/Download/') throw new EError('endpointUrlInvalid', $url);
		//	
		//	/*
		//	function Download() {
		//		var protocol = window.location.protocol;
		//		var host = window.location.host;
		//		var url = protocol + "//" + host + "/Download/GetDownloadBook?bookID=37789f1b-d98d-42b6-b355-eaf4703305fa&bookType=epub&bookName=9780000000000&styling=LibrePrint.css&image=/Images/neutral_logo.png";
		//		$(location).attr("href", url);
		//	};
		//	*/
		//	
		//	$content = stream_get_contents($fp);
		//	if (preg_match('(' . preg_quote('/Download/GetDownloadBook?bookID=') . '[^"]+)', $content, $match) !== 1) //		throw new EError('endpointUrlInvalid', $url);
		//	
		//	$data['redirect'] = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $match[0];
		//}
		
		return $data;
	}
	
	/**
	 *	@param		string		$url
	 *	@param		(url:string, type:string, uniqueurlparts:string, isbn:string)|null		$downloadLinkUrlParts
	 *	@param		boolean		$excludeUnchecked
	 *	@param		TId|null	$excludeEbookDownloadLinkId
	 *	@return		boolean
	**/
	public function urlExists($url, $downloadLinkUrlParts = null, $excludeUnchecked = false, $excludeEbookDownloadLinkId = null)
	{
		// @note needs locking around this call if it's used to check and then insert
		
		$conditionsDsl = [
			[
				'edl.url' => $url,
				'OR',
				'edlv.eventualurl'	=> $url,
				// here possible OR for the downloadLinkUrlParts
			],
			// here possible AND for the excludeUnchecked
			// here possible AND for the excludeEbookDownloadLinkId
		];
		if ($downloadLinkUrlParts !== null)
		{
			$conditionsDsl[0][] = 'OR';
			$conditionsDsl[0][] = [
				'edlt.key'				=> $downloadLinkUrlParts['type'],
				'edl.d_uniqueurlparts'	=> $downloadLinkUrlParts['uniqueurlparts'],
			];
			$conditionsDsl[0][] = 'OR';
			$conditionsDsl[0][] = [
				'edlt.key'				=> $downloadLinkUrlParts['type'],
				'edlv.uniqueurlparts'	=> $downloadLinkUrlParts['uniqueurlparts'],
			];
		}
		if ($excludeUnchecked)
		{
			// only see if a checked url already exists, for when the same URL is already multiple times in the database
			// one of them should be processed, so we should ignore the unchecked ones
			$conditionsDsl[] = [
				'edl.ischecked' => 1,
			];
		}
		if ($excludeEbookDownloadLinkId !== null)
		{
			$conditionsDsl[] = [
				'edl.id' => $this->db->isNot($excludeEbookDownloadLinkId),
			];
		}
		
		$result = $this->db->select([
			'FROM', 'ebookdownloadlink', 'edl',
			'LEFT JOIN', 'ebookdownloadlinkvalidation', 'edlv', ['edlv.ebookdownloadlink_id' => 'edl.id'],
			'LEFT JOIN', 'ebookdownloadlinktype', 'edlt', ['edlt.id' => 'edl.ebookdownloadlinktype_id'],
			'WHERE', $conditionsDsl,
		]);
		return ($result->hasRows());
	}
	
	/**
	 *	@param		integer		$limit
	 *	@return		void
	**/
	public function validateEbookDownloadLinks($limit = 20)
	{
		$limit = (int)$limit;
		if ($limit < 1) throw new EError('limitInvalid', $limit);
		
		// @todo we don't actually need this lock if multiple servers want to process different downloadLinks
		// @note without soft lock the transaction of the lock spans all calls inside, so any locks created inside will block others until the end of this giant lock
		$this->lockService->softLockOrSkip('validateEbookDownloadLinks', 20, function() use ($limit)
		{
			$result = $this->db->select([
				'id',
				'checkcount',
				'url',
				'isbn',
				'FROM', 'ebookdownloadlink',
				'WHERE', [
					'ischecked' => 0,
				],
				'ORDER BY',
					'registrationdate', 'ASC',
				'LIMIT', $limit,
			]);
			foreach ($result as $row)
			{
				$this->lockService->extendLock('validateEbookDownloadLinks', 20);
				$this->validateEbookDownloadLink($row);
			}
		});
	}
	
	/**
	 *	@param		(?)		$row
	 *	@return		void
	**/
	protected function validateEbookDownloadLink($row)
	{
		// use explicit lock so we can skip instead of block
		$this->lockService->lockOrSkip('validateEbookDownloadLink.' . $row['id'], 20, function() use ($row)
		{
			$urls						= array();
			$eventualUrl				= '';
			$invalidReasonKey			= '';
			$downloadLinkUrlParts		= null;
			$intermediaryIsbn			= '';
			$drmToken					= '';
			
			$metaIsbns					= array();
			$metaAuthor					= '';
			$metaTitle					= '';
			$metaCoverStoredFilename	= '';
			
			$fileHash					= '';
			$unzippedFileHash			= '';
			$contentHash				= '';
			
			$isbn						= '';
			
			try
			{
				$ebookDownloadLinkData = $this->loadEbookDownloadLinkUrl($row['url']);
				$urls						= $ebookDownloadLinkData['urls'];
				$eventualUrl				= $ebookDownloadLinkData['eventualUrl'];
				$invalidReasonKey			= $ebookDownloadLinkData['invalidReasonKey'];
				$downloadLinkUrlParts		= $ebookDownloadLinkData['downloadLinkUrlParts'];
				$intermediaryIsbn			= $ebookDownloadLinkData['intermediaryIsbn'];
				$drmToken					= $ebookDownloadLinkData['drmToken'];
				$fp							= $ebookDownloadLinkData['fp'];
				
				if ($eventualUrl === $row['url']) $eventualUrl = '';
				
				if ($invalidReasonKey === '' && $downloadLinkUrlParts['isbn'] !== '')
				{
					$isbn = $downloadLinkUrlParts['isbn'];
				}
				else if ($intermediaryIsbn !== '')
				{
					// use isbn from $intermediaryIsbn
					$isbn = $intermediaryIsbn;
				}
				
				if ($isbn === '' && $row['isbn'] !== '')
				{
					// use isbn that was already found (or provided via the admin)
					$isbn = $row['isbn'];
				}
				
				if ($invalidReasonKey !== '')
				{
					throw new EError('ebookDownloadLinkInvalidReason', $invalidReasonKey);
				}
				
				/*
				$bookId	= null;
				if ('' !== $isbn)
				{
					$result = $this->db->select(['id', 'FROM', 'book', 'WHERE', ['isbn' => $isbn]]);
					if ($result->hasRows()) $bookId = $result->fetchField('id');
				}
				*/
				
				if ($invalidReasonKey === '')
				{
					// pre-check for duplicates of the eventual URL
					if ($this->urlExists($downloadLinkUrlParts['url'], $downloadLinkUrlParts, true))
					{
						throw new EError('ebookDownloadLinkInvalidReason', 'duplicateUrl');
					}
				}
				
				$tempFilePath = tempnam(sys_get_temp_dir(), 'sh_validate_epub_'); // Windows uses only the first three characters of prefix
				if ($tempFilePath === false) throw new EError('createTempFileFailed');
				
				$this->downloadEpub($fp, $tempFilePath);
				
				$fileHash = hash_file('sha512', $tempFilePath, true);
				
				/*
				// @todo check if an existing filehash for different customers is possible
				$result = $this->db->select(['FROM', 'ebookdownloadlink', 'WHERE', ['filehash' => $fileHash]]);
				if ($result->hasRows()) throw new EError('ebookDownloadLinkInvalidReason', 'duplicateBook');
				*/
				
				$tempDirPath = $this->extractEpub($tempFilePath);
				
				$hashData = $this->generateHashes($tempDirPath);
				$unzippedFileHash	= $hashData['unzippedFileHash'];
				$contentHash		= $hashData['contentHash'];
				
				$this->checkDrm($tempDirPath);
				
				if (!is_file($tempDirPath . 'META-INF/container.xml'))
				{
					throw new EError('ebookDownloadLinkInvalidReason', 'invalidEpub');
				}
				
				$metaData = $this->checkEpubFormatAndMetaData($tempDirPath);
				$metaIsbns					= $metaData['metaIsbns'];
				$metaAuthor					= $metaData['metaAuthor'];
				$metaTitle					= $metaData['metaTitle'];
				$metaCoverStoredFilename	= $metaData['metaCoverStoredFilename'];
				
				
				FileSystemUtil::deleteDir($tempDirPath);
				
				/*
				if ($bookId !== null && $row['customer_id'] !== null) $this->checkDuplicateBookTitleForCustomer($bookId, $row['customer_id']);
					$result = $this->db->select([
						'FROM', 'ebookdownloadlink',
						'WHERE', [
							'isvalid'		=> 1
							'customer_id'	=> $row['customer_id'],
							'book_id'		=> $bookId,
						],
					]);
					if ($result->hasRows()) throw new EError('ebookDownloadLinkInvalidReason', 'duplicateBook');
				*/
				
				// pre-check for duplicates of a kobo epub
				if ($downloadLinkUrlParts['type'] === 'kobo' && $this->koboEpubExists($unzippedFileHash))
				{
					throw new EError('ebookDownloadLinkInvalidReason', 'duplicateBook');
				}
			}
			catch (IError $e)
			{
				$e->allow('ebookDownloadLinkInvalidReason');
				
				$invalidReasonKey = $e->getInfo();
			}
			
			$originalStoredFilename	= '';
			$drmStoredFilename		= '';
			$isDrm					= ($invalidReasonKey === 'drm');
			if ($invalidReasonKey === '' || $isDrm)
			{
				try
				{
					$storedFilename = $this->storeEpub($tempFilePath, $isDrm, $isbn, $metaIsbns, $metaAuthor, $metaTitle);
				}
				catch (Throwable $e)
				{
					@unlink($tempFilePath);
					throw $e;
				}
				catch (Exception $e) // @deprecated for PHP 7
				{
					@unlink($tempFilePath);
					throw $e;
				}
				
				if ($isDrm) $drmStoredFilename = $storedFilename;
				else $originalStoredFilename = $storedFilename;
			}
			
			@unlink($tempFilePath);
			
			/*
			$metaIsbn = '';
			if (!empty($metaIsbns))
			{
				$metaIsbn = reset($metaIsbns);
				
				// find meta isbn that matches an e-book
				$result = $this->db->select([
					'isbn',
					'FROM', 'book',
					'WHERE', [
						'isbn' => $this->db->expression('IN (' . $this->db->placeHolders($metaIsbns) . ')', $metaIsbns),
					],
					'LIMIT', 1
				]);
				if ($result->hasRows())
				{
					// just use first result for now
					// @todo store all found meta isbns so you can pick one in the admin
					$metaIsbn = $result->fetchField('isbn');
				}
			}
			*/
			
			// @todo maybe clean up the passing of parameters belown
			
			if ($invalidReasonKey === '')
			{
				$this->lockService->lock('validateEbookDownloadLink.checkDuplicate', 5, 5, function()
					use (
						$row,
						
						$urls,
						$eventualUrl,
						$invalidReasonKey,
						$downloadLinkUrlParts,
						$intermediaryIsbn,
						$drmToken,
						
						$metaIsbns,
						$metaAuthor,
						$metaTitle,
						$metaCoverStoredFilename,
						
						$fileHash,
						$unzippedFileHash,
						$contentHash,
						
						$isbn,
						
						$originalStoredFilename,
						$drmStoredFilename
					)
				{
					// locked check (secure, avoids race conditions)
					
					if ($this->urlExists($downloadLinkUrlParts['url'], $downloadLinkUrlParts, true, $row['id']))
					{
						/////should also ignore the link currently being checked (which could have checked = 1)
						$invalidReasonKey = 'duplicateUrl';
					}
					
					if ($downloadLinkUrlParts['type'] === 'kobo' && $this->koboEpubExists($unzippedFileHash, $row['id']))
					{
						/////should also ignore the link currently being checked (which could have checked = 1)
						$invalidReasonKey = 'duplicateBook';
					}
					
					if ($invalidReasonKey !== '')
					{
						// @todo delete storedfilename
						$originalStoredFilename	= '';
						$drmStoredFilename		= '';
					}
					
					$this->saveValidation(
						$row,
						
						$urls,
						$eventualUrl,
						$invalidReasonKey,
						$downloadLinkUrlParts,
						$intermediaryIsbn,
						$drmToken,
						
						$metaIsbns,
						$metaAuthor,
						$metaTitle,
						$metaCoverStoredFilename,
						
						$fileHash,
						$unzippedFileHash,
						$contentHash,
						
						$isbn,
						
						$originalStoredFilename,
						$drmStoredFilename
					);
				});
			}
			else
			{
				$this->saveValidation(
					$row,
					
					$urls,
					$eventualUrl,
					$invalidReasonKey,
					$downloadLinkUrlParts,
					$intermediaryIsbn,
					$drmToken,
					
					$metaIsbns,
					$metaAuthor,
					$metaTitle,
					$metaCoverStoredFilename,
					
					$fileHash,
					$unzippedFileHash,
					$contentHash,
					
					$isbn,
					
					$originalStoredFilename,
					$drmStoredFilename
				);
			}
		});
	}
	
	/**
	 *	@param		?
	 *	@return		void
	**/
	protected function saveValidation(
		$row,
		
		$urls,
		$eventualUrl,
		$invalidReasonKey,
		$downloadLinkUrlParts,
		$intermediaryIsbn,
		$drmToken,
		
		$metaIsbns,
		$metaAuthor,
		$metaTitle,
		$metaCoverStoredFilename,
		
		$fileHash,
		$unzippedFileHash,
		$contentHash,
		
		$isbn,
		
		$originalStoredFilename,
		$drmStoredFilename
	)
	{
		$result = $this->db->select([
			'a.id',
			'FROM', 'asset', 'a',
			'INNER JOIN', 'assettype', 'at', ['at.id' => 'a.assettype_id'],
			'INNER JOIN', 'entityclass', 'ec', ['ec.id' => 'a.entityclass_id'],
			'WHERE', [
				'a.entity_id'	=> $row['id'],
				'at.key'		=> 'ebookDownloadLink',
				'ec.key'		=> 'ebookDownloadLink',
			],
		]);
		if ($result->isEmpty()) throw new EError('correspondingAssetNotFound', $row['id']);
		$assetId = $result->fetchField('id');
		
		$ebookDownloadLinkInvalidReasonId = null;
		if ($invalidReasonKey !== '')
		{
			$result = $this->db->select(['id', 'FROM', 'ebookdownloadlinkinvalidreason', 'WHERE', ['key' => $invalidReasonKey]]);
			if ($result->isEmpty()) throw new EError('ebookDownloadLinkInvalidReasonNotFound', $invalidReasonKey);
			$ebookDownloadLinkInvalidReasonId = $result->fetchField('id');
		}
		
		$ebookDownloadLinkTypeId = null;
		if ($downloadLinkUrlParts !== null)
		{
			$result = $this->db->select(['id', 'FROM', 'ebookdownloadlinktype', 'WHERE', ['key' => $downloadLinkUrlParts['type']]]);
			if ($result->isEmpty()) throw new EError('ebookDownloadLinkTypeNotFound', $downloadLinkUrlParts['type']);
			$ebookDownloadLinkTypeId = $result->fetchField('id');
		}
		
		$isValid = ($invalidReasonKey === '');
		// if the epub is valid or we already checked it 3 times or the $invalidReasonKey is NOT some reason to try again, mark as checked
		$isChecked = $isValid || ($row['checkcount'] + 1 >= 3) || ('tooManyRedirects' !== $invalidReasonKey && 'urlFailed' !== $invalidReasonKey && 'downloadFailed' !== $invalidReasonKey && 'invalidDownloadUrl' !== $invalidReasonKey && 'downloadWriteFailed' !== $invalidReasonKey && 'fileTooSmall' !== $invalidReasonKey && 'invalidZip' !== $invalidReasonKey && 'brokenZip' !== $invalidReasonKey);
		
		$this->db->transaction(function()
		use
		(
			$row,
			
			$urls,
			$eventualUrl,
			$invalidReasonKey,
			$downloadLinkUrlParts,
			$intermediaryIsbn,
			$drmToken,
			
			$metaIsbns,
			$metaAuthor,
			$metaTitle,
			$metaCoverStoredFilename,
			
			$fileHash,
			$unzippedFileHash,
			$contentHash,
			
			$isbn,
			
			$originalStoredFilename,
			$drmStoredFilename,
			
			$ebookDownloadLinkInvalidReasonId,
			$ebookDownloadLinkTypeId,
			$isValid,
			$isChecked,
			$assetId
		)
		{
			// insert ebook
			$ebookId = null;
			if ($originalStoredFilename !== '' || $drmStoredFilename !== '')
			{
				// @note author and title are still meta data
				$metaIsbn = $isbn;
				if ($metaIsbn === '' && !empty($metaIsbns)) $metaIsbn = reset($metaIsbns);
				
				$ebookId = $this->db->insert('ebook', 'id', [
					'isbn'						=> $metaIsbn,
					'author'					=> $metaAuthor,
					'title'						=> $metaTitle,
					'ismetadata'				=> (($isbn === '') ? 1 : 0),
					'originalstoredfilename'	=> $originalStoredFilename,
					'drmstoredfilename'			=> $drmStoredFilename,
				]);
			}
			
			// insert validation
			$data = [
				'ebookdownloadlink_id'				=> $row['id'],
				'ebookdownloadlinkinvalidreason_id'	=> $ebookDownloadLinkInvalidReasonId,
				'ebook_id'							=> $ebookId,
				'validationdate'					=> $this->db->expression('UTC_TIMESTAMP()'),
				'isvalid'							=> ($isValid ? 1 : 0),
				'eventualurl'						=> $eventualUrl,
				'filehash'							=> $fileHash,
				'unzippedfilehash'					=> $unzippedFileHash,
				'contenthash'						=> $contentHash,
				'drmtoken'							=> $drmToken,
			];
			if ($downloadLinkUrlParts !== null)
			{
				$data['uniqueurlparts'] = $downloadLinkUrlParts['uniqueurlparts'];
			}
			
			$ebookDownloadLinkValidationId = $this->db->insert('ebookdownloadlinkvalidation', 'id', $data);
			
			// insert redirects
			$position = 0;
			foreach ($urls as $redirectUrl)
			{
				$this->db->insert('ebookdownloadlinkredirect', 'id', [
					'ebookdownloadlinkvalidation_id'	=> $ebookDownloadLinkValidationId,
					'position'							=> $position,
					'url'								=> $redirectUrl,
				]);
				++$position;
			}
			
			// insert metaisbns
			foreach ($metaIsbns as $metaIsbn)
			{
				$this->db->insertOrUpdate('ebookdownloadlinkmetaisbn', 'id', [], [
					'ebookdownloadlink_id'	=> $row['id'],
					'metaisbn'				=> $metaIsbn,
				]);
			}
			
			// update ebookdownloadlink
			$data = [
				'd_latestebookdownloadlinkvalidation_id'	=> $ebookDownloadLinkValidationId,
				'ischecked'									=> ($isChecked ? 1 : 0),
				'checkcount'								=> $this->db->expression($this->db->quoteIdentifier('checkcount') . ' + 1'), // @todo this might have incorrect result because of transaction and no lock (i.e. read + update)
				'metaauthor'								=> $metaAuthor,
				'metatitle'									=> $metaTitle,
				'd_eventualurl'								=> $eventualUrl,
			];
			if ($isValid)
			{
				$data['d_latestvalidebookdownloadlinkvalidation_id'] = $ebookDownloadLinkValidationId;
				$data['d_hasbeenvalid'] = '1';
			}
			if ($isbn !== '') $data['isbn'] = $isbn;
			// @todo check if metacoverstoredfilename already existed, then delete? or dont even check meta data if cover already existed?
			if ($metaCoverStoredFilename !== '') $data['metacoverstoredfilename'] = $metaCoverStoredFilename;
			
			//if ($bookId !== null) $data['book_id'] = $bookId;
			if ($downloadLinkUrlParts !== null)
			{
				$data['ebookdownloadlinktype_id']	= $ebookDownloadLinkTypeId;
				$data['d_uniqueurlparts']			= $downloadLinkUrlParts['uniqueurlparts'];
			}
			
			$this->db->update('ebookdownloadlink', $data, ['id' => $row['id']]);
			
			
			
			if ($ebookId !== null)
			{
				/////////on transfer also update ebookpossession < hook?
				
				
				
				// create ebook for all current possessions
				
				// lock asset
				$this->db->lockRecord('asset', 'id', $assetId);
				
				$result = $this->db->select([
					'assetownership_id',
					'assetborrow_id',
					'assetkeeper_id',
					'islent',
					'FROM', 'd_currentassetpossession',
					'WHERE', [
						'asset_id' => $assetId,
					],
				]);
				foreach ($result as $row)
				{
					$this->db->insertOrUpdate('d_ebookassetpossession', 'id', [
						'assetownership_id'	=> $row['assetownership_id'],
						'assetborrow_id'	=> $row['assetborrow_id'],
						'assetkeeper_id'	=> $row['assetkeeper_id'],
						'asset_id'			=> $assetId,
						'islent'			=> $row['islent'],
					], [
						'ebook_id'			=> $ebookId,
						'storedfilename'	=> '',
					]);
				}
			}
		});
	}
	
	/**
	 *	@param		string		$url
	 *	@return		urls:array<string>, invalidReasonKey:string, downloadLinkUrlParts:(url:string, type:string, uniqueurlparts:string, isbn:string), intermediaryIsbn:string, drmToken:string, fp:resource
	**/
	protected function loadEbookDownloadLinkUrl($url)
	{
		$urls					= array();
		$invalidReasonKey		= '';
		$downloadLinkUrlParts	= null;
		$intermediaryIsbn		= '';
		$drmToken				= '';
		$fp						= null;
		
		
		$context = stream_context_create([
			'http' => [
				'follow_location' => false,
			],
		]);
		
		$loadNextUrl = true;
		$redirectCount = 0;
		while ($loadNextUrl)
		{
			$urls[] = $url;
			
			$loadNextUrl = false;
			unset($http_response_header);
			
			if ($redirectCount > self::MAX_REDIRECT_COUNT)
			{
				$invalidReasonKey = 'tooManyRedirects';
				break;
			}
			
			// rewrite proxy-like URLs from m.bol.com
			try
			{
				$url = $this->rewriteUrl($url);
			}
			catch (IError $e)
			{
				$e->allow(['urlInvalid', 'rewriteUrlInvalid']);
			}
			
			try
			{
				// check for valid intermediary URL that might contain the ISBN
				if ($intermediaryIsbn === '') $intermediaryIsbn = $this->obtainIsbnFromIntermediaryUrl($url);
			}
			catch (IError $e)
			{
				$e->allow(['urlInvalid', 'intermediaryUrlInvalid']);
			}
			
			// open socket to see if URL works
			$fp = @fopen($url, 'rb', false, $context);
			if ($fp === false)
			{
				// failed (could be due to eboekhuis proxy error?)
				$invalidReasonKey = 'urlFailed';
				break;
			}
			
			if (!isset($http_response_header))
			{
				$invalidReasonKey = 'urlFailed';
				break;
			}
			
			// check for redirect to eventual URL
			foreach ($http_response_header as $header)
			{
				$header = trim($header); // utf-8 safe? (doesn't need to be)
				if (strpos($header, 'Location:') === 0)
				{
					$redirectUrl = trim(substr($header, strlen('Location:'))); // utf-8 safe? (doesn't need to be)
					// check if it's a relative URL
					if (preg_match('(^[a-z]+://)', $redirectUrl) !== 1)
					{
						$parsedUrl = parse_url($url);
						$redirectUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $redirectUrl;
					}
					
					//$this->logger->log('message', 'debug validateEbookDownloadLink: ' . $row['id'] . ': loaded URL: ' . $url . ' - redirect to: ' . $redirectUrl);
					
					$url = $redirectUrl;
					++$redirectCount;
					$loadNextUrl = true;
					
					break;
				}
			}
			// if redirect found, do immediately
			if ($loadNextUrl) continue;
			
			try
			{
				// check for endpoint data
				$data = $this->getEndpointContentData($url, $fp);
				$drmToken = $data['drmToken'];
				if ($data['isbn'] !== '') $intermediaryIsbn = $data['isbn'];
				
				if ($data['redirect'] !== '')
				{
					//$this->logger->log('message', 'debug validateBookUrl: ' . $row['id'] . ': loaded URL endpoint: ' . $url . ' - redirect to: ' . $data['redirect']);
					
					$url = $data['redirect'];
					++$redirectCount;
					$loadNextUrl = true;
					
					continue;
				}
			}
			catch (IError $e)
			{
				$e->allow(['urlInvalid', 'endpointUrlInvalid']);
			}
			
			try
			{
				// if no redirect found, check if it was a valid download URL
				$downloadLinkUrlParts = $this->getDownloadLinkUrlParts($url);
			}
			catch (IError $e)
			{
				$e->allow(['urlInvalid', 'downloadLinkInvalid']);
				
				//$this->logger->log('message', 'debug validateEbookDownloadLink: ' . $row['id'] . ': invalid download URL: ' . $url);
				
				// not a valid download URL
				$invalidReasonKey = 'invalidDownloadUrl';
				break;
			}
			
			// only get intermediaryIsbn if downloadlink was valid
			if ($intermediaryIsbn === '')
			{
				// use isbn from file-download-attachment header
				foreach ($http_response_header as $header)
				{
					$header = trim($header); // utf-8 safe?
					if (strpos($header, 'Content-Disposition: attachment; filename=') === 0)
					{
						$filename = trim(substr($header, strlen('Content-Disposition: attachment; filename='))); // utf-8 safe?
						$intermediaryIsbn = $this->findIsbnOrEmptyString($filename);
						
						break;
					}
				}
			}
		}
		
		return [
			'urls'					=> $urls,
			'eventualUrl'			=> $url,
			'invalidReasonKey'		=> $invalidReasonKey,
			'downloadLinkUrlParts'	=> $downloadLinkUrlParts,
			'intermediaryIsbn'		=> $intermediaryIsbn,
			'drmToken'				=> $drmToken,
			'fp'					=> $fp,
		];
	}
	
	/**
	 *	@param		resource		$fp
	 *	@param		string			$tempFilePath
	 *	@return		void
	 *	@throws		ebookDownloadLinkInvalidReason, string
	**/
	protected function downloadEpub($fp, $tempFilePath)
	{
		if (file_put_contents($tempFilePath, $fp) === false)
		{
			// @note file_put_contents could also return false if the file is 0 bytes (at least on windows)
			throw new EError('ebookDownloadLinkInvalidReason', 'downloadWriteFailed');
		}
		else if (filesize($tempFilePath) < 700)
		{
			// failed (or eboekhuis response is 'link is being prepared')
			throw new EError('ebookDownloadLinkInvalidReason', 'fileTooSmall');
		}
	}
	
	/**
	 *	@param		string		$tempFilePath
	 *	@return		string		tempDirPath
	 *	@throws		ebookDownloadLinkInvalidReason, string
	 *	@throws		createTempFileFailed
	 *	@throws		createTempDirFailed
	**/
	protected function extractEpub($tempFilePath)
	{
		$tempDirPath = tempnam(sys_get_temp_dir(), 'sh_validate_zip_'); // Windows uses only the first three characters of prefix
		if ($tempDirPath === false) throw new EError('createTempFileFailed');
		@unlink($tempDirPath);
		$tempDirPath .= DIRECTORY_SEPARATOR;
		if (!mkdir($tempDirPath)) throw new EError('createTempDirFailed');
		
		$zip = new ZipArchive();
		if ($zip->open($tempFilePath) !== true)
		{
			throw new EError('ebookDownloadLinkInvalidReason', 'invalidZip');
		}
		// possibility of broken files inside zip, which will cause extract to stop
		else if (@$zip->extractTo($tempDirPath) === false)
		{
			throw new EError('ebookDownloadLinkInvalidReason', 'brokenZip');
		}
		@$zip->close();
		
		return $tempDirPath;
	}
	
	/**
	 *	@param		string		$tempDirPath
	 *	@return		(unzippedFileHash:binary, contentHash:binary)
	**/
	protected function generateHashes($tempDirPath)
	{
		// generate unzippedfilehash & contenthash
		$unzippedHashContext	= hash_init('sha512');
		$contentHashContext		= hash_init('sha512');
		
		$di = new RecursiveDirectoryIterator($tempDirPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$ii = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::SELF_FIRST);
		foreach ($ii as $fileInfo)
		{
			$localPath = str_replace($tempDirPath, '', $fileInfo->getPathname());
			$localPath = strtr($localPath, '\\', '/'); // convert windows separators to linux separators to use in the hash
			
			hash_update($unzippedHashContext, $localPath);
			
			if ($fileInfo->isFile())
			{
				hash_update_file($unzippedHashContext, $fileInfo->getPathname());
				hash_update_file($contentHashContext, $fileInfo->getPathname());
			}
		}
		
		$unzippedFileHash	= hash_final($unzippedHashContext, true);
		$contentHash		= hash_final($contentHashContext, true);
		
		return [
			'unzippedFileHash'	=> $unzippedFileHash,
			'contentHash'		=> $contentHash,
		];
	}
	
	/**
	 *	@param		string		$tempDirPath
	 *	@return		void
	 *	@throws		ebookDownloadLinkInvalidReason, string
	**/
	protected function checkDrm($tempDirPath)
	{
		if (is_file($tempDirPath . 'META-INF/encryption.xml'))
		{
			$xml = $this->parseXml(file_get_contents($tempDirPath . 'META-INF/encryption.xml'));
			if ($xml !== false)
			{
				// http://www.idpf.org/epub/20/spec/FontManglingSpec.html
				// Algorithm="http://www.idpf.org/2008/embedding" is for font embedding and doesn't mean the file is encrypted
				foreach ($xml->EncryptedData as $encryptedXml)
				{
					if (
						!isset($encryptedXml->EncryptionMethod['Algorithm'])
						||
						(
							(string)$encryptedXml->EncryptionMethod['Algorithm'] !== 'http://www.idpf.org/2008/embedding'
							&&
							(string)$encryptedXml->EncryptionMethod['Algorithm'] !== 'http://ns.adobe.com/pdf/enc#RC'
						)
					)
					{
						throw new EError('ebookDownloadLinkInvalidReason', 'drm');
					}
				}
			}
		}
	}
	
	/**
	 *	@param		string		$tempDirPath
	 *	@param		boolean		$checkMetaData
	 *	@return		(metaIsbns:array<string>, metaAuthor:string, metaTitle:string, metaCoverStoredFilename:string)
	 *	@throws		ebookDownloadLinkInvalidReason, string
	**/
	protected function checkEpubFormatAndMetaData($tempDirPath, $checkMetaData = true)
	{
		$metaIsbns					= array();
		$metaAuthor					= '';
		$metaTitle					= '';
		$metaCoverStoredFilename	= '';
		
		$xml = $this->parseXml(file_get_contents($tempDirPath . 'META-INF/container.xml'));
		if ($xml === false)
		{
			throw new EError('ebookDownloadLinkInvalidReason', 'invalidEpub');
		}
		
		
	 	if (!isset($xml->rootfiles->rootfile))
 		{
 			throw new EError('ebookDownloadLinkInvalidReason', 'invalidEpub');
 		}
	 	
		$foundContentOpf	= false;
		$foundBookContent	= false;
		foreach ($xml->rootfiles->rootfile as $rootFileXml)
		{
			// @todo does (string)$xml['attr'] throw exception?
			if ((string)$rootFileXml['media-type'] !== 'application/oebps-package+xml') continue;
			$filePath = (string)$rootFileXml['full-path'];
			if (!$this->validatePath($tempDirPath . $filePath, $tempDirPath)) continue;
			if (!is_file($tempDirPath . $filePath)) continue;
			
			$xml = $this->parseXml(file_get_contents($tempDirPath . $filePath));
			if ($xml === false) continue;
			
			$foundContentOpf = true;
			$opfDirPath = dirname($tempDirPath . $filePath) . DIRECTORY_SEPARATOR;
			
			// keep a map of all manifest items referenced by their id
			$manifestItemMap = [];
			if (isset($xml->manifest->item))
			{
				foreach ($xml->manifest->item as $item)
				{
					$manifestItemMap[(string)$item['id']] = $item;
				}
			}
			
			
			// @note ONLY use ISBN from meta data when you plan to verify it manually
			// it is EXTREMELY unreliable, EVEN in modern day purchased e-books!
			// it can be the ISBN of the paper version
			// it can be the ISBN of a completely different e-book
			if ($checkMetaData)
			{
				try
				{
					// get isbn from meta data
					$metaIsbns[] = $this->getIsbnFromMetaData($xml);
				}
				catch (IError $e)
				{
					$e->allow(['metaDataNotFound', 'isbnNotFound']);
				}
				
				$metadata = isset($xml->metadata) ? $xml->metadata : (isset($xml->resourceItemInfo->metadata) ? $xml->resourceItemInfo->metadata : null);
				if ($metaCoverStoredFilename === '' && $metadata !== null)
				{
					$metaAuthor	= trim((string)$metadata->creator);
					$metaTitle	= trim((string)$metadata->title);
					
					foreach ($metadata->meta as $metaTag)
					{
						if ((string)$metaTag['name'] !== 'cover') continue;
						$coverId = (string)$metaTag['content'];
						if (!isset($manifestItemMap[$coverId])) continue;
						$coverItem = $manifestItemMap[$coverId];
						$coverPath = rawurldecode((string)$coverItem['href']);
						if (!$this->validatePath($opfDirPath . $coverPath, $opfDirPath)) continue;
						if (!is_file($opfDirPath . $coverPath)) continue;
						
						$metaCoverStoredFilename = $this->generateCoverFilename($coverPath, $metaIsbns, $metaAuthor, $metaTitle);
						
						/*
						// @todo maybe resize?
						$resizeds = ImageResizer::resize($opfDirPath . $coverPath, array($this->config['coverSizeListing']));
						foreach ($resizeds as $resized)
						{
							$this->assetStore->assignFile
							@unlink($resized['filename']);
						}
						*/
						
						$this->assetStore->assignFile($this->config['s3.bucket'], $this->config['s3.directory'] . 'epub-cover/' . $metaCoverStoredFilename, new StreamResourceReader(fopen($opfDirPath . $coverPath, 'rb')), S3FileSystem::ACL_PUBLIC_READ);
						
						break;
					}
				}
			}
			
			$pageCount = 0;
			$wordCount = 0;
			if (isset($xml->spine->itemref))
			{
				foreach ($xml->spine->itemref as $spineItem)
				{
					$pageId = (string)$spineItem['idref'];
					if (!isset($manifestItemMap[$pageId])) continue;
					$pageItem = $manifestItemMap[$pageId];
					$pagePath = rawurldecode((string)$pageItem['href']);
					if (!$this->validatePath($opfDirPath . $pagePath, $opfDirPath)) continue;
					if (!is_file($opfDirPath . $pagePath)) continue;
					if (filesize($opfDirPath . $pagePath) > 5000000) continue; // 5.000.000 bytes = 5 MB
					
					++$pageCount;
					
					$content = file_get_contents($opfDirPath . $pagePath);
					
					if ($checkMetaData)
					{
						// try to get isbn from epub content
						// @note often includes isbn from paper edition!
						if (preg_match_all('(978[0-9 -]+)', $content, $matches) !== 0)
						{
							foreach ($matches[0] as $match)
							{
								$metaIsbns[] = substr(preg_replace('([^0-9])', '', $match), 0, 13);
							}
						}
					}
					
					// strip all HTML
					$content = strip_tags($content);
					$content = preg_replace('(&#[0-9]+;)', '', $content); // &#160; encoded HTML entities aren't stripped by strip_tags
					//$content = preg_replace('((\\s)+)', '\\1', $content); // collapse whitespace @note capturing the whitespace in the regex may cause a segmentation fault on large string with a lot of whitespace
					$content = preg_replace('(\\s+)', ' ', $content); // @temp collapse whitespace into space
					$content = trim($content);
					
					$wordCount += str_word_count($content);
				}
			}
			
			// count as a real book if it has at least 5 pages or at least 500 words
			if ($pageCount >= 5 || $wordCount >= 500)
			{
				$foundBookContent = true;
			}
		}
		
		if (!$foundContentOpf)
		{
			throw new EError('ebookDownloadLinkInvalidReason', 'invalidEpub');
		}
		if (!$foundBookContent)
		{
			throw new EError('ebookDownloadLinkInvalidReason', 'bookHasNoContent');
		}
		
		return [
			'metaIsbns'					=> $metaIsbns,
			'metaAuthor'				=> $metaAuthor,
			'metaTitle'					=> $metaTitle,
			'metaCoverStoredFilename'	=> $metaCoverStoredFilename,
		];
	}
	
	/**
	 *	@param		binary		$unzippedFileHash
	 *	@param		TId|null	$excludeEbookDownloadLinkId
	 *	@return		boolean
	**/
	protected function koboEpubExists($unzippedFileHash, $excludeEbookDownloadLinkId = null)
	{
		// @todo maybe also check if file was DRM? (although it already checks UUID in ACSM, and this check is for non-DRM books)
		
		// if it's a kobo link, check if the same file doesn't already exist
		// use unzippedfilehash because kobo zips the file on the fly which includes a timestamp value making the filehash different
		$conditionsDsl = [
			'edlv.isvalid'			=> 1,
			'edlv.unzippedfilehash'	=> $unzippedFileHash,
			'edlt.key'				=> 'kobo',
			// here possible AND for the excludeEbookDownloadLinkId
		];
		
		if ($excludeEbookDownloadLinkId !== null)
		{
			$conditionsDsl[] = [
				'edl.id' => $this->db->isNot($excludeEbookDownloadLinkId),
			];
		}
		
		$result = $this->db->select([
			'FROM', 'ebookdownloadlinkvalidation', 'edlv',
			'INNER JOIN', 'ebookdownloadlink', 'edl', ['edlv.ebookdownloadlink_id' => 'edl.id'],
			'INNER JOIN', 'ebookdownloadlinktype', 'edlt', ['edlt.id' => 'edl.ebookdownloadlinktype_id'],
			'WHERE', $conditionsDsl,
		]);
		return $result->hasRows();
	}
	
	/**
	 *	@param		string				$tempFilePath
	 *	@param		boolean				$isDrm
	 *	@param		string				$isbn
	 *	@param		array<string>		$metaIsbns
	 *	@param		string				$metaAuthor
	 *	@param		string				$metaTitle
	 *	@return		string		stored filename
	**/
	protected function storeEpub($tempFilePath, $isDrm, $isbn = '', $metaIsbns = [], $metaAuthor = '', $metaTitle = '')
	{
		$storedFilename = $this->generateFilename($isbn, $metaIsbns, $metaAuthor, $metaTitle);
		
		$s3Dir = $isDrm ? 'epub-drm' : 'epub-original';
		
		$this->assetStore->assignFile($this->config['s3.bucket'], $this->config['s3.directory'] . $s3Dir . '/' . $storedFilename, new StreamResourceReader(fopen($tempFilePath, 'rb')));
		
		return $storedFilename;
	}
	
	/**
	 *	@param		string				$coverPath
	 *	@param		array<string>		$metaIsbns
	 *	@param		string				$metaAuthor
	 *	@param		string				$metaTitle
	 *	@return		void
	**/
	protected function generateCoverFilename($coverPath, $metaIsbns = [], $metaAuthor = '', $metaTitle = '')
	{
		// @note the database field is max 191 characters (config['maxTextFieldLenth'])
		// the filename is composed of the following parts:
		// 890/ab/9781234567890_21751006e1fa4d6b9ad6d4c360d216b2_author_title.jpeg
		// so the minimum number of characters is: 60
		// @example: 890/ab/9781234567890_21751006e1fa4d6b9ad6d4c360d216b2__.jpeg
		// the largest S3 directory we use is: assetLink/preview/epub-cover/
		// (which we don't store in the filename yet, but might in the future)
		// which is another 29 characters, making the total minimum: 60 + 29 = 89
		// which leaves: 191 - 89 = 102 characters for the author and title
		
		//$maxCharacterCount		= $this->config['maxTextFieldLenth'];
		//$usedCharacterCount		= strlen('890/ab/9781234567890_21751006e1fa4d6b9ad6d4c360d216b2__.jpeg') + strlen('assetLink/preview/epub-cover/');
		$maxPartCharacterCount	= 50; // takes * 2 charachters (so 100) in the final string
		
		$isbn = '';
		if (!empty($metaIsbns)) $isbn = reset($metaIsbns);
		if ($isbn === '') $isbn = 'unknown';
		
		$metaAuthor = SlugConverter::convertSlug($metaAuthor);
		$metaAuthor = mb_substr($metaAuthor, 0, $maxPartCharacterCount);
		if ($metaAuthor === '') $metaAuthor = 'unknown';
		
		$metaTitle = SlugConverter::convertSlug($metaTitle);
		$metaTitle = mb_substr($metaTitle, 0, $maxPartCharacterCount);
		if ($metaTitle === '') $metaTitle = 'unknown';
		
		// last 3 numbers of the isbn
		$isbnDirectory = substr($isbn, -3);
		
		
		// max number of directories = 26 * 26 = 676
		$alphabet = 'abcdefghijklmnopqrstuvwxyz';
		$randomDirectory = substr($alphabet, mt_rand(0, 25), 1) . substr($alphabet, mt_rand(0, 25), 1);
		
		$extension = 'jpg';
		$pos = strrpos($coverPath, '.');
		if ($pos !== false)
		{
			$checkExtension = substr($coverPath, $pos + 1, 4);
			if (preg_match('([^a-zA-Z])u', $checkExtension) === 0)
			{
				$extension = $checkExtension;
			}
		}
		
		return $isbnDirectory . '/' . $randomDirectory . '/' . $isbn . '_' . UuidUtil::v4Hex() . '_' . $metaAuthor . '_' . $metaTitle . '.' . $extension;
	}
	
	/**
	 *	@param		string				$isbn
	 *	@param		array<string>		$metaIsbns
	 *	@param		string				$metaAuthor
	 *	@param		string				$metaTitle
	 *	@return		void
	**/
	protected function generateFilename($isbn = '', $metaIsbns = [], $metaAuthor = '', $metaTitle = '')
	{
		// @note the database field is max 191 characters (config['maxTextFieldLenth'])
		// the filename is composed of the following parts:
		// 890/ab/9781234567890_21751006e1fa4d6b9ad6d4c360d216b2_author_title.epub
		// so the minimum number of characters is: 60
		// @example: 890/ab/9781234567890_21751006e1fa4d6b9ad6d4c360d216b2__.epub
		// the largest S3 directory we use is: assetLink/preview/epub-original/
		// (which we don't store in the filename yet, but might in the future)
		// which is another 32 characters, making the total minimum: 60 + 32 = 92
		// which leaves: 191 - 92 = 99 characters for the author and title
		
		//$maxCharacterCount		= $this->config['maxTextFieldLenth'];
		//$usedCharacterCount		= strlen('890/ab/9781234567890_21751006e1fa4d6b9ad6d4c360d216b2__.epub') + strlen('assetLink/preview/epub-original/');
		$maxPartCharacterCount	= 49; // takes * 2 charachters (so 98) in the final string
		
		if ($isbn === '' && !empty($metaIsbns)) $isbn = reset($metaIsbns);
		if ($isbn === '') $isbn = 'unknown';
		
		$metaAuthor = SlugConverter::convertSlug($metaAuthor);
		$metaAuthor = mb_substr($metaAuthor, 0, $maxPartCharacterCount);
		if ($metaAuthor === '') $metaAuthor = 'unknown';
		
		$metaTitle = SlugConverter::convertSlug($metaTitle);
		$metaTitle = mb_substr($metaTitle, 0, $maxPartCharacterCount);
		if ($metaTitle === '') $metaTitle = 'unknown';
		
		// last 3 numbers of the isbn
		$isbnDirectory = substr($isbn, -3);
		
		
		// max number of directories = 26 * 26 = 676
		$alphabet = 'abcdefghijklmnopqrstuvwxyz';
		$randomDirectory = substr($alphabet, mt_rand(0, 25), 1) . substr($alphabet, mt_rand(0, 25), 1);
		
		return $isbnDirectory . '/' . $randomDirectory . '/' . $isbn . '_' . UuidUtil::v4Hex() . '_' . $metaAuthor . '_' . $metaTitle . '.epub';
	}
	
	protected function stripSlashes($value)
	{
		if (is_array($value))
		{
			foreach ($value as $key => $subValue)
			{
				$value[$key] = $this->stripSlashes($subValue);
			}
		}
		else if (is_string($value))
		{
			$value = stripslashes($value);
		}
		
		return $value;
	}
	
	protected function parseXml($content)
	{
		// strip xml namespaces
		$content = preg_replace('((</?)[a-zA-Z]+:([a-zA-Z]))', '\1\2', $content);
		return @simplexml_load_string($content);
	}
	
	protected function findIsbn($string)
	{
		// start at first occurrence of 978 or 979
		$pos = strpos($string, '978');
		if ($pos === false) $pos = strpos($string, '979');
		if ($pos === false) throw new EError('isbnInvalid');
			
		$string = substr($string, $pos);
		
		try
		{
			return $this->parseIsbn($string);
		}
		catch (IError $e)
		{
			$e->allow('isbnInvalid');
		}
		
		// cut off at first non digit
		preg_match('([^0-9])', $string, $match, PREG_OFFSET_CAPTURE);
		if (!isset($match[0][1])) throw new EError('isbnInvalid');
		
		$string = substr($string, 0, $match[0][1]);
		try {
		return $this->parseIsbn($string);
		} catch (IError $e) { $e->rethrow('isbnInvalid'); }
	}
	
	protected function findIsbnOrEmptyString($string)
	{
		$isbn = '';
		
		try
		{
			$isbn = $this->findIsbn($string);
		}
		catch (IError $e)
		{
			$e->allow('isbnInvalid');
		}
		
		return $isbn;
	}
	
	protected function parseIsbn($string)
	{
		// @todo maybe simplify to just searching for 978 (or 979)
		if ((preg_match('/\b(?:ISBN(?:: ?| ))?((?:97[89])?\d{9}[\dx])\b/i', str_replace('-', '', $string))) !== 1) throw new EError('isbnInvalid');
		
		return preg_replace('([^0-9])', '', $string);
	}
	
	protected function getIsbnFromMetaData($xml)
	{
		$isbn = null;
		
		$metadata = isset($xml->metadata) ? $xml->metadata : (isset($xml->resourceItemInfo->metadata) ? $xml->resourceItemInfo->metadata : null);
		if ($metadata === null) throw new EError('metaDataNotFound');
		
		foreach ($metadata->identifier as $identifier)
		{
			try
			{
				$isbn = $this->findIsbn((string)$identifier);
				
				break;
			}
			catch (IError $e)
			{
				$e->allow('isbnInvalid');
			}
		}
		
		if ($isbn === null) throw new EError('isbnNotFound');
		
		return $isbn;
	}
	
	/**
	 *	@param		string		$path
	 *	@param		string		$basePath
	 *	@return		boolean
	**/
	public function validatePath($path, $basePath)
	{
		$realPath = realpath($path);
		
		return ($realPath !== false && strpos($realPath, $basePath) === 0);
	}
}