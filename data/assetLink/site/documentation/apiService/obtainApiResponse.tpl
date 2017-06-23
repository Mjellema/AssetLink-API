<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">apiService.obtainApiResponse</div>
	<div class="sectionBlock-section-text">
		Obtain an API response you missed because of a connection failure during the request.
	</div>
	<div class="sectionBlock-section-actionPropertyList">
		<div class="sectionBlock-section-actionPropertyList-header">
			<div class="sectionBlock-section-actionPropertyList-header-title">Parameters</div>
			<label class="sectionBlock-section-actionPropertyList-header-types">
				<input class="sectionBlock-section-actionPropertyList-header-types-checkbox" type="checkbox" />
				<div class="sectionBlock-section-actionPropertyList-header-types-label css-label">Types</div>
				<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend css-toggle" onclick="return false;">
					<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type">
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-name">Binary</div>
						<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info">
							<div class="sectionBlock-section-actionPropertyList-header-types-typeLegend-type-info-description">
								Raw bytes (<span class="code">Base64</span> in formats with no binary support)
							</div>
						</div>
					</div>
				</div>
			</label>
		</div>
		<div class="sectionBlock-section-actionPropertyList-properties">
			<div class="sectionBlock-section-actionPropertyList-properties-property">
				<div class="sectionBlock-section-actionPropertyList-properties-property-specifics">
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-name">requestIdentifier</div>
					<div class="sectionBlock-section-actionPropertyList-properties-property-specifics-type">Binary</div>
				</div>
				<div class="sectionBlock-section-actionPropertyList-properties-property-info">
					<div class="sectionBlock-section-actionPropertyList-properties-property-info-description">
						The same value you used to execute the action you're trying to get the response of.
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="sectionBlock-section-actionResult">
		<div class="sectionBlock-section-actionResult-header">
			<div class="sectionBlock-section-actionResult-header-title">Result</div>
		</div>
		<div class="sectionBlock-section-actionResult-result">
			<div class="sectionBlock-section-actionResult-result-type">*</div>
			<div class="sectionBlock-section-actionResult-result-description">
				Anything.
				<br />
				The result is based on the action that was previously executed with the specified requestIdentifier.
			</div>
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Example request</div>
		<div class="sectionBlock-section-example-content">
{
	requestIdentifier: 'd0f38b0f82d44c99bb9bdbb1de6f8ebc'
}
		</div>
	</div>
	<div class="sectionBlock-section-example">
		<div class="sectionBlock-section-example-title">Example response of an action that returns an Id</div>
		<div class="sectionBlock-section-example-content">
{
	result: 'dc8ac37a8ef54e1381cbe0c4628543c0'
}
		</div>
	</div>
</div>