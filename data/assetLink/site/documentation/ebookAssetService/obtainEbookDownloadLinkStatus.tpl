<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">ebookAssetService.obtainEbookDownloadLinkStatus</div>
	<div class="sectionBlock-section-text">
		Obtain the status and meta data of an ebookDownloadLink by the assetOwnershipId you where given by the <span class="code">ebookAssetService.registerEbookDownloadLink</span> action.
	</div>
	<div class="sectionBlock-section-actionPropertyList">
		<div class="sectionBlock-section-actionPropertyList-header">
			<div class="sectionBlock-section-actionPropertyList-header-title">Parameters</div>
			<label class="sectionBlock-section-actionPropertyList-header-types">
				<input class="sectionBlock-section-actionPropertyList-header-types-checkbox" type="checkbox" />
				<div class="sectionBlock-section-actionPropertyList-header-types-label css-label">Types</div>
				<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend css-toggle" onclick="return false;">
					<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-name">Id</div>
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-description">
								Binary v4 UUID (<span class="code">Hexadecimal</span> in formats with no binary support).
							</div>
						</div>
					</div>
				</div>
			</label>
		</div>
		<div class="sectionBlock-section-actionPropertyList-properties">
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">assetOwnershipId</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">Id</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						The return value of the <span class="code">ebookAssetService.registerEbookDownloadLink</span> action.
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="sectionBlock-section-actionResult">
		<div class="sectionBlock-section-actionResult-header">
			<div class="sectionBlock-section-actionResult-header-title">Result</div>
			<label class="sectionBlock-section-actionResult-header-types">
				<input class="sectionBlock-section-actionResult-header-types-checkbox" type="checkbox" />
				<div class="sectionBlock-section-actionResult-header-types-label css-label">Types</div>
				<div class="sectionBlock-section-actionResult-header-types-typeLegend css-toggle" onclick="return false;">
					<div class="sectionBlock-section-actionResult-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-name">TrueFalse</div>
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-description">
								A value of either true or false.
							</div>
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules">
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules-rule">true</div>
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules-rule">false</div>
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionResult-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-name">Url</div>
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-description">
								A valid URL.
							</div>
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-example">
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-example-title">Example:</div>
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-example-value">
									http://www.example.com/path/?some=thing
								</div>
							</div>
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules">
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules-rule">scheme://host/path?query</div>
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionResult-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-name">Isbn</div>
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-description">
								A 13 digit ISBN.
							</div>
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-example">
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-example-title">Example:</div>
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-example-value">
									9781234567890
								</div>
							</div>
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules">
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules-rule">starts with 978</div>
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules-rule">0-9</div>
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules-rule">length = 13</div>
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionResult-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-name">ShortText</div>
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-description">
								A short human readable text (i.e. not binary) in UTF-8 encoding. When not specified as optional, it most likely also cannot be an empty string.
								(<strong>Note:</strong> Most other textual types also carry these properties even when not explicitely mentioned).
							</div>
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules">
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules-rule">UTF-8</div>
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules-rule">not empty</div>
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules-rule">length &lt; 150</div>
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionResult-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-name">List&lt;..&gt;</div>
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-description">
								A list containing 0 or more values of the type specified between the &lt;angle brackets&gt;.
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionResult-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-name">Key</div>
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-description">
								A camelCased textual identifier of some entity.
							</div>
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-example">
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-example-title">Example:</div>
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-example-value">
									someEntityKey
								</div>
							</div>
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules">
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules-rule">a-z0-9</div>
								<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-rules-rule">camelCase</div>
							</div>
						</div>
					</div>
				</div>
			</label>
		</div>
		<div class="sectionBlock-section-actionResult-result">
			<div class="sectionBlock-section-actionResult-result-type">struct</div>
		</div>
		<div class="sectionBlock-section-actionResult-properties">
			<div class="sectionBlock-section-actionResult-properties-property">
				<div class="sectionBlock-section-actionResult-properties-property-specifics">
					<div class="sectionBlock-section-actionResult-properties-property-specifics-name">isChecked</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-type">TrueFalse</div>
				</div>
				<div class="sectionBlock-section-actionResult-properties-property-info">
					<div class="sectionBlock-section-actionResult-properties-property-info-description">
						If the link has been checked for validity.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionResult-properties-property">
				<div class="sectionBlock-section-actionResult-properties-property-specifics">
					<div class="sectionBlock-section-actionResult-properties-property-specifics-name">url</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-type">Url</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-optional">Optional</div>
				</div>
				<div class="sectionBlock-section-actionResult-properties-property-info">
					<div class="sectionBlock-section-actionResult-properties-property-info-description">
						The url of the download link that was given. Only included if the download link was invalid.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionResult-properties-property">
				<div class="sectionBlock-section-actionResult-properties-property-specifics">
					<div class="sectionBlock-section-actionResult-properties-property-specifics-name">hasBeenValid</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-type">TrueFalse</div>
				</div>
				<div class="sectionBlock-section-actionResult-properties-property-info">
					<div class="sectionBlock-section-actionResult-properties-property-info-description">
						If the download link has ever been valid. The link could have gone stale or became invalid since it was first registered.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionResult-properties-property">
				<div class="sectionBlock-section-actionResult-properties-property-specifics">
					<div class="sectionBlock-section-actionResult-properties-property-specifics-name">isbn</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-type">Isbn</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-optional">Optional</div>
				</div>
				<div class="sectionBlock-section-actionResult-properties-property-info">
					<div class="sectionBlock-section-actionResult-properties-property-info-description">
						The actual ISBN of the e-book. This value can be trusted because it's either obtained from the merchant (via the download link) or manually provided.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionResult-properties-property">
				<div class="sectionBlock-section-actionResult-properties-property-specifics">
					<div class="sectionBlock-section-actionResult-properties-property-specifics-name">metaAuthor</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-type">ShortText</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-optional">Optional</div>
				</div>
				<div class="sectionBlock-section-actionResult-properties-property-info">
					<div class="sectionBlock-section-actionResult-properties-property-info-description">
						The author found in the e-book meta data. Note that the meta data cannot be trusted.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionResult-properties-property">
				<div class="sectionBlock-section-actionResult-properties-property-specifics">
					<div class="sectionBlock-section-actionResult-properties-property-specifics-name">metaTitle</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-type">ShortText</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-optional">Optional</div>
				</div>
				<div class="sectionBlock-section-actionResult-properties-property-info">
					<div class="sectionBlock-section-actionResult-properties-property-info-description">
						The title found in the e-book meta data. Note that the meta data cannot be trusted.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionResult-properties-property">
				<div class="sectionBlock-section-actionResult-properties-property-specifics">
					<div class="sectionBlock-section-actionResult-properties-property-specifics-name">metaIsbns</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-type">List&lt;Isbn&gt;</div>
				</div>
				<div class="sectionBlock-section-actionResult-properties-property-info">
					<div class="sectionBlock-section-actionResult-properties-property-info-description">
						Any ISBNs found in the e-book meta data. Note that the meta data cannot be trusted.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionResult-properties-property">
				<div class="sectionBlock-section-actionResult-properties-property-specifics">
					<div class="sectionBlock-section-actionResult-properties-property-specifics-name">hasMetaCover</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-type">TrueFalse</div>
				</div>
				<div class="sectionBlock-section-actionResult-properties-property-info">
					<div class="sectionBlock-section-actionResult-properties-property-info-description">
						If a cover image was found in the e-book meta data that can be obtained via <span class="code">ebookAssetService.downloadEbookMetaCover</span>.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionResult-properties-property">
				<div class="sectionBlock-section-actionResult-properties-property-specifics">
					<div class="sectionBlock-section-actionResult-properties-property-specifics-name">invalidReasonKey</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-type">Key</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-optional">Optional</div>
				</div>
				<div class="sectionBlock-section-actionResult-properties-property-info">
					<div class="sectionBlock-section-actionResult-properties-property-info-description">
						The key specifying the reason the download link was invalid. Only included if the download link was invalid.
					</div>
					<div class="sectionBlock-section-actionResult-properties-property-info-enum">
						<div class="sectionBlock-section-actionResult-properties-property-info-enum-value">bookHasNoContent</div>
						<div class="sectionBlock-section-actionResult-properties-property-info-enum-value">brokenZip</div>
						<div class="sectionBlock-section-actionResult-properties-property-info-enum-value">downloadWriteFailed</div>
						<div class="sectionBlock-section-actionResult-properties-property-info-enum-value">drm</div>
						<div class="sectionBlock-section-actionResult-properties-property-info-enum-value">duplicateBook</div>
						<div class="sectionBlock-section-actionResult-properties-property-info-enum-value">duplicateUrl</div>
						<div class="sectionBlock-section-actionResult-properties-property-info-enum-value">fileTooSmall</div>
						<div class="sectionBlock-section-actionResult-properties-property-info-enum-value">invalidDownloadUrl</div>
						<div class="sectionBlock-section-actionResult-properties-property-info-enum-value">invalidEpub</div>
						<div class="sectionBlock-section-actionResult-properties-property-info-enum-value">invalidZip</div>
						<div class="sectionBlock-section-actionResult-properties-property-info-enum-value">tooManyRedirects</div>
						<div class="sectionBlock-section-actionResult-properties-property-info-enum-value">urlFailed</div>
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionResult-properties-property">
				<div class="sectionBlock-section-actionResult-properties-property-specifics">
					<div class="sectionBlock-section-actionResult-properties-property-specifics-name">isValid</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-type">TrueFalse</div>
				</div>
				<div class="sectionBlock-section-actionResult-properties-property-info">
					<div class="sectionBlock-section-actionResult-properties-property-info-description">
						If the download link was valid.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionResult-properties-property">
				<div class="sectionBlock-section-actionResult-properties-property-specifics">
					<div class="sectionBlock-section-actionResult-properties-property-specifics-name">ebookIsProcessed</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-type">TrueFalse</div>
				</div>
				<div class="sectionBlock-section-actionResult-properties-property-info">
					<div class="sectionBlock-section-actionResult-properties-property-info-description">
						If the e-book was processed for the specified assetOwnership to download via <span class="code">ebookAssetService.downloadEbook</span>.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionResult-properties-property">
				<div class="sectionBlock-section-actionResult-properties-property-specifics">
					<div class="sectionBlock-section-actionResult-properties-property-specifics-name">ebookIsDamaged</div>
					<div class="sectionBlock-section-actionResult-properties-property-specifics-type">TrueFalse</div>
				</div>
				<div class="sectionBlock-section-actionResult-properties-property-info">
					<div class="sectionBlock-section-actionResult-properties-property-info-description">
						If the e-book was damaged (e.g. not readable, missing pages).
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="sectionBlock-section-actionError">
		<div class="sectionBlock-section-actionError-header">
			<div class="sectionBlock-section-actionError-header-title">Error</div>
			<label class="sectionBlock-section-actionError-header-types">
				<input class="sectionBlock-section-actionError-header-types-checkbox" type="checkbox" />
				<div class="sectionBlock-section-actionError-header-types-label css-label">Types</div>
				<div class="sectionBlock-section-actionError-header-types-typeLegend css-toggle" onclick="return false;">
					<div class="sectionBlock-section-actionError-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionError-header-types-typeLegend-type-name">Id</div>
						<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info-description">
								Binary v4 UUID (<span class="code">Hexadecimal</span> in formats with no binary support).
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionError-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionError-header-types-typeLegend-type-name">struct</div>
						<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info-description">
								An anonymous structure to hold one or more named variables.
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionError-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionError-header-types-typeLegend-type-name">ShortText</div>
						<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info-description">
								A short human readable text (i.e. not binary) in UTF-8 encoding. When not specified as optional, it most likely also cannot be an empty string.
								(<strong>Note:</strong> Most other textual types also carry these properties even when not explicitely mentioned).
							</div>
							<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info-rules">
								<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info-rules-rule">UTF-8</div>
								<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info-rules-rule">not empty</div>
								<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info-rules-rule">length &lt; 150</div>
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionError-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionError-header-types-typeLegend-type-name">List&lt;..&gt;</div>
						<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info-description">
								A list containing 0 or more values of the type specified between the &lt;angle brackets&gt;.
							</div>
						</div>
					</div>
				</div>
			</label>
		</div>
		<div class="sectionBlock-section-actionError-errors">
			<div class="sectionBlock-section-actionError-errors-error">
				<div class="sectionBlock-section-actionError-errors-error-specifics">
					<div class="sectionBlock-section-actionError-errors-error-specifics-name">assetOwnershipNotFound</div>
					<div class="sectionBlock-section-actionError-errors-error-specifics-type">Id</div>
				</div>
				<div class="sectionBlock-section-actionError-errors-error-info">
					<div class="sectionBlock-section-actionError-errors-error-info-description">
						The given assetOwnershipId wasn't found.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionError-errors-error">
				<div class="sectionBlock-section-actionError-errors-error-specifics">
					<div class="sectionBlock-section-actionError-errors-error-specifics-name">permissionDenied</div>
				</div>
				<div class="sectionBlock-section-actionError-errors-error-info">
					<div class="sectionBlock-section-actionError-errors-error-info-description">
						You don't have the appropriate permission.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionError-errors-error">
				<div class="sectionBlock-section-actionError-errors-error-specifics">
					<div class="sectionBlock-section-actionError-errors-error-specifics-name">propertiesError</div>
				</div>
				<div class="sectionBlock-section-actionError-errors-error-info">
					<div class="sectionBlock-section-actionError-errors-error-info-description">
						Wrapper indicating errors for one or more properties / parameters. See the reasons.
					</div>
				</div>
				<div class="sectionBlock-section-actionError-errors-error-reasons">
					<div class="sectionBlock-section-actionError-errors-error-reasons-title">Reasons</div>
					
					<div class="sectionBlock-section-actionError-errors-error">
						<div class="sectionBlock-section-actionError-errors-error-specifics">
							<div class="sectionBlock-section-actionError-errors-error-specifics-name">propertyIsRequired</div>
							<div class="sectionBlock-section-actionError-errors-error-specifics-type">struct</div>
						</div>
						<div class="sectionBlock-section-actionError-errors-error-info">
							<div class="sectionBlock-section-actionError-errors-error-info-description">
								You failed to include the specified property which is required.
							</div>
						</div>
						<div class="sectionBlock-section-actionError-errors-error-children">
							<div class="sectionBlock-section-actionError-errors-error-children-title">Info</div>
							<div class="sectionBlock-section-actionError-properties-property">
								<div class="sectionBlock-section-actionError-properties-property-specifics">
									<div class="sectionBlock-section-actionError-properties-property-specifics-name">propertyName</div>
									<div class="sectionBlock-section-actionError-properties-property-specifics-type">ShortText</div>
								</div>
								<div class="sectionBlock-section-actionError-properties-property-info">
									<div class="sectionBlock-section-actionError-properties-property-info-description">
										Name of the property.
									</div>
								</div>
							</div>
							<div class="sectionBlock-section-actionError-properties-property">
								<div class="sectionBlock-section-actionError-properties-property-specifics">
									<div class="sectionBlock-section-actionError-properties-property-specifics-name">path</div>
									<div class="sectionBlock-section-actionError-properties-property-specifics-type">List&lt;ShortText&gt;</div>
								</div>
								<div class="sectionBlock-section-actionError-properties-property-info">
									<div class="sectionBlock-section-actionError-properties-property-info-description">
										Names of the parent structs this property was a child of.
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Example request</div>
		<div class="sectionBlock-section-example-content">
{
	assetOwnershipId: 'dc8ac37a8ef54e1381cbe0c4628543c0'
}
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Example response</div>
		<div class="sectionBlock-section-example-content">
{
	result: {
		isChecked			: true,
		url					: null,
		hasBeenValid		: true,
		isbn				: null,
		metaAuthor			: null,
		metaTitle			: null,
		metaIsbns			: [
			'9781234567890'
			'9781234567809'
		],
		hasMetaCover		: true,
		invalidReasonKey	: null,
		isValid				: true,
		ebookIsProcessed	: false,
		ebookIsDamaged		: false
	}
}
		</div>
	</div>
</div>