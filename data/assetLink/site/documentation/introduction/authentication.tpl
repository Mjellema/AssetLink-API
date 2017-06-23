<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Authentication</div>
	<div class="sectionBlock-section-text">
		Usually one authenticates API requests by using an API key, sometimes combined with an API username. This API is no different, except the API key is simply called <span class="code">password</span>.
		Why name it <span class="code">password</span>? Because it should be used, stored and <strong>protected</strong> like a password. It is secret and should only be known to your system. It authenticates your system (website / app / etc.) just like it authenticates you as a person on your favorite social media site.
		So just like you keep your social media password secure, you need to keep your API password secure.
		<br />
		That means:
		<ul class="list">
			<li>Don't send it in e-mails</li>
			<li>Don't store it in a config file that is commited into version control (e.g. git)</li>
			<li>Don't embed it in client side applications (e.g. javascript)</li>
		</ul>
	</div>
</div>
<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">System login name &amp; password</div>
	<div class="sectionBlock-section-text">
		So your website, or app, or whatever you're building is called a <span class="code">system</span> on our end.
		Just like a <span class="code">user</span> (which is always a person), a <span class="code">system</span> has autonomous access to our platform (via the API).
		That means it has its own <span class="code">loginName</span> and <span class="code">password</span> to authenticate itself.
		The request protocol specifies these 2 parameters, so you can authenticate each request by passing in your <span class="code">loginName</span> and <span class="code">password</span>.
	</div>
	<div class="sectionBlock-section-attention">
		Please make sure you're always using a secure HTTPS endpoint so your password will never cross the wire unencrypted.
	</div>
</div>
<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Passtoken</div>
	<div class="sectionBlock-section-text">
		Now that we understand the API key is actually a <span class="code">password</span> that needs to be kept as secret as possible, it's undesirable to include it in each request.
		Even though <span class="code">HTTPS</span> helps a great deal in keeping all communication secure (and therefore your password safe in transit), there's always room for improvement.
		Using a short lived <span class="code">passtoken</span> as a (possible limited) stand-in for the <span class="code">password</span>, we can improve security even more.
	</div>
	<div class="sectionBlock-section-attention">
		Note that passtokens aren't implemented yet
	</div>
</div>
<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Signing</div>
	<div class="sectionBlock-section-text">
		So while HTTPS helps with secure communication, we don't want to fully rely on it for two reasons:
		<ol class="list _numbered">
			<li>We want the API to remain safe to use even in the absence of an encrypted transport layer (i.e. it should be equally usable over HTTP)</li>
			<li>In case of a Man In The Middle attack that compromises the secure nature of HTTPS</li>
		</ol>
	</div>
	<div class="sectionBlock-section-text">
		Therefore you must sign your requests using an <span class="code">SHA1</span> keyed hash message authentication code (HMAC) of all the request variables using a <strong>shared secret</strong>.
		This secret, like the password should be kept safe and secure, and unlike the password it is <strong>never</strong> included in the request. It is only used to generate the the <span class="code">signature</span> that <strong>is</strong> included in the request.
		<br />
		Because the secret is shared (i.e. known to both the system and the API), the signature can be reproduced by the API to verify the authenticity of the request, as well as if any request variables where tampered with.
	</div>
	<div class="sectionBlock-section-text">
		The signature could theoretically even fully replace the <span class="code">password</span> for authentication purposes, but because the API also needs to have access to this secret, it needs to be <strong>stored</strong> by the API in an accessible manner.
		Even though it is stored safely, in the unfortunate event of a security breach, the shared secret will be visible to the attacker.
		The <span class="code">password</span> on the other hand is <strong>not</strong> stored by the API (only a cryptographic hash of it), so even in the event of a security breach, your password will never be visible to an attacker.
	</div>
	<div class="sectionBlock-section-text">
		To generate the signature, take the <span class="code">SHA1 HMAC</span> of the <span class="code">application/x-www-form-urlencoded</span> request content.
		<br />
		Then add the <span class="code">signature</span> variable to the <span class="code">application/x-www-form-urlencoded</span> content (by simply appending <span class="code">&amp;signature=THE_GENERATED_HMAC</span>).
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Example in <span class="code">PHP</span></div>
		<div class="sectionBlock-section-example-content">
$request = [
	'api'				=> 'apiName',
	'version'			=> '0.1',
	'service'			=> 'someService',
	'action'			=> 'someAction',
	// etc. (all the request variables)
];

// convert $request to application/x-www-form-urlencoded
$httpContent = http_build_query($request);
// generate the signature
$signature = hash_hmac('sha1', $httpContent, 'yourSharedSecret');
// add the signature variable to the request content
$newHttpContent = $content . '&amp;signature=' . $signature;
		</div>
	</div>
	<div class="sectionBlock-section-attention">
		An example for signing a <span class="code">multipart/form-data</span> request will be added when it's fully implemented
	</div>
</div>