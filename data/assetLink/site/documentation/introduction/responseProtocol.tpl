<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Response protocol</div>
	<div class="sectionBlock-section-text">
		The API always responds with a <span class="code">struct</span> (except for responses in <span class="code">binary</span> format).
		<br />
		This struct contains <strong>one</strong> of the following properties:
		<ul class="list">
		 	<li><span class="code">result</span> (the return value of the action)</li>
		 	<li><span class="code">error</span> (an error struct when the action was unable to do what it was asked to do)</li>
		 	<li><span class="code">protocolError</span> (an error struct when the protocol determined it was unable to execute the action)</li>
		 	<li><span class="code">internalError</span> (an error struct when something went wrong internally on our side)</li>
		 </ul>
	</div>
</div>
<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Result</div>
	<div class="sectionBlock-section-text">
		Result values can be of any type, that is, the type varies depending on the action called. See the documentation of the actions for the possible return values.
	</div>
	<div class="sectionBlock-section-text">
		Note that an action always returns a <strong>single</strong> result value (which however may be a list or struct containing several other values).
	</div>
</div>
<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Error</div>
	<div class="sectionBlock-section-text">
		Errors are used to signal the requested action could not be performed. This almost always indicates an error <strong>your</strong> part.
		You most likely asked to perform an action either with an incorrect argument, or which you do not have sufficient permissions for.
	</div>
	<div class="sectionBlock-section-text">
		Error structs all have the same structure, but can contain different types of data depending on the error.<br />
		The error struct always includes a <span class="code">key</span> property which is a textual identifier indicating the type of error.
		An action can throw different types of errors. See the documentation of the actions for the possible errors.
		<br /><br />
		The error struct may also include an <span class="code">info</span> property that provides extra data or information about the error. The type of this property depends on the type of error, e.g. an <span class="code">emailInvalid</span> error could possibly contain the value of the passed in email argument in its <span class="code">info</span> property.
		<br /><br />
		The error struct may also include a <span class="code">reasons</span> property, which is a list (array) containing sub-error structs that provide more information on what the underlying reason of the error was.
	</div>
	<div class="sectionBlock-section-text">
		Note that an action always returns a <strong>single</strong> error struct. A single error may however be used as a 'wrapper' to contain several reasons (most actions use this approach to be able to signal failed validation on multiple arguments).
	</div>
</div>
<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Protocol error</div>
	<div class="sectionBlock-section-text">
		A <span class="code">protocolError</span> is a regular error struct, but indicates an error that does not belong to the specific requested action. They are thrown by the protocol handling your request.
	</div>
	<div class="sectionBlock-section-text">
		Possible protocol errors are:
		<ul class="list">
			<li><span class="code">requestFormatNotSupported, Key</span></li>
			<li><span class="code">requestInvalid</span></li>
			<li><span class="code">responseFormatNotSupported, Key</span></li>
			<li><span class="code">responseFormatNotSupportedForAction</span> (for binary responseFormat without binary response)</li>
			<li><span class="code">accessDenied</span></li>
			<li><span class="code">signatureInvalid</span></li>
			<li><span class="code">timeout, (loginName:ShortText, timeout:DateInterval)</span></li>
	 		<li><span class="code">credentialsInvalid, (loginName:ShortText, timeout:DateInterval)</span></li>
			<li><span class="code">serviceNotFound, Key</span></li>
			<li><span class="code">actionNotFound, Key</span></li>
		</ul>
	</div>
</div>
<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Internal error</div>
	<div class="sectionBlock-section-text">
		An <span class="code">internalError</span> means something went wrong on our side and we couldn't execute the action (even though you might have supplied all the correct arguments). This could be anything, for instance a bug, a full harddisk, a network failure, etc.
		There's really nothing you can do on your end to prevent these (unlike the other errors), so the only thing you can do is either try the same request again in the hopes it was a temporary glitch, or stop, fail and go home.
	</div>
	<div class="sectionBlock-section-attention">
		Although we try to log all internal errors, the system might in some cases be unable to do so. Please notify us in the event you receive an <span class="code">internalError</span>.
	</div>
</div>
<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Examples of successful responses</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Return value is a <span class="code">TId</span> (a UUID encoded in Hexadecimal)</div>
		<div class="sectionBlock-section-example-content">
{
	result: 'f0b5f34b9d6e492aa87f5416ef03d86e'
}
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Return value is a <span class="code">struct</span> of type <span class="code">(url:Url, isValid:TrueFalse)</span></div>
		<div class="sectionBlock-section-example-content">
{
	result: {
		url		: 'http://www.example.com/some/url/',
		isValid	: false
	}
}
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Action succeeded, but doesn't have a return value</div>
		<div class="sectionBlock-section-example-content">
{
	result: null
}
		</div>
	</div>
	<div class="sectionBlock-section-attention">
		Note that if this action did <strong>not</strong> succeed, the response wouldn't contain a <span class="code">result</span> property, but an <span class="code">error</span> property instead.
	</div>
</div>
<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Examples of error responses</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">API action couldn't be found</div>
		<div class="sectionBlock-section-example-content">
{
	protocolError: {
		key: 'actionNotFound'
	}
}
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Several action arguments where incorrect (invalid or missing), they are wrapped in a single 'propertiesError'</div>
		<div class="sectionBlock-section-example-content">
{
	error: {
		key			: 'propertiesError',
		info		: null,
		reasons		: [
			{
				key		: 'propertyIsInvalid',
				info	: {
					propertyName	: 'email',
					path			: [],
					value			: 'info@nothing'
				},
				reasons	: [
					{
						key: 'emailInvalid'
					}
				]
			},
			{
				key		: 'propertyIsInvalid',
				info	: {
					propertyName	: 'firstName',
					path			: ['personDetails'],
					value			: ''
				},
				reasons	: [
					{
						key: 'tooShort'
					}
				]
			},
			{
				key		: 'propertyIsRequired',
				info	: {
					propertyName	: 'lastName',
					path			: ['personDetails']
				},
				reasons	: []
			}
		]
	}
}
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Error indicating internal failure on our side</div>
		<div class="sectionBlock-section-example-content">
{
	internalError: {
		key: 'internalError'
	}
}
		</div>
	</div>
</div>