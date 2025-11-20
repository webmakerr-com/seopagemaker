/**
 * AI Divi Module.
 */
class PageGeneratorProDiviAI extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_AI.
	 *
	 * @since 	4.9.6
	 */
	static slug = 'page-generator-pro-divi-ai';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.9.6
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'ai',
			'AI',
			'Displays content from AI based on a topic.'
		);

	}

}

/**		
 * Alibaba AI Divi Module.
 */
class PageGeneratorProDiviAlibaba extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Alibaba.
	 *
	 * @since 	5.0.6
	 */
	static slug = 'page-generator-pro-divi-alibaba';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	5.0.6
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'alibaba',
			'Alibaba',
			'Displays content from Alibaba Qwen based on a topic.'
		);

	}

}

/**
 * Claude AI Divi Module.
 */
class PageGeneratorProDiviClaudeAI extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Claude_AI.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-claude-ai';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'claude-ai',
			'Claude AI',
			'Displays content from Claude AI based on a topic.'
		);

	}

}

/**
 * Creative Commons Divi Module.
 */
class PageGeneratorProDiviCreativeCommons extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Creative_Commons.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-creative-commons';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'creative-commons',
			'Creative Commons',
			''
		);

	}

}

/**
 * Custom Field Divi Module.
 */
class PageGeneratorProDiviCustomField extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Custom_Field.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-custom-field';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'custom-field',
			'Custom Field',
			''
		);

	}

}

/**
 * Deepseek Divi Module.
 */
class PageGeneratorProDiviDeepseek extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Deepseek.
	 *
	 * @since 	4.9.6
	 */
	static slug = 'page-generator-pro-divi-deepseek';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.9.6
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'deekseep',
			'Deekseep',
			'Displays content from Deepseek based on a topic.'
		);

	}

}

/**
 * Gemini AI Divi Module.
 */
class PageGeneratorProDiviGeminiAI extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Gemini_AI.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-gemini-ai';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'gemini-ai',
			'Gemini AI',
			''
		);

	}

}

/**
 * Gemini AI Image Divi Module.
 */
class PageGeneratorProDiviGeminiAIImage extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Gemini_AI.
	 *
	 * @since 	5.0.4
	 */
	static slug = 'page-generator-pro-divi-gemini-ai-image';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	5.0.4
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'gemini-ai-image',
			'Gemini AI Image',
			''
		);

	}

}

/**
 * Google Map Divi Module.
 */
class PageGeneratorProDiviGoogleMap extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Google_Map.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-google-map';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'google-map',
			'Google Map',
			''
		);

	}

}

/**
 * Google Places Divi Module.
 */
class PageGeneratorProDiviGooglePlaces extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Google_Places.
	 *
	 * @since 	5.2.8
	 */
	static slug = 'page-generator-pro-divi-google-places';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	5.2.8
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'google-places',
			'Google Places',
			''
		);

	}

}

/**
 * Grok AI Image Divi Module.
 */
class PageGeneratorProDiviGrokAIImage extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Grok_AI_Image.
	 *
	 * @since 	5.2.8
	 */
	static slug = 'page-generator-pro-divi-grok-ai-image';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	5.2.8
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'grok-ai-image',
			'Grok AI Image',
			''
		);

	}

}

/**
 * Grok AI Divi Module.
 */
class PageGeneratorProDiviGrokAI extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Grok_AI.
	 *
	 * @since 	5.0.6
	 */
	static slug = 'page-generator-pro-divi-grok-ai';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	5.0.6
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'grok-ai',
			'Grok AI',
			''
		);

	}

}

/**
 * Ideogram AI Image Divi Module.
 */
class PageGeneratorProDiviIdeogramAI extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Ideogram_AI_Image.
	 *
	 * @since 	5.0.3
	 */
	static slug = 'page-generator-pro-divi-ideogram-ai';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	5.0.3
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'ideogram-ai',
			'Ideogram AI',
			''
		);

	}

}

/**
 * Image URL Divi Module.
 */
class PageGeneratorProDiviImageURL extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Image_URL.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-image-url';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'image-url',
			'Image URL',
			''
		);

	}

}

/**
 * Creative Commons Divi Module.
 */
class PageGeneratorProDiviMediaLibrary extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Media_Library.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-media-library';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'media-library',
			'Media Library',
			''
		);

	}

}

/**
 * Creative Commons Divi Module.
 */
class PageGeneratorProDiviMidJourney extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Midjourney.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-midjourney';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'midjourney',
			'Midjourney',
			''
		);

	}

}

/**
 * Mistral AI Divi Module.
 */
class PageGeneratorProDiviMistralAI extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Mistral_AI.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-mistral-ai';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'mistral-ai',
			'Mistral AI',
			''
		);

	}

}

/**
 * Open Street Map Divi Module.
 */
class PageGeneratorProDiviOpenStreetMap extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Open_Street_Map.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-open-street-map';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'open-street-map',
			'Open Street Map',
			''
		);

	}

}

/**
 * Open Weather Map Divi Module.
 */
class PageGeneratorProDiviOpenWeatherMap extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Open_Weather_Map.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-open-weather-map';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'open-weather-map',
			'OpenWeatherMap',
			''
		);

	}

}

/**
 * OpenAI Image Divi Module.
 */
class PageGeneratorProDiviOpenAIImage extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_OpenAI_Image.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-openai-image';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'openai-image',
			'OpenAI Image',
			''
		);

	}

}

/**
 * OpenAI Divi Module.
 */
class PageGeneratorProDiviOpenAI extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Open_AI.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-openai';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'openai',
			'OpenAI',
			''
		);

	}

}

