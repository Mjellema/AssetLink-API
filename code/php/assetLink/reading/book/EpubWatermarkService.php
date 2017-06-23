<?php
namespace assetLink\reading\book;

use DateTime;
use DateTimeZone;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use ZipArchive;
use bite\di\DiTarget;
use bite\error\EError;
use bite\error\IError;
use bite\io\file\FileSystemUtil;
use bite\io\stream\IReader;
use bite\io\stream\ReaderCopyPipe;
use bite\io\stream\StreamResourceWriter;
use bite\io\stream\TempFileReader;
use bite\security\crypto\UnauthenticatedCrypto;

/**
 *	opf:
 *	http://netkingcol.blogspot.nl/2010/01/closer-look-at-opf.html
 *	
 *	zip:
 *	http://alexbergin.com/2010/epub-files-php
 *	https://github.com/vtardia/md2epub
 *	
 *	@depdendency config
**/
class EpubWatermarkService extends DiTarget
{
	/*
	@example how to create zip file with mimetype as first file uncompressed
	file_put_contents('mimetype', 'application/epub+zip');
	system('zip -0X mimetype.zip mimetype');
	
	-0		store only
	-D		do not add directory entries
	-X		eXclude eXtra file attributes
	-q		quiet operation
	-j		junk (don't record) directory names
	
	or use existing mimetype.zip filedata base64 encoded
	file_put_contents('mimetype.zip', base64_decode('UEsDBAoAAAAAAJqB0kZvYassFAAAABQAAAAIAAAAbWltZXR5cGVhcHBsaWNhdGlvbi9lcHViK3ppcFBLAQIeAwoAAAAAAJqB0kZvYassFAAAABQAAAAIAAAAAAAAAAAAAAC0gQAAAABtaW1ldHlwZVBLBQYAAAAAAQABADYAAAA6AAAAAAA='));
	
	@example how to add others files to the zip
	echo -n "application/epub+zip" > mimetype
	zip -0X mimetype.zip mimetype
	zip -r mimetype.zip * -x mimetype
	
	or use existing mimetype.zip on the filesystem
	$this->config['mimeTypeZipFilePath']; // e.g. 'assetLink/watermark/mimetype.zip'
	*/
	
	const VERSION			= '1';
	
	public function watermarkFile(IReader $epub, $transactionReference, $binaryUuid, DateTime $date, $customerReference, $customerName, $customerEmail)
	{
		$tempFilePath = $this->save($epub);
		$tempDirPath = $this->extract($tempFilePath);
		$this->addWatermark($tempDirPath, $transactionReference, $binaryUuid, $date, $customerReference, $customerName, $customerEmail);
		$tempFilePath = $this->assemble($tempDirPath);
		
		return new TempFileReader($tempFilePath);
	}
	
	protected function save(IReader $epub)
	{
		$tempFilePath = tempnam(sys_get_temp_dir(), 'sh_watermark_file_'); // Windows uses only the first three characters of prefix
		if ($tempFilePath === false) throw new EError('createTempFileFailed');
		
		$readerCopyPipe = new ReaderCopyPipe();
		$readerCopyPipe->pipe($epub, new StreamResourceWriter(fopen($tempFilePath, 'wb')));
		
		return $tempFilePath;
	}
	
	protected function extract($tempFilePath)
	{
		$tempDirPath = tempnam(sys_get_temp_dir(), 'sh_watermark_zip_'); // Windows uses only the first three characters of prefix
		if ($tempDirPath === false) throw new EError('createTempFileFailed');
		@unlink($tempDirPath);
		$tempDirPath .= DIRECTORY_SEPARATOR;
		if (!mkdir($tempDirPath)) throw new EError('createTempDirFailed');
		
		$zip = new ZipArchive();
		if ($zip->open($tempFilePath) !== true) throw new EError('epubZipOpenFailed'); // @note open() also throws notice which contains usefull information
		// possibility of broken files inside zip, which will cause extract to stop
		if (@$zip->extractTo($tempDirPath) === false) throw new EError('epubZipExtractFailed');
		$zip->close();
		
		return $tempDirPath;
	}
	
