<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Request protocol</div>
	<div class="sectionBlock-section-text">
		The API can be accessed via HTTP and uses an RPC protocol (Remote Procedure Call).<br />
		It currently lives on a single endpoint:
		<span class="code">https://yourhost/api/</span><br />
		The name of the API is: <span class="code">assetLink</span><br />
		And the current version is: <span class="code">0.1</span>
	</div>
	<div class="sectionBlock-section-text">
		All requests use the HTTP <span class="code">POST</span> method.<br />
		Request data is passed in the request body in either <span class="code">application/x-www-form-urlencoded</span> or <span class="code">multipart/form-data</span> encoding.<br />
		Any textual values must be in <span class="code">UTF-8</span> character encoding.<br />
	</div>
	<div class="sectionBlock-section-attention">
		Experimental support for <span class="code">json</span> and <span class="code">binary</span> request and response formats is also available (see Formats).<br />
		Note that support for <span class="code">multipart/form-data</span> (i.e. for sending files) isn't completely implemented yet
	</div>
	<div class="sectionBlock-section-text">
		The protocol uses the following parameters:
	</div>
	<div class="sectionBlock-section-actionPropertyList">
		<div class="sectionBlock-section-actionPropertyList-header">
			<div class="sectionBlock-section-actionPropertyList-header-title">Parameters</div>
			<label class="sectionBlock-section-actionPropertyList-header-types">
				<input class="sectionBlock-section-actionPropertyList-header-types-checkbox" type="checkbox" />
				<div class="sectionBlock-section-actionPropertyList-header-types-label css-label">Types</div>
				<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend css-toggle" onclick="return false;">
					<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-name">Key</div>
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-description">
								A camelCased textual identifier of some entity.
							</div>
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-example">
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-example-title">Example:</div>
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-example-value">
									someEntityKey
								</div>
							</div>
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules">
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules-rule">a-z0-9</div>
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules-rule">camelCase</div>
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-name">PositiveNumber</div>
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-description">
								Any number (whole or decimal) greater than 0.0
							</div>
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules">
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules-rule">&gt; 0.0</div>
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-name">ShortText</div>
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-description">
								A short human readable text (i.e. not binary) in UTF-8 encoding. When not specified as optional, it most likely also cannot be an empty string.
								(<strong>Note:</strong> Most other textual types also carry these properties even when not explicitely mentioned).
							</div>
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules">
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules-rule">UTF-8</div>
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules-rule">not empty</div>
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules-rule">length &lt; 150</div>
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-name">Binary</div>
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-description">
								Raw bytes (<span class="code">Base64</span> in formats with no binary support)
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-name">Hexadecimal</div>
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-description">
								A value coded in lowercase hexadecimal notation.
							</div>
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules">
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules-rule">a-f0-9</div>
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules-rule">lowercase</div>
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-name">struct</div>
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-description">
								An anonymous structure to hold one or more named variables.
							</div>
						</div>
					</div>
				</div>
			</label>
		</div>
		<div class="sectionBlock-section-actionPropertyList-properties">
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">responseFormat</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">Key</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-optional">Optional</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						The format you would like to receive the response in. (Defaults to <span class="code">wwwFormUrlEncoded</span>)
					</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-enum">
						<div class="sectionBlock-section-actionPropertyList-properties-property-info-enum-value">wwwFormUrlEncoded</div>
						<div class="sectionBlock-section-actionPropertyList-properties-property-info-enum-value">json</div>
						<div class="sectionBlock-section-actionPropertyList-properties-property-info-enum-value">binary</div>
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">api</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">Key</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						The identifying key (name) of the API.
					</div>
					<!-- COMMENT:
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-enum">
						<div class="sectionBlock-section-actionPropertyList-properties-property-info-enum-value">asd</div>
						<div class="sectionBlock-section-actionPropertyList-properties-property-info-enum-value">asd</div>
						<div class="sectionBlock-section-actionPropertyList-properties-property-info-enum-value">asd</div>
					</div>
					-->
				</div>
			</div>
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">version</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">PositiveNumber</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						The version of the API you wish to use.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">service</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">Key</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						The service you're calling.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">action</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">Key</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						The action (procedure / function) you want to execute on the service.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">loginName</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">ShortText</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						Your system login name.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">password</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">ShortText</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						Your system password (a.k.a. API key).
						<br />
						See <a class="link" href="{documentation/authentication/ KEY url}">Authentication</a> for more info.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">data</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">struct</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						The data you want to pass to the action (function parameters).
						The variables of the struct are specified by the specific service actions.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">requestIdentifier</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">Binary</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-optional">Optional</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						An identifier of your choice to be able to re-request the result of the action in case of connection failure (usually a random UUID).
						<br />
						See <a class="link" href="{documentation/connection-failure/ KEY url}">Connection failure</a> for more info.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">signature</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">Hexadecimal</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						An <span class="code">SHA1</span> keyed hash message authentication code (HMAC) of all the request variables using a shared secret.
						<br />
						See <a class="link" href="{documentation/authentication/ KEY url}">Authentication</a> for more info.
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="sectionBlock-section-text">
		All parameters are required except when specified as <span class="actionPropertyList-optional">Optional</span> (they may be omitted).
		<br />
		Parameters specified as <span class="actionPropertyList-optional">Choice</span> <strong>require</strong> exactly <strong>one</strong> of the given choices to be present.
		<br />
		Parameters specified as <span class="actionPropertyList-optional">OptionalChoice</span> <strong>may</strong> have <strong>one</strong> of the given choices be present (or may be omitted entirely).
		<!-- COMMENT:
		<br />
		Some <strong>result</strong> values (part of the response, not the request) may be specified as <span class="actionPropertyList-optional">OptionalEmpty</span>. This is a temporary situation for when optional values are returned in the result, but are <strong>empty</strong> instead of omitted.
		-->
	</div>
	<div class="sectionBlock-section-attention">
		Note that when an <span class="actionPropertyList-optional">Optional</span> parameter is not omitted from the request, it must have a valid value
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">An example of how the protocol data looks in <span class="code">JSON</span></div>
		<div class="sectionBlock-section-example-content">
{
	api			: 'apiName',
	version		: 0.1,
	service		: 'someService',
	action		: 'someAction',
	loginName	: 'yourSite',
	password	: 'NA46YfLvs7v4TSBkwqpfzgcXktGBcyNn9QB2hDZKEdgGTnbDr5fmBcqXbhwhLsnY',
	data		: {
		someArgument		: 'someValue',
		otherArgument		: 'otherValue',
		someStructArgument	: {
			innerValue			: 'something',
			otherInnerValue		: 'otherThing',
		},
	},
	requestIdentifier	: 'd0f38b0f82d44c99bb9bdbb1de6f8ebc',
	signature			: 'ce87d7f1be24b07f8ad1c4f05a19c522ea24888a',
}
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Which converts to the following in <span class="code">application/x-www-form-urlencoded</span></div>
		<div class="sectionBlock-section-example-content">
