<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">assetService.transferAssetOwnership</div>
	<div class="sectionBlock-section-text">
		Transfer the ownership of an asset to a new owner.
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
					<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-name">struct</div>
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-description">
								An anonymous structure to hold one or more named variables.
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-name">Email</div>
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-description">
								A valid e-mail address.
							</div>
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-example">
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-example-title">Example:</div>
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-example-value">
									info@example.com
								</div>
							</div>
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules">
								<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-rules-rule">local@domain.tld</div>
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
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">newOwner</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">struct</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						Struct defining the new / current owner of the asset.
					</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-children">
					<div class="sectionBlock-section-actionPropertyList-properties-property-children-title">Properties</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property">
						<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
							<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">email</div>
							<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">Email</div>
						</div>
						<div class="sectionBlock-section-actionPropertyList-properties-property-info">
							<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
								Email address of the new / current owner.
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property">
						<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
							<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">personDetails</div>
							<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">struct</div>
							<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-optional">
								OptionalChoice<br/>
								personDetails|companyDetails
							</div>
						</div>
						<div class="sectionBlock-section-actionPropertyList-properties-property-info">
							<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
								Struct defining the owner as a person.
							</div>
						</div>
						<div class="sectionBlock-section-actionPropertyList-properties-property-children">
							<div class="sectionBlock-section-actionPropertyList-properties-property-children-title">Properties</div>
							<div class="sectionBlock-section-actionPropertyList-properties-property">
								<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
									<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">firstName</div>
									<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">ShortText</div>
								</div>
								<div class="sectionBlock-section-actionPropertyList-properties-property-info">
									<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
										First / given name of the person.
									</div>
								</div>
							</div>
							<div class="sectionBlock-section-actionPropertyList-properties-property">
								<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
									<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">lastName</div>
									<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">ShortText</div>
								</div>
								<div class="sectionBlock-section-actionPropertyList-properties-property-info">
									<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
										Last / family name of the person.
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property">
						<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
							<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">companyDetails</div>
							<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">struct</div>
							<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-optional">
								OptionalChoice<br/>
								personDetails|companyDetails
							</div>
						</div>
						<div class="sectionBlock-section-actionPropertyList-properties-property-info">
							<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
								Struct defining the owner as a company.
							</div>
						</div>
						<div class="sectionBlock-section-actionPropertyList-properties-property-children">
							<div class="sectionBlock-section-actionPropertyList-properties-property-children-title">Properties</div>
							<div class="sectionBlock-section-actionPropertyList-properties-property">
								<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
									<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">name</div>
									<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">ShortText</div>
								</div>
								<div class="sectionBlock-section-actionPropertyList-properties-property-info">
									<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
										Name of the company.
									</div>
								</div>
							</div>
						</div>
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
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-name">Id</div>
						<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionResult-header-types-typeLegend-type-info-description">
								Binary v4 UUID (<span class="code">Hexadecimal</span> in formats with no binary support).
							</div>
						</div>
					</div>
				</div>
			</label>
		</div>
		<div class="sectionBlock-section-actionResult-result">
			<div class="sectionBlock-section-actionResult-result-type">Id</div>
			<div class="sectionBlock-section-actionResult-result-description">
				The new assetOwnership id.
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
					<div class="sectionBlock-section-actionError-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionError-header-types-typeLegend-type-name">*</div>
						<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info-description">
								Any type.
							</div>
						</div>
					</div>
					<div class="sectionBlock-section-actionError-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionError-header-types-typeLegend-type-name">Binary</div>
						<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionError-header-types-typeLegend-type-info-description">
								Raw bytes (<span class="code">Base64</span> in formats with no binary support)
							</div>
						</div>
					</div>
				</div>
			</label>
		</div>
		<div class="sectionBlock-section-actionError-errors">
			<div class="sectionBlock-section-actionError-errors-error">
				<div class="sectionBlock-section-actionError-errors-error-specifics">
					<div class="sectionBlock-section-actionError-errors-error-specifics-name">assetOwnershipAlreadyTransfered</div>
					<div class="sectionBlock-section-actionError-errors-error-specifics-type">Id</div>
				</div>
				<div class="sectionBlock-section-actionError-errors-error-info">
					<div class="sectionBlock-section-actionError-errors-error-info-description">
						The given assetOwnershipId has already been transfered.
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
					<div class="sectionBlock-section-actionError-errors-error">
						<div class="sectionBlock-section-actionError-errors-error-specifics">
							<div class="sectionBlock-section-actionError-errors-error-specifics-name">propertyIsInvalid</div>
							<div class="sectionBlock-section-actionError-errors-error-specifics-type">struct</div>
						</div>
						<div class="sectionBlock-section-actionError-errors-error-info">
							<div class="sectionBlock-section-actionError-errors-error-info-description">
								The value of the specified property was invalid. See the possible reasons as to why.
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
							<div class="sectionBlock-section-actionError-properties-property">
								<div class="sectionBlock-section-actionError-properties-property-specifics">
									<div class="sectionBlock-section-actionError-properties-property-specifics-name">value</div>
									<div class="sectionBlock-section-actionError-properties-property-specifics-type">*</div>
								</div>
								<div class="sectionBlock-section-actionError-properties-property-info">
									<div class="sectionBlock-section-actionError-properties-property-info-description">
										The value that was given (and was deemed invalid).
									</div>
								</div>
							</div>
						</div>
						<div class="sectionBlock-section-actionError-errors-error-reasons">
							<div class="sectionBlock-section-actionError-errors-error-reasons-title">Reasons</div>
							<div class="sectionBlock-section-actionError-errors-error">
								<div class="sectionBlock-section-actionError-errors-error-specifics">
									<div class="sectionBlock-section-actionError-errors-error-specifics-name">tooLong</div>
									<div class="sectionBlock-section-actionError-errors-error-specifics-type">Binary</div>
								</div>
								<div class="sectionBlock-section-actionError-errors-error-info">
									<div class="sectionBlock-section-actionError-errors-error-info-description">
										The value was too long.
									</div>
								</div>
							</div>
							<div class="sectionBlock-section-actionError-errors-error">
								<div class="sectionBlock-section-actionError-errors-error-specifics">
									<div class="sectionBlock-section-actionError-errors-error-specifics-name">tooShort</div>
									<div class="sectionBlock-section-actionError-errors-error-specifics-type">Binary</div>
								</div>
								<div class="sectionBlock-section-actionError-errors-error-info">
									<div class="sectionBlock-section-actionError-errors-error-info-description">
										The value was too short (probably empty).
									</div>
								</div>
							</div>
							<div class="sectionBlock-section-actionError-errors-error">
								<div class="sectionBlock-section-actionError-errors-error-specifics">
									<div class="sectionBlock-section-actionError-errors-error-specifics-name">emailInvalid</div>
									<div class="sectionBlock-section-actionError-errors-error-specifics-type">Binary</div>
								</div>
								<div class="sectionBlock-section-actionError-errors-error-info">
									<div class="sectionBlock-section-actionError-errors-error-info-description">
										The value was not a valid e-mail address.
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
	assetOwnershipId	: 'dc8ac37a8ef54e1381cbe0c4628543c0',
	previousOwner		: {
		email				: 'sam@doe.tld',
		personDetails		: {
			firstName			: 'Sam',
			lastName			: 'Doe'
		}
	}
}
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Example response</div>
		<div class="sectionBlock-section-example-content">
{
	result: 'eb5f117c27744c75b9a900b53ff24d38'
}
		</div>
	</div>
</div>