	protected function assemble($tempDirPath)
	{
		$tempFilePath = tempnam(sys_get_temp_dir(), 'sh_watermark_epub_'); // Windows uses only the first three characters of prefix
		if ($tempFilePath === false) throw new EError('createTempFileFailed');
		
		//if (!copy($this->config['mimeTypeZipFilePath'], $tempFilePath)) throw new EError('Failed to copy mime zip to temporary file');
		file_put_contents($tempFilePath, base64_decode('UEsDBAoAAAAAAJqB0kZvYassFAAAABQAAAAIAAAAbWltZXR5cGVhcHBsaWNhdGlvbi9lcHViK3ppcFBLAQIeAwoAAAAAAJqB0kZvYassFAAAABQAAAAIAAAAAAAAAAAAAAC0gQAAAABtaW1ldHlwZVBLBQYAAAAAAQABADYAAAA6AAAAAAA='));
		
		$zip = new ZipArchive();
		if ($zip->open($tempFilePath) !== true) throw new EError('zipInvalid'); // @note open() also throws notice which contains usefull information
		
		$excludes = array('mimetype', '.DS_Store', 'Thumbs.db');
		
		$di = new RecursiveDirectoryIterator($tempDirPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$ii = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::SELF_FIRST);
		foreach ($ii as $fileInfo)
		{
			if (in_array($fileInfo->getFilename(), $excludes)) continue;
			
			$localPath = str_replace($tempDirPath, '', $fileInfo->getPathname());
			if ($fileInfo->isDir())
			{
				$zip->addEmptyDir($localPath);
			}
			else if ($fileInfo->isFile())
			{
				//$zip->addFromString($localPath, file_get_contents($fileInfo->getPathname()));
				$zip->addFile($fileInfo->getPathname(), $localPath);
			}
		}
		$zip->close();
		
		/*
		// @alternative using zip command line command
		// @note zip command needs file extension to be .zip
		rename($tempFilePath, $tempFilePath . '.zip');
		$tempFilePath = $tempFilePath . '.zip';
		
		//system('zip -Xr9D ' . escapeshellarg($tempFilePath) . ' ' . escapeshellarg($tempDirPath) . '* -x mimetype');
		// @note for zip command to ignore the 'base path' you need to cd to that directory
		// @todo maybe could use php function chdir() instead?
		echo shell_exec(
			'cd ' . escapeshellarg($tempDirPath) . '; ' .
			'zip -r ' . escapeshellarg($tempFilePath) . ' * -x mimetype'
		);
		*/
		
		/*
		// @alternative using PHPZip class
		// https://github.com/Grandt/PHPePub/blob/master/src/PHPePub/Core/EPub.php
		// https://github.com/Grandt/PHPZip
		// https://github.com/Grandt/PHPZipMerge
		// https://github.com/Grandt/PHPBinString
		
		use PHPZip\Zip\File\Zip;
		
		$zip = new Zip();
		$zip->setZipFile($tempFilePath);
		
        $zip->setExtraField(false);
        $zip->addFile('application/epub+zip', 'mimetype');
        $zip->setExtraField(true);
        $zip->addDirectory('META-INF');
		
		$excludes = array('mimetype', '.DS_Store', 'Thumbs.db');
		
		$di = new RecursiveDirectoryIterator($tempDirPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$ii = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::SELF_FIRST);
		foreach ($ii as $fileInfo)
		{
			if (in_array($fileInfo->getFilename(), $excludes)) continue;
			
			$localPath = str_replace($tempDirPath, '', $fileInfo->getPathname());
			if ($fileInfo->isDir())
			{
				$zip->addDirectory($localPath);
			}
			else if ($fileInfo->isFile())
			{
				$zip->addFile(file_get_contents($fileInfo->getPathname()), $localPath, 0, null, true);
			}
		}
		*/
		
		FileSystemUtil::deleteDir($tempDirPath);
		
		return $tempFilePath;
	}
	