/**
 * OpenRouter Divi Module.
 */
class PageGeneratorProDiviOpenRouter extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_OpenRouter.
	 *
	 * @since 	5.3.0
	 */
	static slug = 'page-generator-pro-divi-openrouter';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	5.3.0
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'openrouter',
			'OpenRouter',
			''
		);

	}

}

/**
 * Perplexity Divi Module.
 */
class PageGeneratorProDiviPerplexity extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Perplexity.
	 *
	 * @since 	5.2.8
	 */
	static slug = 'page-generator-pro-divi-perplexity';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	5.2.8
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'perplexity',
			'Perplexity',
			''
		);

	}

}

/**
 * Pexels Divi Module.
 */
class PageGeneratorProDiviPexels extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Pexels.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-pexels';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'pexels',
			'Pexels',
			''
		);

	}

}

/**
 * Pixabay Divi Module.
 */
class PageGeneratorProDiviPixabay extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Pixabay.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-pixabay';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'pixabay',
			'Pixabay',
			''
		);

	}

}

/**
 * Related Links Divi Module.
 */
class PageGeneratorProDiviRelatedLinks extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Related_Links.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-related-links';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'related-links',
			'Related Links',
			''
		);

	}

}

/**
 * Straico Divi Module.
 */
class PageGeneratorProDiviStraico extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Straico.
	 *
	 * @since 	5.2.7
	 */
	static slug = 'page-generator-pro-divi-straico';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	5.2.7
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'straico',
			'Straico',
			'Displays content from Straico based on a topic.'
		);

	}

}

/**
 * Wikipedia Image Divi Module.
 */
class PageGeneratorProDiviWikipediaImage extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Wikipedia_Image.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-wikipedia-image';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'wikipedia-image',
			'Wikipedia Image',
			''
		);

	}

}

/**
 * Wikipedia Divi Module.
 */
class PageGeneratorProDiviWikipedia extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Wikipedia.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-wikipedia';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'wikipedia',
			'Wikipedia',
			''
		);

	}

}

/**
 * Yelp Divi Module.
 */
class PageGeneratorProDiviYelp extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_Yelp.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-yelp';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'yelp',
			'Yelp',
			''
		);

	}

}

/**
 * YouTube Divi Module.
 */
class PageGeneratorProDiviYouTube extends React.Component {

	/**
	 * The Divi module name. Must match the slug defined in
	 * the PHP class Page_Generator_Pro_Divi_Module_YouTube.
	 *
	 * @since 	4.7.2
	 */
	static slug = 'page-generator-pro-divi-youtube';

	/**
	 * Renders the frontend output for this module.
	 *
	 * @since 	4.7.2
	 */
	render() {

		return PageGeneratorProDiviRenderModule(
			'youtube',
			'Youtube',
			''
		);

	}

}

/**
 * Register Divi modules when the Divi Builder API is ready.
 *
 * @since 	4.7.2
 *
 * @param 	object 	event 	Event.
 * @param 	object 	API 	Divi Buidler API.
 */
jQuery( window ).on(
	'et_builder_api_ready',
	function ( event, API ) {

		// Register Divi modules.
		API.registerModules(
			[
				PageGeneratorProDiviAI,
				PageGeneratorProDiviAlibaba,
				PageGeneratorProDiviClaudeAI,
				PageGeneratorProDiviCreativeCommons,
				PageGeneratorProDiviCustomField,
				PageGeneratorProDiviDeepseek,
				PageGeneratorProDiviGeminiAIImage,
				PageGeneratorProDiviGeminiAI,
				PageGeneratorProDiviGoogleMap,
				PageGeneratorProDiviGooglePlaces,
				PageGeneratorProDiviGrokAIImage,
				PageGeneratorProDiviGrokAI,
				PageGeneratorProDiviIdeogramAI,
				PageGeneratorProDiviImageURL,
				PageGeneratorProDiviMediaLibrary,
				PageGeneratorProDiviMidJourney,
				PageGeneratorProDiviMistralAI,
				PageGeneratorProDiviOpenStreetMap,
				PageGeneratorProDiviOpenWeatherMap,
				PageGeneratorProDiviOpenAIImage,
				PageGeneratorProDiviOpenAI,
				PageGeneratorProDiviOpenRouter,
				PageGeneratorProDiviPerplexity,
				PageGeneratorProDiviPexels,
				PageGeneratorProDiviPixabay,
				PageGeneratorProDiviRelatedLinks,
				PageGeneratorProDiviStraico,
				PageGeneratorProDiviWikipediaImage,
				PageGeneratorProDiviWikipedia,
				PageGeneratorProDiviYelp,
				PageGeneratorProDiviYouTube
			]
		);

	}
);

/**
 * Return a React element similar to the Gutenberg block when displaying a Dynamic
 * Element in a Content Group.
 *
 * @since 	4.7.2
 *
 * @param 	string 	name 			Programmatic name.
 * @param 	string  title   		Title.
 * @param 	string  description 	Description.
 */
function PageGeneratorProDiviRenderModule( name, title, description ) {

	return React.createElement(
		'div',
		{
			className: 'page-generator-pro-divi-module ' + name
		},
		[
			// Title.
			React.createElement(
				'div',
				{
					className: 'page-generator-pro-block-title'
				},
				title + ' Dynamic Element'
			),

			// Description.
			React.createElement(
				'div',
				{
					className: 'page-generator-pro-block-description'
				},
				description
			),
			React.createElement(
				'div',
				{
					className: 'page-generator-pro-block-description'
				},
				'Click the cog icon in the Divi Builder for this module to open this Dynamic Element\'s settings.'
			)
		]
	);

}
