<?php
namespace assetLink\site\documentation;

use bite\error\EError;
use bite\error\IError;
use bite\ui\View;

/**
 *	
**/
class NavigationView extends View
{
	/**
	 *	@param		void
	 *	@return		string|IReader|IWriterProxy
	**/
	public function render()
	{
		if (isset($this->viewConfig['selectedChapter']))
		{
			$this->template->show($this->viewConfig['selectedChapter']);
		}
		
		return $this->template->render();
	}
}