api=apiName&amp;version=0.1&amp;service=someService&amp;action=someAction&amp;loginName=yourSite&amp;password=NA46YfLvs7v4TSBkwqpfzgcXktGBcyNn9QB2hDZKEdgGTnbDr5fmBcqXbhwhLsnY&amp;data%5BsomeArgument%5D=someValue&amp;data%5BotherArgument%5D=otherValue&amp;data%5BsomeStructArgument%5D%5BinnerValue%5D=something&amp;data%5BsomeStructArgument%5D%5BotherInnerValue%5D=otherThing&amp;requestIdentifier=d0f38b0f82d44c99bb9bdbb1de6f8ebc&amp;signature=ce87d7f1be24b07f8ad1c4f05a19c522ea24888a
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">If we make that a little more readable, we can see the structure more clearly</div>
		<div class="sectionBlock-section-example-content">
api			= apiName
version		= 0.1
service		= someService
action		= someAction
loginName	= yourSite
password	= NA46YfLvs7v4TSBkwqpfzgcXktGBcyNn9QB2hDZKEdgGTnbDr5fmBcqXbhwhLsnY
data[someArgument]		= someValue
data[otherArgument]		= otherValue
data[someStructArgument][innerValue]		= something
data[someStructArgument][otherInnerValue]	= otherThing
requestIdentifier	= d0f38b0f82d44c99bb9bdbb1de6f8ebc
signature			= ce87d7f1be24b07f8ad1c4f05a19c522ea24888a
		</div>
	</div>
