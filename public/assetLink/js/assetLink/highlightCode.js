/**
 *	calls highlightjs on document ready
**/
(function()
{	
	if (document.addEventListener)
	{
		document.addEventListener('DOMContentLoaded', function()
		{
			document.removeEventListener('DOMContentLoaded', arguments.callee, false);
			onDomReady();
		}, false);
	}
	else if (document.attachEvent)
	{
		document.attachEvent('onreadystatechange', function()
		{
			if (document.readyState === 'complete')
			{
				document.detachEvent('onreadystatechange', arguments.callee);
				onDomReady();
			}
		});
	}
	
	function onDomReady()
	{
		var codeBlocks = document.getElementsByClassName('sectionBlock-section-example-content');
		for (var i = 0, l = codeBlocks.length; i < l; ++i)
		{
			hljs.highlightBlock(codeBlocks[i]);
		}
	}
})();