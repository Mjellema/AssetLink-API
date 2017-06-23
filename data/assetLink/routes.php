<?php
$documentation = function($template, $selectedChapter)
{
	return ['bite.ui.PageRenderer', 'render', [
		'view'		=> 'bite.ui.MainView',
		'config'	=> [
			'template'	=> 'assetLink/site/main.tpl',
			'styles'	=> [
				'public/assetLink/css/compiled/assetLinkApp1.0.css',
				'//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.10.0/styles/agate.min.css',
			],
			'scripts'	=> [
				'public/assetLink/js/bite/cssKeyboardFocus.js',
				'public/assetLink/js/assetLink/highlightCode.js',
				'//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.10.0/highlight.min.js',
			],
		],
		'children'	=> [
			'header' => [
				[
					//'view'		=> 'assetLink.site.HeaderView',
					'view'		=> 'bite.ui.View',
					'config'	=> ['template' => 'assetLink/site/common/header.tpl'],
				],
			],
			'body' => [
				[
					'view'		=> 'bite.ui.View',
					'config'	=> ['template' => 'assetLink/site/documentation/common/documentationMain.tpl'],
					'children'	=> [
						'navigation' => [
							[
								'view'		=> 'assetLink.site.documentation.NavigationView',
								'config'	=> [
									'template'			=> 'assetLink/site/documentation/common/navigation.tpl',
									'selectedChapter'	=> $selectedChapter,
								],
							],
						],
						'content' => [
							[
								'view'			=> 'bite.ui.View',
								'config'		=> ['template' => $template],
							]
						],
					],
				],
				
			],
			'footer' => [
				[
					//'view'		=> 'assetLink.site.FooterView',
					'view'		=> 'bite.ui.View',
					'config'	=> ['template' => 'assetLink/site/common/footer.tpl'],
				],
			],
		]
	]];
};


$data = [
	'notFound' => ['bite.ui.PageNotFoundController', 'processInput'],
	'routes' => [
		'robots.txt' => ['bite.ui.PageRenderer', 'render', [
			'view'			=> 'bite.ui.RobotsView',
			'config'		=> ['template' => 'assetLink/site/robots.tpl'],
		]],
		// API RPC endpoint
		'api/' => ['assetLink.api.AssetLinkInboundApiController', 'processInput'],
		// API documentation
		'documentation/' => $documentation('assetLink/site/documentation/introduction/introduction.tpl', 'introduction-introduction'),
		'documentation/quick-start/' => $documentation('assetLink/site/documentation/introduction/quickStart.tpl', 'introduction-quickStart'),
		'documentation/request-protocol/' => $documentation('assetLink/site/documentation/introduction/requestProtocol.tpl', 'introduction-requestProtocol'),
		'documentation/response-protocol/' => $documentation('assetLink/site/documentation/introduction/responseProtocol.tpl', 'introduction-responseProtocol'),
		'documentation/authentication/' => $documentation('assetLink/site/documentation/introduction/authentication.tpl', 'introduction-authentication'),
		'documentation/connection-failure/' => $documentation('assetLink/site/documentation/introduction/connectionFailure.tpl', 'introduction-connectionFailure'),
		'documentation/apiService/obtainApiResponse/' => $documentation('assetLink/site/documentation/apiService/obtainApiResponse.tpl', 'apiService-obtainApiResponse'),
		'documentation/ebookAssetService/registerEbookDownloadLink/' => $documentation('assetLink/site/documentation/ebookAssetService/registerEbookDownloadLink.tpl', 'ebookAssetService-registerEbookDownloadLink'),
		'documentation/ebookAssetService/obtainEbookDownloadLinkStatus/' => $documentation('assetLink/site/documentation/ebookAssetService/obtainEbookDownloadLinkStatus.tpl', 'ebookAssetService-obtainEbookDownloadLinkStatus'),
		'documentation/ebookAssetService/downloadEbookMetaCover/' => $documentation('assetLink/site/documentation/ebookAssetService/downloadEbookMetaCover.tpl', 'ebookAssetService-downloadEbookMetaCover'),
		'documentation/ebookAssetService/downloadEbook/' => $documentation('assetLink/site/documentation/ebookAssetService/downloadEbook.tpl', 'ebookAssetService-downloadEbook'),
		'documentation/assetService/provideNewPreviousOwner/' => $documentation('assetLink/site/documentation/assetService/provideNewPreviousOwner.tpl', 'assetService-provideNewPreviousOwner'),
		'documentation/assetService/transferAssetOwnership/' => $documentation('assetLink/site/documentation/assetService/transferAssetOwnership.tpl', 'assetService-transferAssetOwnership'),
		'documentation/assetService/generateManagePermissionPasstoken/' => $documentation('assetLink/site/documentation/assetService/generateManagePermissionPasstoken.tpl', 'assetService-generateManagePermissionPasstoken'),
		'documentation/assetService/grantManagePermissionUsingPasstoken/' => $documentation('assetLink/site/documentation/assetService/grantManagePermissionUsingPasstoken.tpl', 'assetService-grantManagePermissionUsingPasstoken'),
	],
];