</div>
<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Format(s)</div>
	<div class="sectionBlock-section-text">
		As you can see, the <span class="code">application/x-www-form-urlencoded</span> format is very simple, but it's nonetheless quite powerful.
		<span class="code">multipart/form-data</span> is a tiny bit more complex, but still very simple.<br />
		These 2 formats are probably the <strong>most used</strong> formats on the world wide web, because almost every single <span class="code">HTML</span> <span class="code">&lt;form&gt;</span> on the planet uses them.
		In fact, <span class="code">application/x-www-form-urlencoded</span> is the default encoding used by HTML forms.
		And if you ever wanted to upload files with an HTML form, you would use <span class="code">&lt;form enctype="multipart/form-data"&gt;</span>.
	</div>
	<div class="sectionBlock-section-text">
		So both encodings are well established standards and have become somewhat of a natural part of HTTP.
	</div>
	<div class="sectionBlock-section-subTitle">When to use which?</div>
	<div class="sectionBlock-section-text">
		As is the case with HTML forms, both formats can be used interchangeably.
		<br />
		<span class="code">application/x-www-form-urlencoded</span> however encodes all values, which costs (a little) in terms of performance and also means <span class="code">binary</span> values will increase in size.
		<br />
		<span class="code">multipart/form-data</span> doesn't encode anything, so <span class="code">binary</span> values can be sent as-is without an increase in size. It does however include a boundary to separate the different values you're sending, so it has a little bit of overhead in each request.
		<br /><br />
		These characteristics make <span class="code">application/x-www-form-urlencoded</span> suitable for most requests.
		Use <span class="code">multipart/form-data</span> when sending (large) binary values (e.g. files).
	</div>
	<div class="sectionBlock-section-subTitle">But what about JSON?</div>
	<div class="sectionBlock-section-text">
		JSON is a nice format, but originating from a programming language, its main feature is that it's easy to read and understand by humans.
		It's relatively young and became a popular alternative to <span class="code">XML</span> because it's simpler and less verbose.
		However, the use of XML as a data format (as opposed to simpler encodings) had its reasons, namely XML had the possibility of defining a schema to help constrain the structure and content of the data.
		This all turned out to be far from perfect and as it goes in these kinds of situations, sentiment drifted in the other direction in favor if simpler solutions.
	</div>
	<div class="sectionBlock-section-text">
		It's around that time that JavaScript became all the rage, so whilst everyone jumped on the JavaScript / AJAX / JSON bandwagon, existing (simple) solutions where mostly forgotten.
		But JSON has a few limitations, one of which stems from its limited set of data types, which means most values need to be encoded as <span class="code">string</span> values.
		But JSON requires all values to be in <span class="code">UTF-8</span> character encoding, so in order to send plain binary data (like a file), a second encoding needs to be used to convert binary into valid UTF-8.
		The format doesn't help you with this, so you need to manually encode those values using a separate encoding such as <span class="code">Base64</span>, which then needs to be separately decoded at the receiving end (i.e. when processing the API request).
		Because the format doesn't have any support for these secondary encodings, all this has to be documented and implemented by both the client and the server.
	</div>
	<div class="sectionBlock-section-text">
		The <span class="code">application/x-www-form-urlencoded</span> and <span class="code">multipart/form-data</span> formats, being simpler, have no notion of data types and character encodings, they simply encode data, which can by of any type, even <span class="code">binary</span>.
		And because the API is meant to be used by computers, there is little benefit in using a more complicated and limiting format only because it's more readable.
	</div>
	<div class="sectionBlock-section-subTitle">Then why support JSON at all?</div>
	<div class="sectionBlock-section-text">
		Because it has become the de facto standard, and standards are a Good Thing™.<br />
		So if you want to use JSON as your request format, set your HTTP Content-Type header to <span class="code">application/json</span>.<br />
		And if you want to receive JSON responses, set the <span class="code">responseFormat</span> to <span class="code">json</span>.
	</div>
	<div class="sectionBlock-section-attention">
		Note that even though <span class="code">JSON</span> isn't the encouraged format, the examples will be shown in <span class="code">JSON</span>, precisely because it's more readable to humans.
	</div>
	<div class="sectionBlock-section-subTitle">And what is the binary response format?</div>
	<div class="sectionBlock-section-text">
		The <span class="code">binary</span> format lacks any form of encoding and just sends the actions' return value as-is.
		It is therefore only available for actions that return a single value (i.e. not a struct).
		This makes it possible to send large binary values (such as files) without having to encode/decode the entire binary.
		<br />
		The response Content-Type is <span class="code">application/octet-stream</span>.
		<br />
		The downside is that the reponse itself cannot contain any extra information. For this reason any errors are signalled using the following HTTP headers:
		<ul class="list">
			<li><span class="code">X-ProtocolError</span></li>
			<li><span class="code">X-Error</span></li>
		</ul>
	</div>
	<div class="sectionBlock-section-attention">
		Note that the <span class="code">binary</span> format lacks certain features such as response signatures making it less secure.
	</div>
</div>
<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Types</div>
	<div class="sectionBlock-section-text">
		Most data formats have only very limited support for data types and the <span class="code">application/x-www-form-urlencoded</span> encoding is solely a key-value pair format and therefore doesn't have a notion of data types at all. You could say every value is just a <span class="code">string</span> or even more accurately, every value is <span class="code">binary</span>.
	</div>
	<div class="sectionBlock-section-text">
		Note that it does have support for a compound type (struct / object) so you could say a variable is either a <span class="code">binary</span> value or a <span class="code">structure</span> containing other variables.
	</div>
	<div class="sectionBlock-section-text">
		This lack of data types in the format means it's not really beneficial to talk about the parameter types in terms of the low level types that may or may not be supported by some format, like <span class="code">string</span> or <span class="code">integer</span>, since you can only pass in <span class="code">binary</span> values anyway.
		Additionally, for formats where those low level types would be available, they will only cover the most basic requirements the value must meet.
		Therefore it's more useful to specify types as high level semantic types that should convey more meaning than simply saying a <span class="code">downloadLink</span> parameter should be of type <span class="code">string</span>, when it should actually be a valid <span class="code">Url</span>.
	<div class="sectionBlock-section-text">
		You can always view more details about the type definitions by clicking on the <span class="actionPropertyList-typesButton">Types</span> button.
	</div>
</div>