	protected function addWatermark($tempDirPath, $transactionReference, $binaryUuid, DateTime $date, $customerReference, $customerName, $customerEmail)
	{
		// @todo better just check META-INF/container.xml for rootfile (and fall back to search for *.opf)
		// some epubs contain more than 1 opf (which could be in the wrong directory)
		
		$dirIterator		= new RecursiveDirectoryIterator($tempDirPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$recursiveIterator	= new RecursiveIteratorIterator($dirIterator);
		$regexIterator		= new RegexIterator($recursiveIterator, '(\\.opf$)');
		foreach($regexIterator as $fileInfo)
		{
			$content = file_get_contents($fileInfo->getPathname());
			
			// strip xml namespaces
			$content = preg_replace('((</?)[a-zA-Z]+:([a-zA-Z]))', '\1\2', $content);
			$xml = @simplexml_load_string($content);
			if ($xml !== false)
			{
				$opfDirPath = $fileInfo->getPath() . DIRECTORY_SEPARATOR;
				
				$items = array();
				if (isset($xml->manifest->item))
				{
					foreach ($xml->manifest->item as $item)
					{
						$items[(string)$item['id']] = rawurldecode((string)$item['href']);
					}
				}
				
				if (isset($xml->spine->itemref))
				{
					foreach ($xml->spine->itemref as $itemref)
					{
						$idRef = (string)$itemref['idref'];
						if (isset($items[$idRef]))
						{
							$href = $items[$idRef];
							
							$htmlFilePath = $opfDirPath . $href;
							
							// @temp @temp @temp @temp @temp 
							if (is_file($htmlFilePath))
							// @temp @temp @temp @temp @temp 
							{
								$this->writeWatermarkToHtmlFile($htmlFilePath, $transactionReference, $binaryUuid, $date, $customerReference, $customerName, $customerEmail);
							}
						}
					}
				}
			}
		}
	}
	
	protected function writeWatermarkToHtmlFile($filePath, $transactionReference, $binaryUuid, DateTime $date, $customerReference, $customerName, $customerEmail)
	{
		// make new watermark data for each file, the salt is different for each file, so the resulting ciphertext will be different for each file
		$watermarkData = $this->makeWatermarkData($transactionReference, $binaryUuid, $date, $customerReference, $customerName, $customerEmail);
		$watermarkHtml = '<div style="display: none;">' . $watermarkData . '</div>';
		
		$html = file_get_contents($filePath);
		
		// find </body> end tag
		$pos = strrpos($html, '</body>');
		if ($pos === false && preg_match_all('(<\s*/\s*body\s*>)', $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) !== 0)
		{
			// find </body> end tag with whitespace
			$match = end($matches);
			$pos = $match[0][1];
		}
		
		if ($pos !== false)
		{
			$html = substr_replace($html, $watermarkHtml, $pos, 0);
		}
		else
		{
			// if not is not found, just add it at the end
			$html .= $watermarkHtml;
		}
		
		file_put_contents($filePath, $html);
	}
	
	protected function makeWatermarkData($transactionReference, $binaryUuid, DateTime $date, $customerReference, $customerName, $customerEmail)
	{
		// @todo copy
		$date->setTimeZone(new DateTimeZone('UTC'));
		
		$data = array();
		$data[] = self::VERSION;
		$data[] = $transactionReference;
		$data[] = $binaryUuid;
		$data[] = $date->getTimestamp();
		$data[] = $customerReference;
		$data[] = $customerName;
		$data[] = $customerEmail;
		
		// escape separator character
		foreach ($data as $index => $value)
		{
			$data[$index] = str_replace(',', '\\,', $value);
		}
		
		$str = implode(',', $data);
		
		// @note append a comma and the version to the unencrypted string so we can always determine the version
		return base64_encode(UnauthenticatedCrypto::encrypt($str, $this->config['epubWatermarkService.password']) . ',' . self::VERSION);
	}
}