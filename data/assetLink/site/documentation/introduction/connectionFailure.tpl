<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Connection failure</div>
	<div class="sectionBlock-section-text">
		There is a special purpose protocol parameter that some actions support, which is <span class="code">requestIdentifier</span>.
		<br />
		It can be used in case the connection between your system and the API failed before you were able to obtain the response.
		Some actions cannot be (safely) executed multiple times, so it's unwise to simply re-do the same request. If you used a <span class="code">requestIdentifier</span>, you can try to obtain the response without executing the action again.
	</div>
	<div class="sectionBlock-section-text">
		You can choose the value for the <span class="code">requestIdentifier</span> yourself, but we recommend you use a randomly generated UUID (v4) in <span class="code">binary</span> form.
	</div>
	<div class="sectionBlock-section-attention">
		 UUIDs are usually represented in 32 character hex format with 4 separating dashes e.g. f0d595a7-9d8d-4470-b228-40fb01682b38. As-is that would take up 36 bytes. You can convert that hex to plain binary which would only make it 16 bytes.
	</div>
	<div class="sectionBlock-section-text">
		After the action is executed and you weren't able to obtain the response, you can use the <span class="code">apiService.obtainApiResponse</span> action with that same <span class="code">requestIdentifier</span> value as its <strong>argument</strong> to retroactively get the response.
	</div>
</div>
<div class="sectionBlock-section">
	<div class="sectionBlock-section-title">Usage</div>
	<div class="sectionBlock-section-text">
		You generate the <span class="code">requestIdentifier</span> value before you send the request and include it in the request content.
		<br />
		You then have 2 ways to handle failures:
	</div>
	<div class="sectionBlock-section-text">
		1) Retry now<br/>
		Keep the <span class="code">requestIdentifier</span> value in memory (in a variable).
		<br />
		Then while sending the request, catch any network related errors thrown by your HTTP / network library and immediately try to obtain the response with a call to <span class="code">apiService.obtainApiResponse</span> using the <span class="code">requestIdentifier</span> variable.
	</div>
	<div class="sectionBlock-section-attention">
		Note that it's possible the action is still executing when immediately you try to get the response, so the response won't be ready yet. You may want to wait a few seconds.
	</div>
	
	<div class="sectionBlock-section-text">
		2) Retry later<br />
		Same as 1) you also keep the <span class="code">requestIdentifier</span> value in memory and catch the network errors.
		But instead of immediately trying to get the response, you store the <span class="code">requestIdentifier</span> somewhere (in a database, maybe together with other data specifying what the request was for and how to use the response).
		<br />
		Then at a later time (perhaps in a scheduled task) you find all your 'failed' requests and try to get their responses via <span class="code">apiService.obtainApiResponse</span> and process them at that time.
	</div>
	<div class="sectionBlock-section-attention">
		Note that API responses have an expiration date, so don't wait too long to schedule a 'retry'
	</div>
</div>