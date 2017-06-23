/**
 *	adds _bite-keyboardFocus modifier css class to <body> when Tab key is pressed
 *	removes the class on mouse down
**/
(function()
{	
	var cssClassName = '_bite-keyboardFocus';
	
	if (document.addEventListener) document.addEventListener('keydown', onKeyDown);
	else if (document.attachEvent) document.attachEvent('onkeydown', onKeyDown);
	
	function onKeyDown(event)
	{
		// check for Tab key
		if (event.keyCode === 9 && document.body && (' ' + document.body.className + ' ').indexOf(' ' + cssClassName + ' ') === -1)
		{
			// addClass
			document.body.className += ' ' + cssClassName;
			
			if (document.addEventListener) document.addEventListener('mousedown', onMouseDown);
			else if (document.attachEvent) document.attachEvent('onmousedown', onMouseDown);
		}
	}
	
	function onMouseDown(event)
	{
		var position = (' ' + document.body.className + ' ').indexOf(' ' + cssClassName + ' ');
		if (position !== -1)
		{
			// removeClass
			document.body.className = document.body.className.substr(0, position) + document.body.className.substr(position + cssClassName.length);
		}
	}
})();