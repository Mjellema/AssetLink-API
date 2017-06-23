<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Quick start</div>
	<div class="sectionBlock-section-text">
		These quick start examples will show you the very basics to get up and running quickly. To understand the ins and outs of the API and each action however, it is advised to read the documentation pages.
		<br />
		Code examples will be shown in <span class="code">PHP</span>.
	</div>
	<div class="sectionBlock-section-attention">
		Please note that these examples don't use any best practices with regards to program architecture (i.e. minimizing code duplication, separation of concerns, abstraction, dependency injection, etc.).
		They are simply the bare minimum that you can use to experiment with, and see how the API works on a low level.
	</div>
	<!-- COMMENT:
	<div class="sectionBlock-section-text">
		<ul class="list">
			<li>Register a user account</li>
			<li>Register your system (e.g. website) that requires API access</li>
			<li>To allow your system to automatically verify company e-mail ..</li>
			<li>Grant system permissions on company e-mail (asset keeper)</li>
		</ul>
	</div>
	-->
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Start with a simple 'library' function that we will use in the examples</div>
		<div class="sectionBlock-section-example-content">
function sendRequest($request, $secret)
{
	// convert to application/x-www-form-urlencoded
	$content = http_build_query($request);
	// add signature
	$content = $content . '&signature=' . hash_hmac('sha1', $content, $secret);
	
	// @note replace localhost with the correct tld
	$response = file_get_contents('http://yourhost/api/', false, stream_context_create(['http' => [
		'timeout'			=> 3,
		'protocol_version'	=> 1.1,
		'method'			=> 'POST',
		'header'			=> ['Content-Type: application/x-www-form-urlencoded'],
		'content'			=> $content,
	]]));
	if ($response === false) throw new Exception('connectionFailed');
	$data = [];
	parse_str($response, $data);
	if (isset($data['internalError']))
	{
		if (!isset($data['internalError']['key'])) throw new Exception('internalErrorKeyUndefined');
		throw new Exception($data['internalError']['key']);
	}
	if (isset($data['protocolError']))
	{
		if (!isset($data['protocolError']['key'])) throw new Exception('protocolErrorKeyUndefined');
		throw new Exception($data['protocolError']['key']);
	}
	if (isset($data['error']))
	{
		if (!isset($data['error']['key'])) throw new Exception('errorKeyUndefined');
		throw new Exception($data['error']['key']);
	}
	if (!isset($data['result'])) throw new Exception('resultUndefined');

	return $data['result'];
}
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Accept e-book download link from customer</div>
		<div class="sectionBlock-section-example-content">
$request = [
	'api'				=> 'assetLink',
	'version'			=> '0.1',
	'service'			=> 'ebookAssetService',
	'action'			=> 'registerEbookDownloadLink',
	'loginName'			=> 'yourSite',
	'password'			=> 'yourPassword',
	'data' => [
		'downloadLink'	=> 'http://www.example.com/download-ebook/a33cf54d-500f-4ddb-a588-cb08a06a45aa.epub',
		'email'			=> 'info@yourcompany.tld',
		'previousOwner'	=> [
			'email'			=> 'john@doe.tld',
			'personDetails'	=> ['firstName' => 'John', 'lastName' => 'Doe'],
		],
	],
];

$assetOwnershipId = sendRequest($request, 'yourSharedSecret');
// @todo store $assetOwnershipId somewhere so you can transfer the ownership to someone else at a later time
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Transfer asset to other customer</div>
		<div class="sectionBlock-section-example-content">
$request = [
	'api'				=> 'assetLink',
	'version'			=> '0.1',
	'service'			=> 'assetService',
	'action'			=> 'transferAssetOwnership',
	'loginName'			=> 'yourSite',
	'password'			=> 'yourPassword',
	'data' => [
		'assetOwnershipId'	=> 'dc8ac37a8ef54e1381cbe0c4628543c0',
		'email'				=> 'jane@doe.tld',
		'personDetails'		=> ['firstName' => 'Jane', 'lastName' => 'Doe'],
	],
];

$newAssetOwnershipId = sendRequest($request, 'yourSharedSecret');
// @todo store $newAssetOwnershipId somewhere so you can transfer the ownership to someone else at a later time
		</div>
	</div>
</div>