<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">ebookAssetService.downloadEbook</div>
	<div class="sectionBlock-section-text">
		Download the processed e-book file.
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
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-name">Binary</div>
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-description">
								Raw bytes (<span class="code">Base64</span> in formats with no binary support)
							</div>
						</div>
					</div>
				</div>
			</label>
		</div>
		<div class="sectionBlock-section-actionResult-result">
			<div class="sectionBlock-section-actionResult-result-type">Binary</div>
			<div class="sectionBlock-section-actionResult-result-description">
				The e-book file
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
					<div class="sectionBlock-section-actionError-errors-error-specifics-name">ebookNotFound</div>
					<div class="sectionBlock-section-actionError-errors-error-specifics-type">Id</div>
				</div>
				<div class="sectionBlock-section-actionError-errors-error-info">
					<div class="sectionBlock-section-actionError-errors-error-info-description">
						The asset wasn't an e-book.
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionError-errors-error">
				<div class="sectionBlock-section-actionError-errors-error-specifics">
					<div class="sectionBlock-section-actionError-errors-error-specifics-name">ebookNotProcessed</div>
					<div class="sectionBlock-section-actionError-errors-error-specifics-type">Id</div>
				</div>
				<div class="sectionBlock-section-actionError-errors-error-info">
					<div class="sectionBlock-section-actionError-errors-error-info-description">
						The e-book hasn't been processed yet (try again later).
					</div>
				</div>
			</div>
			<div class="sectionBlock-section-actionError-errors-error">
				<div class="sectionBlock-section-actionError-errors-error-specifics">
					<div class="sectionBlock-section-actionError-errors-error-specifics-name">assetIsLent</div>
					<div class="sectionBlock-section-actionError-errors-error-specifics-type">Id</div>
				</div>
				<div class="sectionBlock-section-actionError-errors-error-info">
					<div class="sectionBlock-section-actionError-errors-error-info-description">
						The asset is lent out (you can't have access to it while it's lent).
					</div>
				</div>
			</div>
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
	result: 'YmluYXJ5'
}
		</div>
	</div>
</div>