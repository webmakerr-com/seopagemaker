=== Page Generator Pro ===
Contributors: wpzinc
Donate link: https://www.wpzinc.com/plugins/page-generator-pro
Tags: page,generator,content,bulk,pages
Requires at least: 5.0
Tested up to: 6.8.3
Requires PHP: 8.0
Stable tag: 5.3.3

Generate multiple Pages, Posts and Custom Post Types using dynamic content.

== Description ==

Page Generator Pro allows you to generate multiple Pages, Posts or Custom Post Types, each with their own variation of a base content template.  

Variations can be produced by using keywords, which contain multiple words or phrases that are then cycled through for each Page that is generated.

Generate multiple Pages, Posts or CPT's in bulk by defining:

* Page Title
* Page Slug / Permalink
* Content
* Publish status (Draft or Publish)
* Number of Pages to generate
* Author

[youtube http://www.youtube.com/watch?v=KTBDy3-6Z1E]

= Support =

For all support queries, please email us: <a href="mailto:support@wpzinc.com">support@wpzinc.com</a>

== Installation ==

1. Upload the `page-generator-pro` folder to the `/wp-content/plugins/` directory
2. Active the Page Generator Pro plugin through the 'Plugins' menu in WordPress
3. Configure the plugin by going to the `Page Generator Pro` menu that appears in your admin menu

== Frequently Asked Questions ==



== Screenshots ==



== Changelog ==

= 5.3.3 (2025-11-19) =
* Added: Settings: Integration: Gemini AI: Gemini 3.0 Pro Preview model
* Added: Settings: Integration: Grok AI: Grok 4 Fast Reasoning and Non Reasoning models
* Added: Settings: Integration: OpenAI: GPT-5.1 and 5.1 Chat models

= 5.3.2 (2025-11-05) =
* Fix: Import: Breakdance: Correctly encode and save Page Builder data on imported Content Groups
* Fix: Import: Oxygen: Correctly encode and save Page Builder data on imported Content Groups

= 5.3.1 (2025-11-03) =
* Added: Generate: Content: Overwrite Sections: Metabox.io Field Groups. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields-generation-overwrite-sections-meta-box-metaboxio, https://www.wpzinc.com/documentation/page-generator-pro/generate-using-custom-field-plugins/#meta-box-metaboxio
* Fix: Pages/Posts: Directly query database to populate `Filter by Content Group` dropdown, avoiding conflicts with admin column outputs from e.g. Metabox.io
* Fix: Dynamic Elements: OpenAI: Corrected max tokens for GPT-5 models
* Removed: Settings: Integration: OpenAI: gpt-4o-2024-11-20, gpt-4o-2024-05-13, gpt-4-turbo-preview deprecated models

= 5.3.0 (2025-10-28) =
* Added: Dynamic Elements: Bricks: OpenRouter Dynamic Element
* Added: Dynamic Elements: Divi: Gemini AI, Grok AI, OpenRouter Dynamic Elements
* Added: Dynamic Elements: Elementor: OpenRouter Dynamic Elements
* Added: Dynamic Elements: Live Composer: OpenRouter Dynamic Elements
* Fix: Generate: Content: Bricks: Fatal error on some elements

= 5.2.9 (2025-10-15) =
* Added: Keywords: Random Subset: Support for specifying minimum Terms = 1, with no maximum Terms
* Fix: Generate: Content: Featured Image: Media Library: Honor Alt Text setting

= 5.2.8 (2025-10-02) =
* Added: Dynamic Elements: Google Places.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-google-places/
* Added: Generate: Content: Divi: Frontend editor support for AI, Grok AI, Perplexity and Straico Dynamic Elements
* Fix: Generate: Content: Bricks: Fatal error on some elements
* Updated: Dynamic Elements: Google Maps: Changed icon
* Updated: Dynamic Elements: Open Street Maps: Changed icon to Open Street Maps icon
* Updated: Claude AI: Added Claude Sonnet 4.5 (Latest), removed deprecated Claude 1.2, 2.0, 2.1, 3 Sonnet and 3.5 Sonnet models

= 5.2.7 (2025-09-29) =
* Added: Keywords: Sources: Straico AI as a Keyword Data source.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--ai
* Added: Dynamic Elements: Straico AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-ai/
* Added: Research: Straico AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research
* Added: Generate: Content: Bricks: Register Dynamic Elements as Bricks elements.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-bricks/#dynamic-elements
* Added: Generate: Content: Live Composer: Register Dynamic Elements as Live Composer modules.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-live-composer/#dynamic-elements
* Fix: Polylang: Don't set a language on Generated Pages if languages and transations not enabled on Content Groups

= 5.2.6 (2025-09-17) =
* Fix: Keyword Autocompleters: Honor selected Keyword when using in some page builders, such as Elementor
* Fix: Generate: Conditional Output: Ignore nested brackets within @if statement

= 5.2.5 (2025-09-11) =
* Added: Gutenberg / Block Editor: Image Dynamic Elements: Generate Image block instead of HTML block
* Added: Site Editor: Blocks: Support for Related Links Dynamic Element
* Added: Settings: Generate: Generate Content Items per Request: Increase maximum supported value to 500

= 5.2.4 (2025-09-01) =
* Added: Gemini AI Image: Gemini 2.5 Flash Image Preview (aka Nano Banana) model
* Fix: Research: `Unsupported content type specified` error
* Fix: Research: Honor Instructions and Tuning parameters

= 5.2.3 (2025-08-25) =
* Fix: Keywords: CSV: Ignore empty rows
* Fix: Dynamic Elements: AI: Don't wrap output in paragraph tags when Content Type = Freeform
* Updated: Settings: Integrations: Yelp: Description updated to reflect that a Yelp API Key is required when using the Yelp Dynamic Element. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-integrations/#yelp

= 5.2.2 (2025-08-21) =
* Added: Gemini AI Image: Imagen 3 and 4 models
* Fix: Generate: Content: Improve generation time and performance when using AI Image Dynamic Elements
* Fix: Generate: Content: Featured Image: Ideogram AI: Honor model selection in Settings > Integrations > Ideogram AI Model
* Fix: Generate Spintax from Selected Text: `you must provide a model parameter` error

= 5.2.1 (2025-08-09) =
* Fix: Settings: Integrations: OpenAI Model: GPT-5 Nano missing
* Fix: OpenAI: Use `max_completion_tokens` for GPT-5 models

= 5.2.0 (2025-08-08) =
* Added: Settings: Integrations: AI: Instructions Setting. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-integrations/#ai-instructions
* Added: Dynamic Elements: AI: Instructions Setting. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-ai/#instructions
* Added: Claude AI: Claude Opus 4.1 (Latest) model
* Added: Gemini AI: 2.5 Pro, 2.5 Flash, 2.5 Flash Lite models: https://ai.google.dev/gemini-api/docs/changelog#07-22-2025
* Added: OpenAI: Added GPT-5, 5 Mini, 5 Nano and 5 Chat models
* Fix: Dynamic Elements: AI: Provide verbose error when prompt server returns a 403 forbidden
* Removed: Gemini AI: 2.5 Pro Preview, Experimental, which are replaced/deprecated: https://ai.google.dev/gemini-api/docs/changelog#06-26-2025
* Removed: Deepseek: Reasoning model, as output included reasoning steps, unsuitable for SEO
* Removed: Perplexity: Sonar Reasoning model, as output included reasoning steps, unsuitable for SEO

= 5.1.9 (2025-07-17) =
* Added: Dynamic Elements: AI: Language: Support for country-specific language variants
* Fix: Generate: Content: Featured Image: Pixabay: Set correct value `vertical` when Image Orientation = Portrait

= 5.1.8 (2025-07-10) =
* Added: Settings: Integrations: Grok AI: Grok 4 model
* Fix: Generate: Content: Search Exclude Plugin: Copy setting to generated content

= 5.1.7 (2025-07-07) =
* Added: Ideogram: Support for 3.0 model
* Fix: Settings: Integrations: Ideogram: Honor model selection

= 5.1.6 (2025-07-03) =
* Fix: Keywords: Don't display confirmation dialog twice when using Bulk Actions below Keywords table
* Fix: Dynamic Elements: OpenAI Image: Size: Display correct values based on chosen model from Settings > Integrations > OpenAI Image: Model

= 5.1.5 (2025-06-17) =
* Fix: OpenAI: gpt-image-1: Increase timeout cutoff to allow OpenAI time to generate image 
* Fix: Generate: Content: PHP 8.4: Deprecated: str_getcsv(): the $escape parameter must be provided

= 5.1.4 (2025-05-23) =
* Updated: Settings: Integrations: Claude AI: Added Claude 4 Sonnet and Opus models

= 5.1.3 (2025-05-22) =
* Notice: PHP 8.0 is the minimum required version
* Added: Developer: Keywords: `page_generator_pro_keywords_save` filter before saving a Keyword
* Fix: Settings: Integrations: Gemini AI Image Model: Save setting when changed
* Updated: kwn\NumberToWords Library to 2.11.2
* Updated: PHPOffice\PHPSpreadsheet Library to 2.0.0

= 5.1.2 (2025-05-13) =
* Added: Keywords: Confirmation dialog when deleting Keywords
* Added: Generate: Content: Featured Image: Creative Commons: Link to image licenses explanation
* Added: Settings: Integrations: Gemini AI: 2.5 Flash Preview model
* Added: Settings: Integrations: Grok AI: 3.0 models
* Added: Settings: Integrations: Mistral AI: Saba model (recommended for languages from the Middle East and South Asia)
* Added: Settings: Integrations: OpenAI: o4 mini and o3 models
* Fix: Dynamic Elements: Claude AI: Increase maximum supported tokens on 3.7 Sonnet to 64,000
* Fix: Dynamic Elements: Gemini AI: Increase maximum supported tokens on Gemini 2.5 models to 65,536
* Fix: Dynamic Elements: Mistral AI: Increase maximum supported tokens on Mistral Small and Medium to 128,000
* Fix: Dynamic Elements: OpenAI: Increase maximum supported tokens on o4-mini, o3, o3-mini, o1, o1-pro to 100,000
* Fix: Dynamic Elements: OpenAI: Increase maximum supported tokens on gpt-4.1 to 32,768
* Fix: Generate: Content: Featured Image: Wikipedia Image: PHP Warning: Array to string conversion
* Fix: Generate: Content: Add New Using AI: Populate form with default values and fix PHP warnings
* Fix: Settings: Integrations: Correct documentation links for models, API key and account registration for each provider

= 5.1.1 (2025-05-08) =
* Updated: Gemini AI Image: `gemini-2.0-flash-preview-image-generation` model
* Fix: Gemini AI Image: Error "File is empty. Please upload something more substantial." when using Dynamic Element or Featured Image

= 5.1.0 (2025-05-07) =
* Added: Generate: Content: Slim SEO Pro Support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-seo-integration/#slim-seo

= 5.0.9 (2025-04-29) =
* Added: Dynamic Elements: OpenAI Image: gpt-image-1 model.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-openai-image/
* Added: Generate: Content: Featured Image: gpt-image-1 model.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Fix: Notice: Function `_load_textdomain_just_in_time` was called incorrectly in WordPress 6.8 and higher
* Fix: Generate: Content: Add New Directory Structure: Populate form with default values, include location Keyword in Content Groups and fix PHP warnings

= 5.0.8 (2025-04-15) =
* Added: Settings: Integrations: OpenAI: Added GPT-4.1, 4.1 Mini and 4.1 Nano models
* Added: Settings: Integrations: IndexNow: Display site API Key

= 5.0.7 (2025-04-03) =
* Fix: Research: Display Topic field when using Article Forge or ContentBot

= 5.0.6 (2025-03-31) =
* Added: Keywords: Sources: Grok / xAI as a Keyword Data source. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--ai
* Added: Dynamic Elements: Grok / xAI. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-ai/
* Added: Research: Grok / xAI. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research
* Added: Settings: Integrations: Grok / xAI Image support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-integrations/#grok-ai
* Added: Generate: Content: Featured Image: Grok / xAI Image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Added: Dynamic Elements: Grok / xAI Image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-grok-ai-image/
* Added: Dynamic Elements: Alibaba AI: Register in Divi and Elementor
* Added: Dynamic Elements: Deepseek: Register in Divi
* Fix: Dynamic Elements: Alibaba Qwen: Correctly register as a Dynamic Element

= 5.0.5 (2025-03-27) =
* Added: Settings: Integrations: OpenAI: Added o1-pro model
* Added: Settings: Integrations: Gemini AI: Added Gemini 2.5 Pro (Experimental), 2.0 Flash (Latest), 2.0 Flash (Stable, v1) 2.0 Flash Lite (Latest), 2.0 Flash Lite (Stable, v1), 1.5 Pro (Stable, v1), 1.5 Pro (Stable, v2) models
* Added: Generate: Content: IndexNow: Send scheduled generated Pages, Posts and Custom Post Types to IndexNow when they transition to published
* Added: Generate: Content: Permalink: Permit static Permalink when Parent > Attribute is a Keyword
* Added: Generate: Content: Permalink: Additional validation when using Permalink with Parent > Attribute
* Added: Generate: Content: Return error if Keyword used in Permalink results in no output for the Permalink
* Fix: Dynamic Elements: Gemini AI: Some models would fail due to incorrect endpoint
* Fix: Keywords: Generate Locations: Remove column names from Terms and trim data when using AI source
* Fix: Generate: Content: Bricks: Generate Bricks CSS for generated Pages
* Fix: Updated WordPress Coding Standards
* Removed: Keywords: Import CSV or Spreadsheet. Use Keywords > Add New, setting Source = CSV File, CSV URL or Spreadsheet.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--csv-file

= 5.0.4 (2025-03-18) =
* Added: Settings: Integrations: Gemini AI Image support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-integrations/#gemini-ai
* Added: Generate: Content: Featured Image: Gemini AI Image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Added: Dynamic Elements: Gemini AI Image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-gemini-ai-image/
* Added: Generate: Content: Add New using AI: Option to specify page builder to use for building Content Group.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#add-new-using-ai
* Fix: Generate: Content: BeTheme: Don't duplicate Dynamic Element processing in BeTheme 27.6+
* Updated: Settings: Integrations: Claude AI: Added Claude 3.7 Sonnet model

= 5.0.3 (2025-03-13) =
* Added: Settings: Integrations: Ideogram AI support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-integrations/#ideogram-ai-image
* Added: Settings: Integrations: IndexNow support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-integrations/#indexnow
* Added: Generate: Content: Featured Image: Ideogram AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Added: Dynamic Elements: Ideogram AI Image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-ideogram-ai-image/
* Added: Whitelabelling: Support for editing description.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/whitelabelling-access/#configure-whitelabelling

= 5.0.2 (2025-03-10) =
* Fix: Generate: Content: Remove horizontal scroll bar
* Fix: Dynamic Elements: AI: Could not load Plugin class error

= 5.0.1 (2025-03-06) =
* Added: Settings: Integrations: OpenAI: Added GPT-4.5 Preview model
* Added: Keywords: AI: Data Columns option. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--ai
* Added: Keywords: Generate Locations: City Ethnicity, Housing, Education, Employment, Crime, Weather, Income, History data and Country Flag.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/#ai
* Added: Generate: Content: Require Title.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--title
* Fix: Dynamic Elements: AI: Return error when the prompt exceeds the maximum tokens supported by the AI and model
* Fix: Dynamic Elements: OpenAI Image: Prevent 502 timeout when using OpenAI Image Divi Module
* Fix: Divi: Render block spintax in Text module. Code module for block spintax is still recommended.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-spintax/#block-spinning

= 5.0.0 (2025-02-27) =
* Added: Keywords: Generate Locations: Option to use AI provider. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-locations-settings/
* Added: Whitelabelling: Support for header background color, primary text color and secondary text color. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/whitelabelling-access/#configure-whitelabelling
* Added: Generate: Content: Comments: Insert spintax button for first name and surname.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--discussion--generate-comments
* Fix: Spintax: SpinnerChief: Use new API. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/spintax-settings/#service--spinnerchief

= 4.9.9 (2025-02-19) =
* Added: Updated UI and icon
* Added: Whitelabelling: Support for whitelabelling logo.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/whitelabelling-access/#configure-whitelabelling

= 4.9.8 (2025-02-10) =
* Added: Dynamic Elements: Media Library: Support for WebP image output
* Added: Dynamic Elements: Image URL: Support for WebP image output
* Updated: Settings: Integrations: OpenAI: Added GPT-4o (May 13th 2024 snapshot) model
* Fix: Dynamic Elements: OpenAI: Use `max_completion_tokens` for o1 and o3 models
* Fix: Generate: Content: Comments: Display icons for AI and Deepseek

= 4.9.7 (2025-02-01) =
* Added: Keywords: Sources: Alibaba Qwen as a Keyword Data source. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--ai
* Added: Dynamic Elements: Alibaba Qwen. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-ai/
* Added: Research: Alibaba Qwen. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research
* Added: Generate: Content: Add New Content Group using OpenAI is now Add New using AI, and no longer limited to using OpenAI. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#add-new-keyword-and-content-group-using-ai
* Fix: Dynamic Elements: Deepseek: Uncaught TypeError on missing `rate_limit_backoff` method
* Fix: Dynamic Elements: Perplexity: Uncaught TypeError on missing `rate_limit_backoff` method
* Fix: Dynamic Elements: Gemini AI: Response data empty when using 2 or more Gemini AI Dynamic Elements in a single Content Group
* Fix: Dynamic Elements: OpenRouter: `No models provided` error when a single model specified at Settings > Integrations
* Updated: Settings: Integrations: Claude AI: Added Claude 3.5 Sonnet and Haiku (Latest) models
* Updated: Settings: Integrations: Gemini AI: Added Gemini 1.5 Flash v2 and 1.5 Flash-8B v2 models 
* Updated: Settings: Integrations: Mistral AI: Added Mistral 3B and 8B models
* Updated: Settings: Integrations: OpenAI: Added o1 and o3-mini models
* Updated: Settings: Integrations: OpenAI: Removed GPT-4 Turbo (April 9th 2024), as this is the same as GPT-4 Turbo
* Updated: Settings: Integrations: OpenAI: Removed GPT-4: Turbo (gpt-4-0125-preview), as this is the same as GPT-4 Turbo (Preview)
* Updated: Settings: Integrations: Perplexity AI: Added Sonar Reasoning, Pro and Sonar models

= 4.9.6 (2025-01-29) =
* Added: Keywords: Sources: Deepseek as a Keyword Data source.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--deepseek
* Added: Dynamic Elements: Deepseek.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-ai/
* Added: Research: Deepseek. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research
* Added: Dynamic Elements: AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-ai/

= 4.9.5 (2025-01-23) =
* Fix: Generate: Content: Featured Image: Midjourney: "A valid URL was not provided" error
* Fix: Dynamic Elements: Midjourney: "A valid URL was not provided" error

= 4.9.4 (2025-01-16) =
* Fix: Dynamic Elements: Pexels: Fatal error when "Save to Library" enabled and image successfully copied

= 4.9.3 (2025-01-06) =
* Added: Keywords: Sources: Perplexity AI as a Keyword Data source.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--ai
* Added: Dynamic Elements: Perplexity AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-perplexity/
* Added: Research: Perplexity AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research
* Added: Dynamic Elements: Option to ignore errors, returning blank content sections when a Dynamic Element fails
* Added: Dynamic Elements: Claude AI, Gemini, Mistral, OpenAI, OpenRouter: General and Tuning Tab Icons
* Added: Content Groups: Support for OceanWP Theme

= 4.9.2 (2024-12-20) =
* Added: Dynamic Elements: Wikipedia: Improved detection of disambiguation pages in non-English languages
* Fix: Generate: Content: Divi: Dynamic Elements: Support for array attributes

= 4.9.1 (2024-12-12) =
* Added: Dynamic Elements: Return more verbose error messages
* Added: Dynamic Elements: Elementor: Honor `Style` and `Advanced` properties when generating content
* Added: Settings: Integration: Gemini AI: Added Gemini 2.0 Flash (Experimental) and Gemini 1.5 Flash-8B model and their variants
* Fix: Dynamic Elements: Media Library: Return an error when no image could be found
* Fix: Dynamic Elements: PHP Deprecated notice `strip_tags(): Passing null to parameter #1`

= 4.9.0 (2024-12-10) =
* Added: Dynamic Elements: Return error when rendering a Dynamic Element fails, instead of generating a Page with blank content sections.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-shortcodes/#generation-errors
* Added: Dynamic Elements: Claude AI: Handle 429 and 529 rate limit / server overload responses using suggested pause time from Claude AI response before re-attempting
* Added: Dynamic Elements: OpenAI: Handle 429 and 529 rate limit / server overload responses using suggested pause time from Claude AI response before re-attempting
* Fix: Block Spintax: Ignore paragraphs containing only HTML tags and no text

= 4.8.9 (2024-11-28) =
* Fix: Dynamic Elements: Custom Field: Elementor: Render output on generated Pages

= 4.8.8 (2024-11-25) =
* Added: Content Groups: Support for Neve Theme
* Fix: Generate: Content: Beaver Builder: Don't copy Beaver Builder data to Post Content if Beaver Builder not active on the Content Group

= 4.8.7 (2024-11-21) =
* Added: Settings: Integration: OpenAI: Added GPT-4o (November 20th 2024 snapshot) `gpt-4o-2024-11-20` model
* Added: Claude AI: Claude 2.1, 2 and Instant 1.2 legacy models
* Added: Generate: Content: Breakdance Builder: Regenerate CSS for each Generated Page
* Fix: Generate: Content: Cornerstone: Don't rebuild generated Page content if Cornerstone not used on a Content Group
* Fix: Generate: Content: Featured Image: Media Library: Set Featured Image on Generated Content when Create as Copy = No

= 4.8.6 (2024-11-14) =
* Added: Dynamic Elements: Custom Fields: Cornerstone: Don't convert to shortcode on Generated Content
* Added: Dynamic Elements: Related Links: Cornerstone: Don't convert to shortcode on Generated Content
* Fix: Dynamic Elements: Cornerstone: Show / hide conditional fields when condition is an array of values
* Fix: Dynamic Elements: Cornerstone: Store Dynamic Elements content in non-classic raw element
* Fix: Dynamic Elements: Cornerstone: Don't register Dynamic Elements outside of Content Groups

= 4.8.5 (2024-11-12) =
* Added: Generate: Content: BeTheme: Support for Keywords in Global Sections and Global Wraps.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-betheme-muffin-page-builder-integration/#global-sections-and-global-wraps
* Added: Settings: Integration: OpenAI: Added chatgpt-4o-latest model
* Removed: Settings: Integration: OpenAI: gpt-4-32k and gpt-4-32k-0613 models

= 4.8.4 (2024-10-31) =
* Added: Dynamic Elements: Custom Field: Gutenberg: Don't convert to shortcode on Generated Content
* Added: Dynamic Elements: Custom Field: Elementor: Don't convert to shortcode on Generated Content
* Added: Dynamic Elements: Related Links: Elementor: Don't convert to shortcode on Generated Content
* Fix: Dynamic Elements: Related Links: Elementor: Honor Taxonomy and Custom Field configuration
* Fix: Keyword Autocompleters: Improved performance by conditionally re-initializing autocompleters

= 4.8.3 (2024-10-25) =
* Fix: Elementor: Dynamic Elements: Related Links: Fatal error: Uncaught TypeError: json_decode(): Argument #1 ($json) must be of type string, array given

= 4.8.2 (2024-10-24) =
* Added: Dynamic Elements: AI: Support for newlines in topic / prompt
* Added: Dynamic Elements: Gutenberg: Conditionally display fields based on Dynamic Element's configuration
* Added: Dynamic Elements: Related Links: Gutenberg: Don't convert to shortcode on Generated Content
* Added: Dynamic Elements: Related Links: Gutenberg: Support for Taxonomy and Custom Field configuration.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/#configuration--taxonomy-conditions, https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/#configuration--custom-field-conditions

= 4.8.1 (2024-10-16) =
* Fix: Dynamic Elements: Restore Save to Library, Size and Display Caption settings
* Removed: Settings: Integrations: Creative Commons: API Key field, as no API Key is required

= 4.8.0 (2024-10-16) =
* Added: Refactored registration of Integrations, Research Providers, Spintax Providers, Keyword Sources, Featured Image Providers and AI Dynamic Elements
* Added: Generate: Content: Featured Image: Creative Commons: Sources and License fields.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Added: Generate: Content: Featured Image: Pixabay: Safe Search field.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Added: Generate: Content: Featured Image: Midjourney.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Fix: Dynamic Elements: Media Library: Save Search Parameters > Alt Tag when using Gutenberg
* Fix: Generate: Content: Featured Image: Featured Image from URL: Correctly set Featured Image
* Fix: Generate: Content: Dynamic Elements: AI: Perform exponential backoff when a 429 rate limit is hit, to reduce the chance of blank content being returned
* Removed: AI Writer. API no longer exists.
* Removed: Generate: Content: Featured Image: Featured Image by URL Plugin support, as Plugin no longer exists.

= 4.7.5 (2024-10-03) =
* Fix: Dynamic Elements: Wikipedia: Improved method to detect sections with accented characters
* Fix: Dynamic Elements: OpenAI Image: Could not insert attachment into the database error, due to some image filenames exceeding `guid` length limit
* Fix: Generate: Content: Cornerstone (Pro / X Theme): Display Generated Content correctly
* Fix: Generate: Content: Cornerstone (Pro / X Theme): Correctly register Dynamic Elements and their fields

= 4.7.4 (2024-09-19) =
* Added: Settings: Integration: Gemini AI: Added Gemini-1.5 Flash model and their variants
* Added: Settings: Integration: OpenAI: Added o1-preview and o1-mini models
* Fix: Dynamic Elements: Gemini AI: Use correct endpoint for v1.5 based models

= 4.7.3 (2024-09-12) =
* Fix: Dynamic Elements: Google Map: Save Center Latitude and Longitude settings when using Gutenberg

= 4.7.2 (2024-08-28) =
* Added: Generate: Content: Divi: Dynamic Elements: Support for using Dynamic Elements in Divi's frontend builder.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-divi/#backend---frontend-editing
* Fix: Generate: Content: Divi: "This third party module is not fully compatible" messages when editing a Dynamic Element in Divi
* Fix: Generate: Content: All in One SEO: Fatal error when using AIOSEO Pro 4.6.9 and higher

= 4.7.1 (2024-08-20) =
* Fix: Generate: Content: Breakdance Builder: Use Breakdance `get_tree` and `set_meta` functions to ensure shortcodes are saved on generated Pages correctly
* Removed: Settings: Integration: OpenAI: gpt-3.5-turbo-0613 model (deprecating September 13th)

= 4.7.0 (2024-08-12) =
* Added: Keywords: Sources: OpenRouter AI as a Keyword Data source.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--ai
* Added: Dynamic Elements: OpenRouter AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-openrouter/
* Added: Research: OpenRouter AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research
* Fix: Dynamic Elements: Wikipedia: Parse sections correctly due to changes in Wikipedia's content structure
* Fix: Dynamic Elements: Wikipedia: Remove <sup> elements due to changes in Wikipedia's content structure

= 4.6.9 (2024-08-05) =
* Added: Generate: Content: Breakdance Builder: Display Dynamic Element buttons in Rich Text TinyMCE editor.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-breakdance-builder/#dynamic-elements

= 4.6.8 (2024-07-29) =
* Added: Dynamic Elements: Claude AI: Content Types: Review and Review (Plain Text, no schema) options.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-claude-ai/#configuration--general
* Added: Dynamic Elements: Mistral AI: Content Types: Review and Review (Plain Text, no schema) options.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-gemini-ai/#configuration--general
* Added: Dynamic Elements: Gemini AI: Content Types: Review and Review (Plain Text, no schema) options.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-mistral-ai/#configuration--general
* Added: Dynamic Elements: OpenAI: Content Types: Review and Review (Plain Text, no schema) options.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-openai/#configuration--general
* Added: Generate: Content: Comments: Execute shortcodes on First Name, Surname and Comment fields
* Added: Generate: Content: Comments: Add AI Dynamic Elements to Comment Text. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--discussion--generate-comments

= 4.6.7 (2024-07-25) =
* Added: Dynamic Elements: Custom Field: Execute shortcodes that only run when viewing a generated page (e.g. Related Links)
* Added: Mistral AI: Mixtral Nemo model

= 4.6.6 (2024-07-22) =
* Added: Settings: Integration: OpenAI: Added GPT-4o-mini
* Fix: Generate: Content: Improve performance when `Rotate Authors` option is enabled.

= 4.6.5 (2024-07-04) =
* Fix: Generate: Content: Thrive Architect: Don't attempt to process spintax on JSON encoded Thrive Architect Elements 

= 4.6.4 (2024-06-25) =
* Added: Dynamic Elements: Midjourney Image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-midjourney/
* Added: Claude AI: Claude 3.5 Sonnet model
* Added: Gemini AI: Pro 1.5 Latest Stable model
* Added: Mistral AI: Mixtral 8x22B model
* Fix: Generate: Content: Oxygen Builder: Support Oxygen changed meta key names in Oxygen 4.8.3+

= 4.6.3 (2024-06-24) =
* Added: Generate: Content: Custom Fields: Add Dynamic Elements to Custom Fields. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--custom-fields

= 4.6.2 (2024-06-20) =
* Added: Dynamic Elements: Custom Field.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-custom-field/
* Fix: Generate: Content: Improve performance by removing settings for Overwrite Sections that are disabled, and the generated Page exists
* Fix: Generate: Content: Divi: Dynamic Elements: Register missing Dynamic Elements for Claude AI, Gemini AI, Mistral AI, OpenAI, OpenAI Image
* Fix: Generate: Content: Elementor: Dynamic Elements: Register missing Dynamic Elements for Claude AI, Gemini AI, Mistral AI, OpenAI Image
* Fix: Generate: Content: Elementor: Dynamic Elements: OpenAI: Display icon

= 4.6.1 (2024-05-22) =
* Added: Dynamic Elements: OpenAI Image (DALL·E 3): Option to specify size output.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-openai-image/
* Fix: Generate: Content: Breakdance Builder: Support for 1.7.2+ due to change of meta key storing content (`_breakdance_data`).

= 4.6.0 (2024-05-16) =
* Added: Keywords: Sources: Gemini AI as a Keyword Data source.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--ai
* Added: Dynamic Elements: Gemini AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-gemini-ai/
* Added: Research: Gemini AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research
* Added: Settings: Integration: OpenAI: Added GPT-4o, GPT-4 Turbo models and their variants
* Updated: Settings: Integration: OpenAI: GPT-3.5 models and their variants

= 4.5.9 (2024-04-18) =
* Added: Keywords: Sources: Claude AI as a Keyword Data source.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--ai
* Added: Dynamic Elements: Claude AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-claude-ai/
* Added: Research: Claude AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research

= 4.5.8 (2024-04-11) =
* Fix: Generate: Content: Divi: Ensure Layouts and Modules can be inserted
* Fix: Divi: Undefined `ETBackendBuilder` JS error when editing a Page or Post after editing a Content Group

= 4.5.7 (2024-04-05) =
* Fix: Settings: Integrations: Mistral AI: Honor API Key and Model changes when saved
* Fix: Dynamic Elements: Media Library: Output: Don't show Caption, Display Caption, Description, Filename or EXIF options when Create as Copy = No
* Fix: Dynamic Elements: Image URL: Don't include conditional field logic for "Create as Copy", as images are always copied to the Media Library
* Fix: Dynamic Elements: OpenAI Image: Don't include conditional field logic for "Create as Copy", as images are always copied to the Media Library
* Fix: Generate: Content: Divi: PHP Warning: Undefined array key 'copy', caused by Image URL and OpenAI Image Dynamic Elements incorrectly running conditional field logic

= 4.5.6 (2024-04-04) =
* Added: Keywords: Sources: Mistral AI as a Keyword Data source.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--ai
* Added: Dynamic Elements: Mistral AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-mistral-ai/
* Added: Research: Mistral AI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research
* Fix: Performance Addon: Load Elementor and Elementor Pro when Use Performance Addon enabled

= 4.5.5 (2024-03-28) =
* Fix: Generate: Content: 500 Internal Server error when Status = Scheduled, Overwrite = No, Skip if existing Page exists and new Pages are generated

= 4.5.4 (2024-02-26) =
* Added: Generate: Content: SmartCrawl SEO Pro Support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-seo-integration/#smartcrawl-seo---smartcrawl-pro-seo
* Added: Generate: Content: The SEO Framework Support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-seo-integration/#the-seo-framework

= 4.5.3 (2024-01-26) =
* Fix: Dynamic Elements: Gutenberg: Load icons when PHP `allow_url_fopen` disabled
* Fix: Performance: Reduce queries to populate Filter by Content Group dropdown

= 4.5.2 (2024-01-25) =
* Added: Generate: Content: Publish: Option to specify specific date and time from Keyword.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--publish
* Added: Dynamic Elements: Creative Commons: Output Caption option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-creative-commons-image/#configuration--output
* Added: Dynamic Elements: OpenAI Image: Output Caption option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-openai-image/#configuration--output
* Added: Dynamic Elements: Pexels: Output Caption option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pexels/#configuration--output
* Added: Dynamic Elements: Pixabay: Output Caption option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pixabay/#configuration--output
* Added: Dynamic Elements: Wikipedia Image: Output Caption option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-wikipedia-image/#configuration--output
* Fix: Dynamic Elements: Media Library: Honor Link settings
* Fix: Dynamic Elements: Wikipedia: Deprecated: `mb_convert_encoding()` notice in PHP 8.2+
* Fix: Keywords: Improve parsing of CSV URLs and files when data contains HTML

= 4.5.1 (2024-01-18) =
* Fix: Dynamic Elements: Pexels: `page_count(): Response data not valid JSON` error

= 4.5.0 (2024-01-11) =
* Added: Keywords: Sources: Notion as a Keyword Data source.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--notion

= 4.4.9 (2024-01-03) =
* Added: Dynamic Elements: OpenAI: Incresed maximum word limit to 1,000
* Added: Settings: Integration: OpenAI: Added GPT-3.5 Turbo (ChatGPT, Updated) model (gpt-3.5-turbo-1106)
* Removed: Settings: Integration: OpenAI: Removed Ada, Babbage, Curie and Davinci models, which are deprecated by OpenAI from January 4th 2024: https://platform.openai.com/docs/deprecations
* Fix: Generate: Content: Header & Footer: Gutenberg: Fix rendering and save changes

= 4.4.8 (2023-12-11) =
* Fix: Generate: Content: Featured Image: Media Library: Don't delete image from Media Library when overwriting and Create as Copy = No

= 4.4.7 (2023-12-04) =
* Added: Generate: Content: Authentic Theme: Page Layout options
* Added: Dynamic Elements: OpenAI Image (DALL·E 3).  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-openai-image/
* Added: Generate: Content: Featured Image: OpenAI Image (DALL·E 3).  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image

= 4.4.6 (2023-11-30) =
* Fix: Dynamic Elements: Wikipedia Image: Improve detection of relevant images for more matches when selecting an image from Wikipedia for a Term
* Fix: Dynamic Elements: Wikipedia Image: PHP Warning: Undefined property: stdClass::$text

= 4.4.5 (2023-11-22) =
* Added: Dynamic Elements: Google Map: Option to specify map language.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-google-map/
* Fix: Dynamic Elements: Wikipedia: Parsing would return all content due to changes in Wikipedia's content structure
* Fix: Generate: Content: Featured Image: Media Library: Remove errant commas and spaces from Image IDs field to ensure a Featured Image is set

= 4.4.4 (2023-11-16) =
* Added: Settings: Integration: OpenAI: Added GPT-4 Turbo
* Fix: Generate: Content: Don't delete Featured Image when Content Overwrite Section enabled

= 4.4.3 (2023-11-02) =
* Added: Generate: Conditional Output: Option to specify AND ('&&') / OR ('||') operators. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-conditional-output/#logical-operators

= 4.4.2 (2023-10-24) =
* Fix: Generate: Content: Only enqueue buttons, common and form CSS for wizard screens
* Fix: Dynamic Elements: Target CSS for TinyMCE modals to avoid overriding Page Builder CSS

= 4.4.1 (2023-10-18) =
* Fix: Keywords: Database: Improve import of database table rows containing quotation marks and newlines
* Fix: Generate: Conditional Output: Trim non-breaking space characters before evaluating a condition against a Keyword column

= 4.4.0 (2023-10-09) =
* Fix: Keywords: Revert changes to parsing Keywords from 4.3.2 designed for RSS Feeds, as this breaks some CSV files
* Fix: Keywords: Source: RSS: Improve import of RSS feeds containing complex HTML

= 4.3.9 (2023-10-05) =
* Fix: Generate: Content: Elementor Pro: Uncaught Error at `Elementor > Landing Pages`. Re-generate existing Landing Pages to resolve.

= 4.3.8 (2023-10-03) =
* Fix: Research: ArticleForge: Show progress percentage when researching article
* Fix: Research: ArticleForge: Display specific error message when API returns an error

= 4.3.7 (2023-09-07) =
* Fix: Generate via Browser: Show errors in log
* Fix: Generate via Browser: Honor Settings > Generate > Stop on Error when configured to regenerate the Content / Term again
* Fix: Generate: Content: Taxonomies: Ignore blank Taxonomy Terms in `Enter new taxonomy terms` setting

= 4.3.6 (2023-09-01) =
* Fix: Generate: Content: Divi: Only perform Keyword replacement in Global Modules on frontend site

= 4.3.5 (2023-08-31) =
* Added: Generate: Content: Avada Builder: Support for Keywords in Global Modules.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-avada-fusion-builder/#global-elements
* Added: Generate: Content: Divi: Support for Keywords in Global Modules.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-divi/#global-modules

= 4.3.4 (2023-08-24) =
* Added: Generate: Content: SmartCrawl SEO Support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-seo-integration/#smartcrawl-seo---smartcrawl-pro-seo

= 4.3.3 (2023-08-23) =
* Fix: Keywords: Improve parsing of Keyword Terms when no columns and delimiter are specified
* Fix: Generate: Content: Elementor: Don't attempt to replace Keywords in Global Widgets if no Keywords are defined
* Fix: Updated WordPress Coding Standards to 3.0.0

= 4.3.2 (2023-08-17) =
* Added: Settings: Integrations: Airtable: Support Airtable's Personal Access Token.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-integrations/#airtable
* Added: Generate: Content: Elementor: Support for Keywords in Global Widgets.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-elementor/#global-widgets
* Added: Generate: Content: Option to specify Header & Footer code.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--header--amp--footer-code
* Fix: Keywords: Display correct Number of Terms count in table
* Fix: Keywords: Source: RSS Feed: PHP Deprecated notice on addslashes()
* Fix: Keywords: Improve parsing of Keyword Terms when using columns to retain relational data

= 4.3.1 (2023-08-03) =
* Fix: PHP Deprecated notices in PHP 8.2

= 4.3.0 (2023-07-27) =
* Added: Generate: Content: Add New Directory Structure: Region > County > City, Region > City and County > City options.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#add-new-directory-structure
* Fix: Generate: Content: Beaver Builder: Ensure Dynamic Element buttons in Text Editor module show modal
* Fix: PHP Fatal error: Uncaught error "class WP_Error does not have a method posts_filter_by_group" when License has expired

= 4.2.9 (2023-07-20) =
* Added: Settings: Generate: Generate Content Items per Request can now be used when Content Group's Status = Scheduled.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-generate/#generate-content-items-per-request
* Fix: Generate: Terms: Honor Generate Content Items per Request setting
* Fix: Dynamic Elements: Related Links: Check if Post object is truly a WordPress Post before setting default Group and Post

= 4.2.8 (2023-07-13) =
* Added: Settings: Generate: Generate Content Items per Request.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-generate/#generate-content-items-per-request
* Added: Settings: Generate: Trash / Delete Generated Content Items per Request.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-generate/#trash---delete-generated-content-items-per-request
* Added: Generate: Content: Test: Output Time and Memory Usage

= 4.2.7 (2023-07-06) =
* Added: Keywords: Sources: Airtable: Option to specify Table View and Table Fields.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--airtable
* Fix: Keywords: Sources: Airtable: Fetch linked records as strings

= 4.2.6 (2023-06-27) =
* Added: Generate: Content: Page Builders: Support for Yootheme Builder.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-yootheme-builder/
* Added: Generate: Content: Beaver Builder: Copy page builder data into generated Page's post_content, using FLBuilder::render_editor_content().  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-beaver-builder/#common-issues

= 4.2.5 (2023-06-22) =
* Added: Dynamic Elements: Google Map: title attribute to iframe for accessibility
* Added: Dynamic Elements: Related Links: Option to specify comparison operator for Custom Field conditions. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/#configuration--custom-field-conditions
* Added: Pages, Posts: Automatically Generate Spintax from Selected Text.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-spintax/#automatically-generate-spintax
* Fix: Keywords: Generate Locations: Show contextual error message when no Output Type(s) are specified

= 4.2.4 (2023-06-15) =
* Added: Settings: Integration: OpenAI: Updated available GPT-4 and GPT-3.5 models per https://openai.com/blog/function-calling-and-other-api-updates#new-models
* Fix: Dynamic Elements: Wikipedia: Follow 'Redirect to:' header if included in API response, to improve fetching data

= 4.2.3 (2023-06-08) =
* Added: Settings: Spintax: OpenAI: Language support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/spintax-settings/#service--openai
* Fix: Generate: Content: Automatically Generate Spintax from Selected Text: Honor Skip Capitalized Words and Skip Words settings when Service = OpenAI
* Fix: Media Library: Display filter by Content Group dropdown after filtering

= 4.2.2 (2023-06-01) =
* Added: Settings: Integration: OpenAI: Option to choose GPT-4 March 14th models 
* Added: Dynamic Elements: OpenAI: Support for Temperature, Nucleus Sampling, Presence and Frequency Penalty parameters.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-openai/
* Fix: Media Library: Filter attachments by Content Group when selected using dropdown

= 4.2.1 (2023-05-25) =
* Added: Generate via Server.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-server/. WP-CLI is still recommended for server side generation.

= 4.2.0 (2023-05-18) =
* Added: Generate: Content: Add New Content Group using OpenAI: Option to specify language.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#add-new-keyword-and-content-group-using-openai
* Added: Dynamic Elements: OpenAI: FAQ: Output using structured data schema for FAQPage
* Fix: Generate: Content: Add New Content Group using OpenAI: Invalid arguments supplied when creating Keyword and Content Group
* Fix: Dynamic Elements: Classic Editor: Insert button would fail when inserting Dynamic Element into Text Editor, switching to Visual Editor and attempting to insert a second Dynamic Element 
* Fix: Dynamic Elements: OpenAI: Improve performance of OpenAI requests
* Fix: Dynamic Elements: Media Library: Don't permit writing EXIF metadata if not copying image
* Fix: Research: Text Editor: Change 'Insert' button label to 'Run'
* Fix: Research: Text Editor: Perform research when Run button clicked
* Fix: PHP Deprecated notice in add_submenu_page() with PHP 8.1+
* Updated: lsolesen/pel to 0.9.12

= 4.1.9 (2023-05-11) =
* Fix: Dynamic Elements: Related Links: Gutenberg: Use wp.serverSideRender instead of deprecated wp.components.ServerSideRender for WordPress 6.2+

= 4.1.8 (2023-05-10) =
* Fix: Generate: Content: WooCommerce: External/Affiliate Product: Keyword support for Product URL fields
* Fix: Dynamic Elements: YouTube: Gutenberg: Parse oEmbed URL to output video instead of YouTube URL

= 4.1.7 (2023-05-06) =
* Fix: Dynamic Elements: OpenAI: PHP Fatal error: Uncaught TypeError: implode()

= 4.1.6 (2023-05-04) =
* Added: Research: OpenAI: Freeform prompt option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research
* Fix: Dynamic Elements: OpenAI: If a rate limit is hit, wait 60 seconds before attempting a second time.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-openai/#common-issues 
* Fix: Dynamic Elements: OpenAI: Allow HTML tags to be output
* Fix: Dynamic Elements: OpenAI: Only apply paragraphs to output sections that have no HTML markup included in the OpenAI response

= 4.1.5 (2023-04-20) =
* Added: Keywords: Third Party Sources: Option to refresh data.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--viewing-terms
* Added: Dynamic ELements: OpenAI: Freeform prompt option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-openai/#configuration
* Added: Dynamic Elements: OpenAI: Register as Elementor widget
* Added: Dynamic Elements: Wikipedia Image: Option to return first image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-wikipedia-image/#configuration--search-parameters
* Added: Generate: Content: Featured Image: Wikipedia Image: Option to return first image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Added: Generate: Content: Gutenberg: Automatically Generate Spintax from Selected Text.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-spintax/#automatically-generate-spintax
* Fix: Dynamic Elements: OpenAI: Display icon in Gutenberg block
* Fix: CLI: Generate: Content: PHP Fatal error when using OpenAI Dynamic Element

= 4.1.4 (2023-04-12) =
* Fix: OpenAI: PHP Fatal error: Uncaught Error: Call to undefined method WP_Error::get_content_types()

= 4.1.3 (2023-04-06) =
* Added: Dynamic Elements: OpenAI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-openai/
* Fix: Generate via Browser: Show error notice instead of PHP warning when no Content Group ID specified

= 4.1.2 (2023-03-30) =
* Added: Keywords: Sources: OpenAI as a Keyword Data source.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--openai

= 4.1.1 (2023-03-24) =
* Added: Research: OpenAI: Option to specify output language.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research
* Fix: Research: OpenAI: Return text in spintax format when "Return as Spintax" enabled for the Article Content Type 
* Fix: Settings: Integrations: PHP error when specifying OpenAI Key and Settings > Research never previously configured

= 4.1.0 (2023-03-16) =
* Added: Settings: Integrations: OpenAI support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-integrations/#openai
* Added: Settings: Spintax: OpenAI support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/spintax-settings/#service--openai
* Added: Generate: Content: Add New Content Group using OpenAI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#add-new-keyword-and-content-group-using-openai
* Added: Research: OpenAI: Option to select models, including GPT-3.5 (ChatGPT).  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-research/#settings--openai
* Added: Research: Improved UI for entering topic.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research--classic-editor
* Added: Research: Content Type and Word Count options when using OpenAI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/#research--classic-editor
* Fix: Research: Elementor: Show progress indicator and modal overlay when performing research
* Fix: Keywords: Workaround conflict caused when Contact Form CFDB7 active which resulted in "Invalid nonce..!!" error returned from CFDB7
* Fix: Generate: Content: Featured Image: Fixed "page_count(): Ensure this value is less than or equal to 20" error when using Creative Commons
* Fix: Logs: Filter by System would not work
* Fix: Logs: Export Log: Don't attempt to convert Keyword Terms to string if no keywords were specified in the Group
* Fix: Import & Export: Improved handling when no Keywords, Content Groups or Term Groups exist
* Fix: Import & Export: Export: Corrected ID on Settings label
* Fix: Improve WordPress Coding Standards and PHP 8.0+ compatibility

= 4.0.4 (2023-02-16) =
* Fix: Generate: Content: Menu: Don't duplicate generated Pages in Menu if menu items already exist

= 4.0.3 (2023-02-06) =
* Fix: Call to undefined method WP_Error::log_cleanup()

= 4.0.2 (2023-02-02) =
* Added: Generate: Content: Dynamic Elements: Google Maps: Option to define center point when Map Type = Location, Location without Marker or Place(s) / Business(es) in Location.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-google-map/
* Fix: Generate: Content: Elementor 3.9.0+ compatibility

= 4.0.1 (2023-01-20) =
* Fix: Remove orphaned Generate via Server / Cron code

= 4.0.0 (2023-01-19) =
* Notice: PHP 7.4 is the minimum required version
* Fix: Keywords: Third Party Sources: Improved Preview when handling large data, with option to scroll horizontally within table
* Fix: Generate: Content: PHP Fatal Error when Post Meta value is an array
* Fix: Keyword Transformations: Don't attempt :random transformation if Keyword does not exist
* Updated: League\Csv Library to 9.8.0
* Updated: kwn\NumberToWords Library to 2.6.1
* Updated: PHPOffice\PHPSpreadsheet Library to 1.26.0
* Updated: PSR\SimpleCache Library to 3.0.0

= 3.9.9 (2023-01-11) =
* Removed: Generate via Server.  WP-CLI is recommended for server side generation.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/
* Fix: Dynamic Elements: Related Links: Improve method for fetching current Post ID
* Fix: Dynamic Elements: Wikipedia: Replace footnotes with blank string instead of `null`
* Fix: Generate: Content: WooCommerce: Calculate display price after Product generation to honor Regular + Sale Prices

= 3.9.8 (2023-01-03) =
* Added: Generate: Content: WooCommerce: Keyword support for Stock Quantity, Weight and Dimensions fields
* Fix: Generate: Content: WooCommerce: Display Keyword in Price fields after saving

= 3.9.7 (2022-12-19) =
* Fix: Keywords: Save: Replace utf8_encode() with mb_convert_encoding() for PHP 8.2 compatibility
* Fix: Keyword Autocompleters: Don't initialize in Gutenberg / Block Editor text blocks when editing Pages or Posts
* Fix: Generate: Content: Keyword Autocomplete: Don't initialize if no Keywords defined
* Fix: Generate: Content: Keywords: PHP Deprecated notice for count() and getIterator()
* Fix: Generate: Content: Gutenberg: PHP Deprecated notice for strpos() when a null block is encountered
* Fix: Generate: Content: Replace utf8_encode() with mb_convert_encoding() for PHP 8.2 compatibility
* Fix: Dynamic Elements: Wikipedia: PHP Fatal Error when Terms are blank

= 3.9.6 (2022-12-06) =
* Fix: Dynamic Elements: Yelp: Call to undefined method WP_Error::get_rating_options()
* Fix: Generate: Content: 500 Internal Server error when using AIOSEO 4.2.8

= 3.9.5 (2022-12-01) =
* Added: Generate: Content: Breakdance Builder support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-breakdance-builder/
* Added: Generate: Content: Squirrly SEO Support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-seo-integration/#squirrly-seo

= 3.9.4 (2022-11-19) =
* Fix: Dynamic Elements: YouTube: Render video when using shortcode in Elementor
* Fix: Dynamic Elements: Classic Editor: Improved method to insert shortcode into correct TinyMCE or Text editor when multiple instances exist

= 3.9.3 (2022-11-10) =
* Fix: Dynamic Elements: Related Links: Check Radius is a number before attempting to perform calculations to fetch Related Links
* Fix: Dynamic Elements: Related Links: Classic Editor: Taxonomies: Display fields when clicking Add button to define Taxonomy conditions
* Fix: Dynamic Elements: Related Links: Classic Editor: Custom Fields: Display fields when clicking Add button to define Custom Field conditions
* Fix: Spintax: WordAI: Generate Spintax from Selected Text functionality would fail when specifying WordAI as provider

= 3.9.2 (2022-10-20) =
* Added: Settings: Research: OpenAI GPT-3 support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-research/#settings--openai
* Removed: Generate: Content: Apply Synonms.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generation--spin-content

= 3.9.1 (2022-10-12) =
* Added: Settings: Research: ArticleForge support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-research/#settings--articleforge
* Added: Research: Improved method to register third party providers
* Added: Spintax: Improved method to register third party providers
* Fix: Generate: Content: Dynamic Elements: Classic Editor: Keyword autocomplete and conditional fields would fail to initialize 
* Fix: Licensing: Uncaught Error: Attempt to modify property "response" on null

= 3.9.0 (2022-10-06) =
* Added: Keywords: Generate Locations: Exclusions: Option to specify exclusions when using the Radius method.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/#radius
* Removed: Settings: General: CSS Prefix: Prefix is now automatically generated.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/general-settings/#css-prefix
* Fix: Keywords: Generate Locations: Exclusions: Prevent timeout and improve query performance when specifying multiple exclusions
* Fix: Dynamic Elements: Related Links: Fallback to all Post Types if no Post Type specified and cannot determine Post Type based on where Related Link element is placed
* Fix: Dynamic Elements: PHP Fatal Error when third party Plugins define DOING_CRON on every frontend request

= 3.8.9 (2022-09-26) =
* Fix: Generate via Browser: Generate when Resume Index is greater than No. Pages
* Updated: Porgues Brasil / Portuguese Brazilian Translations

= 3.8.8 (2022-09-06) =
* Added: Generate: Content: Keyword Autocomplete: Gutenberg: Support for Keyword Autocomplete on Title field

= 3.8.7 (2022-08-25) =
* Added: Keywords: Generate Locations: Option to sort by City Population, when City Population is specified as an Output Type.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/#add-a-new-location-keyword
* Added: Generate: Content: Add New Directory Structure: Region > City > Service option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#add-new-directory-structure
* Added: Generate: Content: Add New Directory Structure: Option to filter Cities by Population and define exclusions.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#add-new-directory-structure
* Fix: Keywords: Generate Locations: Honor "Sort Terms" settings
* Fix: Generate: Content: Add New Directory Structure: For performance, require one or more Regions or Counties to be specified when constraining by area
* Fix: Generate: Content: Parent Page: Don't treat forwardslash in a Keyword Term as a path if it has spaces either side of it e.g. "Metro / Second and Hume", to better detect Parent Pages
* Fix: Generate via Browser: Miscalculation of end index when Resume Index specified, which would result in "All possible keyword term combinations have been generated" error after all items correctly generated

= 3.8.6 (2022-08-17) =
* Fix: Import as Content Group: Don't attempt to validate source as a Content Group, which would result in a fatal error

= 3.8.5 (2022-08-11) =
* Fix: Dynamic Elements: Related Links: White screen in modal when using Classic Editor and PHP 8.x
* Fix: Generate: Content: PHP Warning:  Undefined variable $menu

= 3.8.4 (2022-08-11) =
* Added: Generate: Content: Attributes: Parent: Validate Parent value to prevent unsupported characters being included
* Added: Generate: Content: Check ID is a Content Group, and show an error if not across browser, server and CLI
* Added: Generate: Terms: Check ID is a Content Group, and show an error if not across browser, server and CLI
* Updated: Porgues Brasil / Portuguese Brazilian Translations

= 3.8.3 (2022-08-04) =
* Fix: Dynamic Elements: Honor spaces in fields / attributes

= 3.8.2 (2022-07-28) =
* Added: Generate: Content: Support for All in One Video Gallery
* Added: Dynamic Elements: Media Library: Option to output caption.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-media-library-image/#configuration--output
* Fix: Dynamic Elements: Wikipedia: Convert relative /wiki/... links to absolute xx.wikipedia.org/wiki/... links

= 3.8.1 (2022-07-26) =
* Added: Generate: Content: BeTheme compatibility for 26.x+.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-betheme-muffin-page-builder-integration/

= 3.8.0 (2022-07-07) =
* Added: Generate: Content: Page Builders: Support for Bricks Visual Website Builder.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-bricks/
* Fix: Related Links: Persistent Caching: Don't automatically load cache from WordPress option table unless requested

= 3.7.9 (2022-07-01) =
* Fix: Dynamic Elements: Gutenberg: Correctly generate when Hybrid Composer Page Builder not active/installed

= 3.7.8 (2022-06-30) =
* Added: Generate: Content: Page Builders: Support for Hybrid Composer Page Builder
* Added: Generate: Content: Support for Landkit Theme
* Fix: Keywords: Generate Phone Area Codes: Honor default country specified at Settings > General > Country Code when first loading
* Fix: Generate: Content: Gutenberg: Don't display Gutenberg's Page Attributes and Template editor panels, as Page Generator Pro supplies its own UI for these options
* Fix: Improve WordPress Coding Standards

= 3.7.7 (2022-06-23) =
* Fix: Removed clipboard.js, as WordPress provides this library
* Fix: Use sanitize_sql_orderby() when defining order by parameter across Keyword, Log, Content Group and Term Group tables
* Fix: Generate: Content: Test Mode: Honor Resume Index when using Test link in Content Groups table or Gutenberg editor
* Fix: Generate: Terms: Test Mode: Honor Resume Index when using Test link in Term Groups table
* Fix: Logs: Don't attempt to output Keywords/Terms when the log is an error stating "All possible keyword term combinations have been generated."

= 3.7.6 (2022-06-19) =
* Fix: Dynamic Elements: Default "Save to Library" to false, to honor in Gutenberg when not enabled
* Fix: Dynamic Elements: Creative Commons: Limit results per page when performing unauthenticated API request to avoid API errors

= 3.7.5 (2022-06-10) =
* Fix: Dynamic Elements: Wikipedia: Strip out /wiki/ when specifying source URL as a Term, preventing "page you specified doesn't exist" errors

= 3.7.4 (2022-06-09) =
* Added: Generate: Content: Oxygen Builder 4.x: Process Oxygen's JSON instead of Oxygen's shortcodes
* Fix: Dynamic Elements: Related Links: Preview: When editing a Content Group, don't attempt to limit by radius if Keywords specified in Geolocation
* Fix: Dynamic Elements: Related Links: Support radius values less than 1 mile

= 3.7.3 (2022-06-02) =
* Fix: Generate: Content: Test button not rendering correctly when Rank Math Plugin active

= 3.7.2 (2022-06-02) =
* Added: Generate: Terms: Research Content and Generate Spintax buttons in TinyMCE editor for the Term's Description
* Fix: Keywords: Add/Edit: Strip slashes from quotation marks when adding/editing a Keyword fails validation
* Fix: Keywords: Search: Strip slashes from 'Search results for' label
* Fix: Generate: Content: Add New Directory Structure: Strip slashes from services field when creating a directory fails validation
* Fix: Generate: Terms: Failed to initialize plugin errors in description field
* Fix: Logs: Search: Strip slashes from 'Search results for' label
* Fix: Ensure views meet WordPress Coding Standards

= 3.7.1 (2022-05-21) =
* Fix: Generate: Content: Random Method: Don't fetch Term at random when Keyword contains a single Term, which would result in a blank Term being returned 
* Fix: Generate: Multilingual Content: WPML: Tooltip over WPML's "Translate" plus icon would incorrectly cover the icon

= 3.7.0 (2022-05-19) =
* Added: Dynamic Elements: Related Links: Option to specify latitude and longitude as centre of radius.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/#configuration--geolocation
* Added: Generate via Browser: Show dialog confirmation if navigating away from generation window whilst generation is running
* Added: Generate via Browser: Remove 'Generating' flag on Content Group if navigated away from generation window whilst generation is running
* Fix: Keywords: Source: CSV URL: Show error if CSV URL is not CSV data of type text/csv
* Fix: Generate: Content: Classic Editor: Use absolute URLs for Research and Generate Spintax from selected text button icons
* Fix: Dynamic Elements: Remove double forwardslash on icon URLs

= 3.6.9 (2022-05-12) =
* Added: Settings: Integrations: Yelp API Key option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-integrations/#yelp
* Added: Dynamic Elements: Yelp: Option to link Business listings to Yelp.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-yelp-business-listings/#configuration--output
* Added: Dynamic Elements: Gutenberg: Display preview when adding a Dynamic Element block from Gutenberg's left hand sidebar 
* Fix: Dynamic Elements: Text Editor: Quick Tags: Load backbone modal in footer, not header 
* Fix: Dynamic Elements: Yelp: Minimum Rating: Typo on 'or higher'
* Fix: Generate: Content: Honor Schedule Increment setting
* Fix: Keyword Transformations: Use case insensitive search/replace when using :random_different modifier, to ensure Keyword is replaced with a Term
* Fix: Multisite: Activation: When using wp_insert_site, get blog ID from WP_Site before running activation routine
* Fix: Multisite: Activation: Conditionally load required hook depending on WordPress version

= 3.6.8 (2022-05-05) =
* Added: Generate: Content: Divi: Block Spintax support when used in Divi's code module
* Added: Dynamic Elements: Related Links: Elementor: Output preview when editing a Content Group or Page
* Fix: Dynamic Elements: Elementor: Don't register non-Related Links widgets outside of Content Groups
* Fix: Dynamic Elements: Gutenberg: Set numerical setting field to zero when blank, to prevent non-recoverable block error

= 3.6.7 (2022-05-03) =
* Added: Dynamic Elements: Gutenberg: Open settings sidebar if closed and focused/clicked on a Dynamic Element
* Added: Dynamic Elements: Classic Editor: Added scrollbar to modal window and fixed positioning of Insert / Cancel buttons, to support smaller screen resolutions
* Added: Dynamic Elements: Related Links: Reinstated Order By = Random, using non-database method to randomize output order for better performance
* Fix: Dynamic Elements: Wikipedia: Remove "Edit data on Wikidata" text
* Fix: Dynamic Elements: JS error when using Classic Editor block/widget in Page Builders

= 3.6.6 (2022-04-28) =
* Added: Generate: Content: Export Generated Content via WP All Export Pro.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-export-generated-content/
* Added: Generate: Content: Trash and Delete Generated Content: Show number of Pages/Posts that will be trashed/deleted in confirmation dialog
* Added: Generate: Terms: Delete Generated Terms: Show number of Terms that will be deleted in confirmation dialog
* Added: Dynamic Elements: Gutenberg: Display icon, title and description in block when editing a Content Group
* Added: Dynamic Elements: Related Links: Gutenberg: Output preview when editing a Content Group or Page
* Fix: Dynamic Elements: Related Links: Gutenberg: Output links when used outside of a Content Group on e.g. Pages
* Fix: Dynamic Elements: Related Links: Gutenberg: Support pipe symbol as a delimiter
* Fix: Dynamic Elements: Related Links: Removed Order By = Random due to poor database performance when using ORDER BY RAND()
* Fix: Divi: Generate: Content: PHP Warning: Undefined offset: 0
* Fix: Divi: Dynamic Elements: Removed Related Links Module to permit Divi's frontend builder to load on Pages.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-divi/#backend---frontend-editing
* Fix: Generate: Content: All in One SEO: Define metadata on generated Pages

= 3.6.5 (2022-04-25) =
* Fix: Dynamic Elements: Related Links: Don't attempt to assign parent page constraint if none defined

= 3.6.4 (2022-04-24) =
* Added: CLI: Trash Generated Content: Option to exclude Post IDs from trash.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/#trash-generated-content
* Added: CLI: Delete Generated Content: Option to exclude Post IDs from deletion.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/#delete-generated-content
* Added: CLI: Delete Generated Terms: Option to exclude Term IDs from deletion.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/#delete-generated-terms
* Added: Generate: Content: Elementor: Dynamic Elements: Display icons for each dynamic element widget 
* Added: Generate: Content: OptimizePress Builder: Clear OptimizePress' page and asset caches after generation
* Added: Generate via Browser: Update browser tab title with generation progress
* Added: Dynamic Elements: Creative Commons: Sources option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-creative-commons-image/#configuration--search-parameters
* Fix: Dynamic Elements: Creative Commons: Don’t return a 500 error if only one page of results found
* Fix: Generate: Content: Dynamic Elements: Classic Editor: Array to string conversion PHP warning 
* Fix: Import as Content Group: Verify CSRF token for security to permit import action
* Fix: Support link would not redirect to support page

= 3.6.3 (2022-04-21) =
* Added: Generate: Content: Dynamic Elements: Media Library: Option to search by Filename.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-media-library-image/#configuration--search-parameters
* Fix: Keywords: Source: CSV File: Permit changing CSV file when editing an existing Keyword
* Fix: Keywords: Source: CSV URL: Show error if CSV URL is removed when editing an existing Keyword
* Fix: Keywords: Source: RSS Feed: Improved error message when no RSS Feed is specified
* Fix: Keywords: Source: Spreadsheet: Improved detection if no spreadsheet specified
* Fix: Keywords: Bulk and Row Actions: Verify CSRF token for security before performing chosen action
* Fix: Keywords: Search: Could not change search term once entered and submitted due to CSS blocking
* Fix: Generate: Content: Add New Directory Structure: Refresh Regions / States when Area selected and Country is changed
* Fix: Generate: Content: Bulk and Row Actions: Verify CSRF token for security before performing chosen action
* Fix: Generate: Content: Bulk and Row Actions: Retain Search, Order and Order By parameters after performing chosen action
* Fix: Generate: Content: Bulk Actions: Display text in success notification
* Fix: Generate: Content: Duplicate Content Group: Link to view duplicated Content Group pointed to invalid Group ID
* Fix: Generate: Content: Classic Editor: Improved loading of Dynamic Element buttons in Classic Editor instances
* Fix: Generate: Content: Classic Editor: Load Dynamic Element icons when SCRIPT_DEBUG is enabled
* Fix: Dynamic Elements: Gutenberg: Honor default values when adding a new Dynamic Element
* Fix: Dynamic Elements: Gutenberg: Sort select options alphabetically
* Fix: Dynamic Elements: Media Library: Gutenberg: Changed Operator from autocomplete to select dropdown for improved UX
* Fix: Generate via Browser: Verify CSRF token for security before performing chosen action
* Fix: Logs: Bulk Actions: Retain Search, Order and Order By parameters after performing chosen action
* Fix: Pages: Uncaught ReferenceError JS errors when editing a Page or Post
* Fix: Ensure code meets WordPress Coding Standards

= 3.6.2 (2022-04-07) =
* Fix: Generate: Content: Metabox.io: Only register Metaboxes for Post Types generated by Content Groups

= 3.6.1 (2022-03-31) =
* Fix: Keywords: Sources: Airtable: Fetch all rows where table contains more than 100 rows
* Fix: Keywords: Sources: Airtable: Fetch all available fields as column names, as some rows may not have data for all columns specified

= 3.6.0 (2022-03-17) =
* Fix: Divi: Theme Builder: Don't save Divi Theme as WooCommerce Product

= 3.5.9 (2022-03-03) =
* Added: Generate: Content: Permalink: Validate that Keyword syntax is valid prior to Test / Generation
* Fix: Generate: Content: Keyword Autocomplete: Classic Editor: Up/down/enter key changes introduced in 3.5.7 which were reverted in 3.5.8
* Fix: Generate via Browser: Show error message preventing Content Group generation instead of "Page Generator Pro: ReferenceError: page_generator_pro_generate_browser is not defined" error
* Fix: Multisite: Activation: Use wp_insert_site hook when available in WordPress 5.1 and higher

= 3.5.8 (2022-02-24) =
* Fix: Keyword Transformations: Number to Words: Remove comma from Term before attempting transformation
* Fix: Keyword Transformations: Currency to Words: Remove comma from Term before attempting transformation

= 3.5.7 (2022-01-27) =
* Added: Generate: Content: Keyword Autocomplete: Classic Editor: Up and down keys can be used to select highlighted autocomplete suggestion
* Added: Generate: Content: Keyword Autocomplete: Classic Editor: Insert first displayed Keyword suggestion when enter key pressed
* Fix: Generate: Content: Keyword Autocomplete: Classic Editor: Don't show autocompleter when left square bracket key pressed
* Fix: Generate via Server and CLI: Honor Dynamic Element settings for correct output when using a Plugin Dynamic Element in Elementor and Divi
* Fix: Generate: Content: WooCommerce: Save Product Type setting

= 3.5.6 (2022-01-20) =
* Added: Generate: Content: JetEngine Meta Box Support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-custom-field-plugins/#jetengine

= 3.5.5 (2022-01-13) =
* Added: Settings: Research: ContentBot.ai support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-research/#settings--contentbot
* Added: Keyword Transformations: Number to Words.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#number-to-words-transformations
* Added: Keyword Transformations: Currency to Words.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#currency-to-words-transformations
* Added: Generate: Terms: Parent Term can comprise of multiple Terms expressed as a Term Path.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-terms/#fields--parent-term
* Fix: Settings: Spintax: ChimpRewriter: corrected link to find API Key
* Fix: Generate: Content: Detect Keywords in Gutenberg Blocks and Page Builders that use nested JSON strings to store data
* Fix: Generate: Terms: Confirmation dialogs when performing Test or Generate functions
* Fix: Generate: Terms: Notice: Undefined variable: post when editing a Term Group
* Fix: Generate: Terms: Notice: Undefined index: date_option when generating a Term Group

= 3.5.4 (2022-01-06) =
* Fix: Generate: Content: Rank Math: When editing a Content Group with Rank Math enabled, change Test and Generate buttons to links, due to Rank Math 1.0.78+ wrongly removing submit buttons when the Content Group form is submitted.
* Fix: Dynamic Elements: Wikipedia: Improved text alignment on Terms dropdown field

= 3.5.3 (2021-12-22) =
* Fix: Dynamic Elements: Creative Commons: Updated API endpoint to prevent JSON errors
* Fix: Dynamic Elements: Wikipedia: Don't show Keyword autocomplete suggestions on Term field
* Fix: Import and Export: Import Keyword Source and Source Options (i.e. CSV URL / File)

= 3.5.2 (2021-12-02) =
* Added: Keywords: Add/Edit: Use WordPress Code Editor for Terms for improved editing, readibility and search.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--local
* Fix: Keywords: Generate Locations: Encapsulate Term if it contains delimiter character

= 3.5.1 (2021-11-18) =
* Fix: Dynamic Elements: Wikipedia: Remove inline style and link elements from text, to prevent unexpected line breaks in content
* Fix: Generate: Content: Oxygen Builder: Honor Back to WP > Admin when clicked
* Fix: Generate: Content: Oxygen Builder: Prevent WordPress Form styling overriding element ID / class input field

= 3.5.0 (2021-11-11) =
* Added: Generate: Content: Support for WPTouch Pro and Mobile Content Addon.
* Fix: Keyword Autocompleters: Don't initialize autocompletors on search dropdown fields, which would prevent results displaying for e.g. Group Parent when "Change Page Dropdown Fields" = Search Dropdown Field.

= 3.4.9 (2021-11-04) =
* Fix: Settings: Integrations: Honor API Key when specified for OpenWeatherMap, Pexels and Pixabay
* Fix: Dynamic Elements: Pexels: Display more precise error message when in Test mode
* Fix: Import and Export: Include Settings > Integrations data

= 3.4.8 (2021-11-01) =
* Fix: Dynamic Elements: Google Maps: Undefined index show_place_card_marker warning
* Fix: Divi: Divi 4.12+ compatibility for Divi Frontend Editor on non-Content Groups i.e. Pages
* Removed: Generate: Content: Divi: Frontend Editor Support. Backend editor support remains. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-divi/#backend---frontend-editing

= 3.4.7 (2021-10-28) =
* Fix: Dynamic Elements: YouTube: Honor API Key when specified at Settings > Integrations > Youtube Data API Key
* Fix: Dynamic Elements: YouTube: Display more verbose error message when in Test mode

= 3.4.6 (2021-10-21) =
* Added: Licensing: Improved verification method when OpenSSL < 1.1.0 and/or web host continues to use an expired DST Root CA X3.  See Docs: https://www.wpzinc.com/documentation/installation-licensing-updates/entering-license-key/#common-issues
* Added: Generate: Content: OptimizePress Builder Support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-optimizepress/
* Added: Block Spintax: Option to disable randomizing paragraph order within a section.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-spintax/#block-spinning--sections--disable-randomizing-paragraph-order
* Added: Block Spintax: Option to require specific paragraphs within a section when using min/max arguments.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-spintax/#block-spinning--require-paragraph-s--when-using-minimum---maximum-paragraph-limits
* Fix: Dynamic Elements: Wikipedia: Improved fetching content when Term contains accented characters and language isn't English

= 3.4.5 (2021-10-14) =
* Added: Keywords: Import: Spreadsheet option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-import-csv/
* Added: Keywords: Third Party Sources: Spreadsheet option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--spreadsheet
* Fix: Generate: Content: Store Keywords setting could not be disabled

= 3.4.4 (2021-10-07) =
* Added: Keywords: Improved parsing and importing more complex CSV files
* Added: Keywords: Third Party Sources: Improved Preview when handling large data, with option to scroll horizontally within table

= 3.4.3 (2021-09-23) =
* Added: Related Links: Persistent Caching option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/general-settings/#persistent-caching
* Added: Generate: Content: All in One SEO: Support for Focus and Additional Keyphrases
* Fix: Keywords: Correctly escape Keyword Name
* Fix: Licensing, Settings, Logs, Import & Export: Correctly escape form action
* Updated: Porgues Brasil / Portuguese Brazilian Translations

= 3.4.2 (2021-09-16) =
* Fix: Dynamic Elements: Related Links: Classic Editor: Add button on Taxonomies and Custom Fields tabs would incorrectly close modal window when used on non-Content Groups
* Fix: Generate: Content: Popup Maker: Populate Display > Apperance > Popup Theme options
* Fix: Generate via Server: Honor Page Template setting
* Fix: Block Spintax: Don't trim non-block spintax strings (resolves issue with Enfold setting values being trimmed when trailing spaces must be retained)
* Fix: Generate via Browser: PHP 8 compatibility when Number of Pages and/or Resume Index settings are blank

= 3.4.1 (2021-09-09) =
* Added: Generate: Content: Popup Maker Support
* Added: Generate: Content: Zion Builder Support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-zion-builder/
* Added: Generate: Content: Dynamic Elements: Strip HTML from parameter values
* Fix: WooCommerce: Variations could not be added to a Variable Product in WooCommerce
* Updated: Porgues Brasil / Portuguese Brazilian Translations

= 3.4.0 (2021-09-02) =
* Added: Keyword Transformations: Output Random Term Subsets in a List.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#output-random-term-subsets-in-a-list
* Added: Generate: Content: Dynamic Elements: Google Maps: Support for multiple place markers and direction modes (driving/walking/transit etc).  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-google-map/
* Added: Generate: Content: Featured Image: Wikipedia Image: Include images that partially match supplied Term.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Added: Generate: Content and Terms: Use improved hrtime() for measuring performance if available
* Fix: Generate via Server: Improved checking if a Page Builder, SEO, Schema Plugin is active, ensuring its data is correctly copied/not copied to generated items
* Updated: Porgues Brasil / Portuguese Brazilian Translations

= 3.3.9 (2021-08-26) =
* Added: Generate: Content: Overwrite Sections: Template option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generation--overwrite-sections
* Added: Generate: Content: Overwrite Sections: GoodLayers Page Options.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-goodlayers/#page-options
* Added: Generate: Content: GoodLayers Infinite Theme Page Options.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generation--overwrite-sections--third-party-plugins
* Added: Generate: Content: Skip setting a Template, Featured Image or Discussion options if not supported by the generated Post Type
* Added: Generate: Content: WooCommerce: Support for Grouped and External/Affiliate Products
* Added: Porgues Brasil / Portuguese Brazilian Translations
* Changed: Generate: Content: Template option moved from Attributes to Template Meta Box.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--template
* Fix: Generate: Content: Improved performance of conditionally showing/hiding options based on the generated Post Type
* Fix: Generate: Content: Overwrite Sections: Honor Content overwriting setting for GoodLayers Page Builder
* Fix: Generate: Content: Improved JS performance with Generate via Browser

= 3.3.8 (2021-08-19) =
* Added: Generate: Content: Overwrite Sections: Honor Content overwriting setting for supported Page Builders
* Fix: Generate: Content: Don't process Dynamic Elements in Content / Page Builders if Content Group set not to overwrite existing generated content, for performance

= 3.3.7 (2021-08-12) =
* Added: Spintax: Support for WordAI 5. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/spintax-settings/#service--wordai
* Added: Generate: Content: Salient Theme 13.0+ Support
* Added: Generate: Content: Overwrite: Options to enable/disable overwriting for third party Plugins: SEOPress.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generation--overwrite-sections--third-party-plugins
* Added: Generate: Content: Ignore Keywords and don't copy data to Generated Pages from inactive ('old') Page Builder, SEO, Schema data etc.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/performance/#orphaned-metadata
* Fix: Generate: Content: Store Keywords: Keywords with no columns would wrongly have their Term added twice to Generated Page's Metadata

= 3.3.6 (2021-08-05) =
* Added: Generate: Content: Menu: Parent: Option to specify Menu ID.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--menu

= 3.3.5 (2021-07-29) =
* Added: Keywords: Sources: RSS Feeds as a Keyword Data source.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--rss-feed
* Fix: Keywords: Sources: Airtable: Terms added/edited to Airtable after Keyword creation would assign to the wrong column 
* Fix: Generate: Content: Add New Directory Structure: Honor Country selection
* Fix: Generate: Content: Add New Directory Structure: Don't limit number of Locations to 10,000
* Fix: PHP Deprecated notice: Required parameter $block_attributes follows optional parameter $block_name 

= 3.3.4 (2021-07-22) =
* Added: Keywords: Sources: Airtable as a Keyword Data source.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--source--airtable
* Fix: Generate: Content: Dynamic Elements: PHP deprecated notices for `block_categories` filter in WordPress 5.8
* Fix: Links to Documentation now consistently open in new window/tab
* Fix: Invalid/out of date Documentation Links
* Removed: Settings: Google: Google Maps API Key: Google Map Dynamic Element for Road Map, Satellite, Driving Directions and Street View is now free from Google with unlimited usage, so the API key is no longer needed

= 3.3.3 (2021-07-15) =
* Fix: Licensing: Quicker method to check license key for performance

= 3.3.2 (2021-07-12) =
* Fix: Spintax: Removed expected second argument for filtering Excerpt, resolving for Page Builders that incorrectly implement get_the_excerpt filter calls

= 3.3.1 (2021-07-08) =
* Added: Keyword Transformations: Output Nearby Terms in a List.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#output-nearby-terms-in-a-list
* Added: Keyword Transformations: Output Same Random Terms in a List.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#output-same-random-terms-in-a-list
* Fix: Spintax: Better filtering of Title, Excerpt and Content when processing spintax on non-Page Generator Pro Pages

= 3.3.0 (2021-07-01) =
* Added: Dynamic Elements: OpenWeatherMap: Support for ZIP Code as Location. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-openweathermap/#configuration
* Added: Generate: Content: Add New Directory Structure: Support for additional structures.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#add-new-directory-structure

= 3.2.9 (2021-06-24) =
* Added: Generate: Content: Add New Directory Structure.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#add-new-directory-structure
* Added: Settings: General: Option to enable/disable frontend CSS.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/general-settings/#output-css
* Added: Settings: Spintax: Option to process Block Spintax and/or Spintax detected on non-Page Generator Pro Pages, Posts and Custom Post Types.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/spintax-settings/#process-on-frontend
* Fix: Don't minify Plugin Javascript if a third party minification Plugin is active

= 3.2.8 (2021-06-23) =
* Added: Generate: Conditional Output.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-conditional-output/

= 3.2.7 (2021-06-14) =
* Fix: Uncaught Error: Class 'League\Csv\Reader' not found when open_basedir() restrictions are in effect

= 3.2.6 (2021-06-03) =
* Fix: Import as Content Group: PHP warnings when metadata isn't a string
* Fix: Keyword Autocompleters: Don't initialize autocompletors if no Keywords have been specified

= 3.2.5 (2021-05-27) =
* Added: Generate: Content: Platinum SEO Support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-seo-integration/#platinum-seo---platinum-seo-pro
* Added: Generate: Terms: Platinum SEO Support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-seo-integration/#platinum-seo---platinum-seo-pro
* Added: Option to filter WP_List_Table by Pages/Posts/Custom Post Types not generated by a Content Group. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-run/#view-generated-content

= 3.2.4 (2021-05-13) =
* Added: Generate: Content: Keyword Autocomplete: Extended support for autocomplete suggestions to most third party Plugins.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#autocomplete-suggestions
* Added: Generate: Terms: Keyword Autocomplete: Extended support for autocomplete suggestions to most third party Plugins.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#autocomplete-suggestions
* Fix: Generate: Content: Set Delete Generated Content Button text to white color
* Fix: Generate: Terms: Set Delete Generated Terms Button text to white color

= 3.2.3 (2021-05-06) =
* Fix: Generate: Content: Thrive Architect: Remove duplicate Page Builder content when using a Thrive Landing Page prior to generation for better performance and no duplication of shortcodes, keywords and spintax processing
* Fix: Generate: Content: Set Delete Generated Content link to red color
* Fix: Generate: Terms: Set Delete Generated Terms link to red color
* Fix: Block Spintax: Remove newlines between multiple #s# elements within a #p# element

= 3.2.2 (2021-04-29) =
* Added: Generate: Content: Author: Changed Rotate to Random, choosing a WordPress User at random when enabled
* Added: Generate: Content: Keyword Autocomplete: Improved autocomplete for Keyword suggestions with better search and UI
* Added: Generate: Content: Keyword Autocomplete: Added to Custom Field Value, Comment, Menu Title and Menu Parent fields
* Added: Generate: Terms: Keyword Autocomplete: Improved autocomplete for Keyword suggestions with better search and UI
* Fix: Generate: Content: Keyword Autocomplete: Improved list position when using the Classic Editor and starting a paragraph/newline with a Keyword
* Fix: Generate: Content: Keyword Autocomplete: Improved list position when using a Classic Editor Block in Gutenberg

= 3.2.1 (2021-04-22) =
* Fix: Block Spintax: Improved checks to detect block spintax for performance and improved compatibility with e.g. GeneratePress
* Fix: Block Spintax: Don't strip newlines which would result in paragraphs being merged

= 3.2.0 (2021-04-17) =
* Added: Generate: Content: WPSSO Integration.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-seo-integration/#wpsso
* Fix: Generate: Content: All Method would result in generation index out of bounds when using Resume Index on some server configurations
* Fix: Generate: Content: Random Method: Improved randomization

= 3.1.9 (2021-04-16) =
* Fix: Generate: Content: All / Random Method would result in generation index out of bounds after ~1,000 items on some server configurations

= 3.1.8 (2021-04-15) =
* Added: Generate: Content: Schedule Increment is now based on last generated page's date and time.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--publish
* Fix: Generate: Content: Don't display Schedule Increment when Date = Random Date, as the increment is not used
* Fix: Generate: Content: Keyword Dropdowns: Prevent PHP warnings when no Keywords defined 
* Fix: Keywords: Retain newlines on Terms in form field when an error occurs saving a Keyword
* Fix: Settings: General: Change Page Dropdown Fields: Retain CSS classes when changing to an ID Field

= 3.1.7 (2021-04-08) =
* Added: Dynamic Elements: Wikipedia Image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-wikipedia-image/
* Added: Generate: Content: Featured Image: Wikipedia Image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Fix: Dynamic Elements: Wikipedia: Improved performance by reducing number of requests
* Fix: Generate: Content: Attributes: Parent: Only show Parent field applicable to the Post Type chosen, and not all Post Type Parent fields

= 3.1.6 (2021-04-01) =
* Added: Generate: Content: Specific Date: Option to specify time.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--publish
* Fix: Generate: Content: Scheduled: Honor WordPress' timezone when Status is `Scheduled` and Date is `Now`

= 3.1.5 (2021-03-29) =
* Fix: Generate: Content: Live Composer: Process Dynamic Elements / Shortcode

= 3.1.4 (2021-03-25) =
* Added: Generate: Content: Defaults: Set Overwrite = Yes, if existing Page Generated by this Group as the default for new Content Groups
* Fix: Generate: Content: Divi Theme and Divi Builder: Only Register Dynamic Elements as Divi Modules when editing a Content Group, not a Page
* Fix: Generate: Content: Gutenberg: Further improvements to block encoding/decoding to support third party blocks and special characters in third party blocks
* Fix: Generate: Content: Modals: Ensure modals display over Page Builder Modules/Elements 
* Fix: Keywords: Generate Phone Area Codes: Show error if no Output Type specified
* Fix: Import as Content Group: Better support for importing from Pages created with a Page Builder where special characters are used

= 3.1.3 (2021-03-18) =
* Added: Generate: Content: Duplicate: Add link to duplicated Content Group in success notification
* Added: Import as Content Group: Add link to imported Content Group in success notification
* Added: Dynamic Elements: Related Links: Radius can be specified to nearest 0.1 miles
* Fix: Keywords: Autocomplete: Don't strip commas from existing field when selecting a Keyword from the autocomplete dropdown list
* Fix: Import as Content Group: Don't copy unnecessary Post Metadata, such as Keywords, Group ID and Index from Page, Post or Custom Post Type
* Fix: Generate: Content: Reduce database requests for Generated Count and Last Index during generation to improve performance for larger sites
* Fix: Generate: Content: Prevent memory usage increasing by flushing WordPress' Term cache occasionally during generation
* Fix: Generate: Content: Delete Generated Content: PHP Warnings or AJAX errors when no Generated Content exists
* Fix: Generate: Terms: Reduce database requests for Generated Count and Last Index during generation to improve performance for larger sites
* Fix: Generate: Terms: Prevent memory usage increasing by flushing WordPress' Term cache occasionally during generation
* Fix: Generate: Terms: Delete Generated Terms: PHP Warnings or AJAX errors when no Generated Terms exists
* Fix: CLI: Generate: Content: Honor number_of_posts and resume_index arguments.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/#generate-content
* Fix: CLI: Generate: Terms: Honor number_of_terms and resume_index arguments.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/#generate-terms
* Fix: CLI: Delete: Terms: Delete all Terms

= 3.1.2 (2021-03-12) =
* Fix: Generate: Content: Test: URL would wrongly result in 404, even when Test Page/Post was successfully generated

= 3.1.1 (2021-03-11) =
* Added: Generate: Content: Page Builders: Support for GoodLayers Page Builder.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-goodlayers/
* Fix: Generate: Content: Duplicate action missing below each Content Group Title in the table
* Fix: Generate: Content: Test: URL might result in 404 when Test Page/Post is successfully generated, due to using page_id parameter for e.g. a Test Post
* Fix: Generate: Content: Gutenberg: Don't encode special characters in third party blocks

= 3.1.0 (2021-03-05) =
* Fix: Generate: Content: Detect Non-lowercase Keywords and replace them with Terms

= 3.0.9 (2021-03-04) =
* Added: Keywords: Third Party Sources: Display preview of data when editing Keyword.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--viewing-terms
* Added: Generate: Content: Option to specify Group Parent.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--attributes
* Fix: Keywords: Import CSV: Remove UTF8 BOM sequencing from Column Names to prevent Keywords not being detected in Content and Term Groups
* Fix: Dynamic Elements: Related Links: If the Post Parent parameter isn't a Page ID or a slug, convert it to a slug before querying for Related Links to produce more accurate results
* Fix: Generate: Don't detect spintax words as Keywords, for performance
* Fix: Generate: Content: PHP Warning: count(): Parameter must be an array or an object

= 3.0.8 (2021-02-25) =
* Added: Keywords: Support for third party data sources.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#adding---editing-keywords--sources
* Added: Keywords: Generate Phone Area Codes: View Keyword link in success notification when Keyword created
* Added: Generate: Content: Elementor: Dynamic Elements are available as Elementor Widgets.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-elementor/#dynamic-elements 
* Added: Generate: Content: Support for Search Exclude Plugin
* Fix: Generate: Content: Elementor: Attempt to clear Elementor's cache after Content Generation, to prevent output/layout errors and save the user having to go to Elementor > Tools > Regenerate CSS
* Fix: Generate: Content: Developer Hooks: Apply page_generator_pro_generate_content_before and page_generator_pro_generate_content_after hooks consistently across Browser, Server and CLI generation
* Fix: Generate: Content: Developer Hooks: Apply page_generator_pro_generate_terms_before and page_generator_pro_generate_terms_after hooks consistently across Browser, Server and CLI generation
* Fix: Dynamic Elements: Wikipedia: Undefined index: headings PHP warning
* Fix: Logs: Honor Preserve Logs setting, ensuring Logs are cleared periodically

= 3.0.7 (2021-02-12) =
* Added: Generate: Content: Divi Theme and Divi Builder: Register Dynamic Elements as Divi Modules.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-divi/#dynamic-elements 
* Fix: Groups: Cache calls made to get_all_ids_names() for the request lifestyle, to reduce duplicate queries and improve performance
* Fix: Keywords: Cache calls made to get_keywords_and_columns() for the request lifecycle, to reduce duplicate queries and improve performance

= 3.0.6 (2021-02-09) =
* Fix: Generate: Content: Yoast SEO: Prevent Keyword brackets and braces being encoded/stripped from Canonical field, resulting in Keyword not being replaced with a Term

= 3.0.5 (2021-02-01) =
* Added: Generate: Content: Genesis Framework Support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-seo-integration/#genesis-framework
* Fix: Generate: Content: WooCommerce: Check minimum supported WooCommerce version before loading integration, to avoid errors

= 3.0.4 (2021-01-25) =
* Fix: Generate: Content: Comments: Generation would fail with 500 error when Generate Comments enabled and no Keyword specified in the First Name, Surname and Comment fields

= 3.0.3 (2021-01-21) =
* Added: Generate: Terms: Delete Generated Terms will Delete in batches to avoid timeouts
* Added: Generate via Server: Set maximum execution time of 60 seconds for each generated page, to minimise timeout errors
* Fix: Generate via Server, Generate via CLI: sprintf(): Argument number must be greater than zero warning
* Fix: Generate: Content: Delete Generated Content: Reset Last Index Generated to zero
* Fix: Generate: Terms: Delete Generated Terms: Reset Last Index Generated to zero
* Fix: Generate: Terms: Changed terminology from 'Content' to 'Term'
* Fix: Generate: Terms: "Search results for" label wrongly overlapping search box
* Fix: Logs: Display correct page of Log Entries when entering a page number in the pagination field

= 3.0.2 (2021-01-18) =
* Added: Error message if the minimum required PHP version isn't met.  See https://www.wpzinc.com/documentation/installation-licensing-updates/hosting-requirements/ and https://www.php.net/supported-versions.php, noting older PHP versions are end of life, with no security updates and no support.
* Added: Generate: Content: Support for Brizy Page Builder.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-brizy/
* Added: Generate: Content: Dynamic Elements: Google Maps: Option to show or hide Place Name and Marker.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-google-map/#road-map
* Added: Generate: Content: Divi: Classic Editor: Don't display bottom Action Meta Box for Save, as Divi prevents saving changes. Use Save button in top Actions Meta Box instead
* Fix: Generate: Content: Classic Editor: Bottom Actions Meta Box: Ensure Generate, Trash and Delete buttons perform action when clicked
* Fix: Generate: Content: Prevent undefined index errors for FIBU and FIFU integrations
* Fix: Generate: Content: Dynamic Elements: Classic Editor: Wrap form field labels onto multiple lines if required
* Fix: Generate: Content: Dynamic Elements: Center UI modal when using Text tab in Classic Editor
* Fix: Generate: Content: Dynamic Elements: Display Dynamic Element Title in modal when using Text tab in Classic Editor
* Fix: Generate: Terms: Yoast SEO: Output custom metadata when viewing a generated Term on the frontend site
* Fix: Classic Editor: Autocomplete: Keyword suggestions would incorrectly display on Pages, Posts and Custom Post Types
* Fix: Whitelabelling: Don't display Review Request notification if whitelabelling is available after a license is upgraded to an Agency license

= 3.0.1 (2021-01-14) =
* Added: Dynamic Elements: Related Links: Option to specify delimiter for List of Links, Comma Separated.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/#configuration--output--list-of-links--comma-separated
* Fix: Dynamic Elements: Related Links: Honor Limit

= 3.0.0 (2021-01-07) =
* Added: Generate: Content: Dynamic Elements: UI available when using Text tab in Classic Editor.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-shortcodes/#adding--classic-editor---tinymce--text-editor
* Added: Generate: Content: Block Spintax: Support for minimum and maximum number of paragraphs to output.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-spintax/#block-spinning--minimum---maximum-paragraph-limits
* Added: Whitelabelling: Support for whitelabelling Changelog URL.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/whitelabelling-access/#configure-whitelabelling
* Fix: Whitelabelling: Apply to Dashboard > Updates when a Plugin Update is available
* Fix: Whitelabelling: Apply to Plugins > View Details modal when a Plugin Update is available
* Fix: Generate: Terms: Display Slug and Taxonomy in Table and Form when new Term Group added
* Fix: Generate: Terms: Yoast SEO: Copy metadata to Generated Terms
* Fix: Generate: Terms: Don't copy unnecessary Term Meta to Generated Terms

= 2.9.9 (2020-12-24) =
* Added: Dynamic Elements: Creative Commons: Option to output attribution below image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-creative-commons-image/
* Added: Dynamic Elements: Creative Commons: Option to choose Licenses to fetch an image from.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-creative-commons-image/
* Added: Dynamic Elements: Creative Commons: Option to copy / not copy image to Media Library.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-creative-commons-image/
* Added: Dynamic Elements: Pexels: Option to output attribution below image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pexels/
* Added: Dynamic Elements: Pexels: Option to copy / not copy image to Media Library.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pexels/
* Added: Dynamic Elements: Pixabay: Option to output attribution below image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pixabay/
* Added: Dynamic Elements: Pixabay: Option to copy / not copy image to Media Library.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pixabay/
* Fix: Dynamic Elements: Gutenberg: Multi select fields wouldn't select a value when clicked from the list
* Fix: Generate: Content: Cornerstone (Pro / X Theme): Register Dynamic Elements
* Fix: Generate: Content: Cornerstone (Pro / X Theme): Register autocomplete fields on Dynamic Elements as text fields, so they're not missing

= 2.9.8 (2020-12-17) =
* Fix: PHP errors and unable to Generate Content if WP All Export not installed and activated

= 2.9.7 (2020-12-17) =
* Added: Generate: Content: Export Generated Content via WP All Export.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-export-generated-content/
* Added: Generate: Content: Custom Fields: Support for multiple Custom Fields with the same Meta Key
* Added: Generate: Content: Divi Den Pro: Support for multiple Custom Fields with the same Meta Key, ensuring e.g. Animated Buttons display correctly
* Fix: Import & Export: Export: Undefined varaible $settings notice
* Fix: Import & Export: Export: Error would display if no Keywords, Content Groups or Term Groups exist

= 2.9.6 (2020-12-10) =
* Added: Generate: Content: SEOPressor Compatibility.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-seo-integration/
* Added: Generate: Content: Overwrite: Options to enable/disable overwriting for third party Plugins: SEOPressor.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generation--overwrite-sections--third-party-plugins
* Added: Dynamic Elements: Yelp: Option to choose Image Size, Display Order and Display Alignment.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-yelp-business-listings/
* Added: Keywords: Generate Locations: ZIP Codes that belong to multiple Cities will be included multiple times in Keyword Terms to reflect each ZIP Code to City relationship
* Fix: Keywords: Generate Locations: Remove country prefix from Region Codes
* Fix: Keywords: Generate Locations: Include ZIP Codes that do not have a County relation
* Fix: Generate: Content: Oxygen Builder: Replace Keywords with Terms in encoded elements, such as images
* Fix: Dynamic Elements: Yelp: Default Image Alt Tag to %business_name% instead of %business_name, so the Business Name is set correctly
* Fix: WP-CLI: Plugin activation when using `wp plugin activate page-generator-pro`

= 2.9.5 (2020-12-03) =
* Added: Generate: Content: Overwrite: Options to enable/disable overwriting for third party Plugins: All in One SEO Pro.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generation--overwrite-sections--third-party-plugins
* Added: Generate: Content: Validate Geolocation Latitude and Longitude, returning an error in Test mode if values are not valid.
* Fix: Generate: Content: Block Spinning: Gutenberg: Additional <p> tags would be added, breaking blocks on generated Pages.
* Fix: Dynamic Elements: Related Links: Moved order parameters from Output tab to Ordering tab, to ensure full modal window displays in Classic Editor and Page Builders.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/#configuration--ordering 
* Fix: Spintax: Javascript: Don't process logical OR operators as spintax (e.g. ||), to ensure Javascript is fully retained on generated Pages

= 2.9.4 (2020-11-26) =
* Added: Licensing: Whitelabel success messages for Agency Licenses with Whitelabelling enabled.
* Added: Dynamic Elements: Related Links: Option to choose Display Alignment.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/
* Fix: Generate: Content: Remove blank row after Trash or Delete Generated Content buttons clicked
* Fix: Generate: Content: Reset Generated Items count in table when Delete Generated Content clicked
* Fix: Generate: Content: WooCommerce: Uncaught Error: Call to undefined method WP_Error::get_current_screen()

= 2.9.3 (2020-11-19) =
* Added: Keywords: Generate Locations: Output Types for Counties in Local Language.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/#output-type--local-langauge
* Added: Generate: Content: Featured Image: Featured Image from URL Plugin support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Added: Generate: Content: Featured Image: Featured Image by URL Plugin support.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Added: Dynamic Elements: Related Links: Option to choose Featured Image size, Display Order and List Style.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/
* Added: Localization support, with .pot file and translators comments

= 2.9.2 (2020-11-12) =
* Added: Settings: Generate: Option to enable which Plugins to load when using Performance Addon.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-generate/#performance-addon--load-plugins
* Added: Keywords: Generate Locations: Output Types for Region in Local Language.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/#output-type--local-langauge
* Added: Generate: Content: Overwrite: Options to enable/disable overwriting for third party Plugins: ACF, Yoast SEO Premium.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generation--overwrite-sections--third-party-plugins

= 2.9.1 (2020-11-06) =
* Fix: BeTheme: No such file or directory error

= 2.9.0 (2020-11-05) =
* Added: Keywords: Export to CSV.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#export-a-keyword-to-csv
* Added: Generate: Content: Overwrite: Options to enable/disable overwriting for Attributes, Taxonomies and Menus.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generation--overwrite-sections
* Added: Generate: Content: Overwrite: Options to enable/disable overwriting for third party Plugins: All in One SEO Pack, Rank Math SEO, WooCommerce and Yoast SEO.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generation--overwrite-sections--third-party-plugins
* Added: Generate: Terms: Support for third party Plugins that register metaboxes e.g. Rank Math SEO, Yoast SEO
* Fix: Activation: SSL certificate error when importing Phone Area Codes data
* Fix: Keywords: Generate Phone Area Codes: If data did not import on Plugin Activation, attempt it on this screen and show verbose errors
* Fix: Generate: Content: Dynamic Elements: Google Maps, Open Street Map and YouTube: "GeoRocket: No License Key was specified in the request." error
* Fix: Import & Export: Tabs would not work when whitelabelling enabled on Agency Licenses
* Fix: Spintax: Local: If Skip Capitalized Words = No, replace first word of each sentence with synonyms when first word's first letter is capitalized.

= 2.8.9 (2020-10-29) =
* Added: Generate: Content: Research Content.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content-research/
* Added: Menus and Submenus: Filter to define minimum required capability for accessing Plugin Menus and Submenus.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/developers/#page_generator_pro_admin_admin_menu_minimum_capability
* Fix: Keywords: Automatically fetch list of similar terms if no Terms supplied 

= 2.8.8 (2020-10-22) =
* Added: Generate: Content: Comments: Generate Comments.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--discussion--generate-comments
* Added: Generate: Content: Dynamic Elements: Remote Image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-remote-image/
* Fix: Generate: Content: Keyword might not be detected if contained within spintax e.g. {Location|{keyword}}, when {keyword} not specified elsewhere in the Content Group

= 2.8.7 (2020-10-15) =
* Added: Generate: Content: Dynamic Elements: Creative Commons: Size parameter moved to Output tab, with standardized choices matching WordPress' registered image sizes.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-creative-commons-image/
* Added: Generate: Content: Dynamic Elements: Pexels: Size parameter moved to Output tab, with standardized choices matching WordPress' registered image sizes.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pexels/
* Added: Generate: Content: Dynamic Elements: Pixabay: Size parameter moved to Output tab, with standardized choices matching WordPress' registered image sizes.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pixabay/
* Fix: bbPress: Settings: Forums: Forum Root: Fatal error
* Fix: Generate: Content: Featured Image and Dynamic Elements: Pexels: Strip any URL parameters on image filename to prevent errors
* Fix: Generate: Content: Featured Image and Dynamic Elements: Pexels: Limit resultset to ~ 8,000 images for a query to avoid errors from the API, even where it states more images are available

= 2.8.6 (2020-10-01) =
* Added: Generate: Content: ListingPro Support for Listings, Reviews and Events.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-listingpro-integration/
* Added: Generate: Content: Gutenberg: Dynamic Elements: Use native autocomplete dropdown component for better performance
* Added: Generate: Content: Conditionally show/hide Meta Boxes based on the Publish > Post Type e.g. don't show WooCommerce if we're not generating Products
* Added: Generate via Browser: Clear log after 100 entries to improve browser and generation performance
* Fix: Generate via Browser: Display Start and End Index in counter correctly when Resume Index and/or No. Posts specified
* Fix: Generate: Content: Autocomplete: Title: Keyword suggestions hidden behind Classic Editor
* Fix: Generate: Content: Featured Image and Dynamic Elements: Creative Commons: Limit maximum number of image results to 1,000 to avoid API errors
* Fix: Generate: Content: Featured Image and Dynamic Elements: Pixabay: [ERROR 400] "per_page" is out of valid range.
* Fix: Generate: Content: Dynamic Elements: Don't append .jpg.jpeg to imported JPEG images, if either the .jpg or .jpeg extension already exist in the filename
* Fix: Generate: Content: Featured Image: Don't append .jpg.jpeg to imported JPEG images when using Image Source = URL and the .jpg or .jpeg extension already exist in the filename

= 2.8.5 (2020-09-24) =
* Added: Generate: Terms: Keyword Autocomplete on Description Field
* Added: Generate: Terms: Description: Generate Spintax from Selected Text
* Fix: Classic Editor: Autocomplete: Keyword suggestions would incorrectly display on Pages, Posts and Custom Post Types
* Fix: Generate: Content: Classic Editor: Autocomplete: Ensure autocomplete suggestions box height does not exceed 120px and is scrollable
* Fix: Generate: Content: Featured Image: Don't delete existing image when overwriting previously generated content and Featured Image > Media Library Image > Output > Copy = No
* Fix: Generate: Content: Avia / Enfold: Remove duplicate Page Builder content prior to generation for better performance and no duplication of shortcodes, keywords and spintax processing
* Fix: Generate: Content: Beaver Builder: Remove duplicate Page Builder content prior to generation for better performance and no duplication of shortcodes, keywords and spintax processing
* Fix: Generate: Content: Thrive Architect: Remove duplicate Page Builder content prior to generation for better performance and no duplication of shortcodes, keywords and spintax processing
* Fix: Dynamic Elements: Creative Commons, Media Library, Pexels, Pixabay: Attach images imported into the Media Library to the Generated Page

= 2.8.4 (2020-09-17) =
* Added: Generate: Content: Featured Image: Support for Creative Commons
* Added: Generate: Content: Featured Image: Creative Commons, Pexels, Pixabay: Fetch more images to improve random image selection
* Added: Dynamic Elements: Creative Commons, Pexels, Pixabay: Fetch more images to improve random image selection
* Fix: Dynamic Elements: Creative Commons: Image would fail if it had no title or the Orientation parameter was specified
* Fix: Generate: Content: Strip HTML tags from Keyword Term Log Output, to avoid browser memory errors
* Fix: Generate: Content: Elementor: Don't double encode Elementor Data on generated content

= 2.8.3 (2020-09-10) =
* Added: Dynamic Elements: Yelp: Option to output as HTML table.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-yelp-business-listings/#configuration--output
* Added: Dynamic Elements: Yelp: Number of Columns for List Output Type.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-yelp-business-listings/#configuration--output
* Fix: Dynamic Elements: Wikipedia: Don't attempt to parse Wikipedia content when errors, no content and no similar pages were returned from Wikipedia
* Fix: Dynamic Elements: Wikipedia: Don't attempt to process spintax on Wikipedia content if no spintax could be generated
* Fix: Dynamic Elements: Yelp: Output CSS for star ratings

= 2.8.2 (2020-09-03) =
* Fix: Logs: Screen Options: Apply "Choose table columns to display" to Log entry data as well as Log Table Columns

= 2.8.1 (2020-08-28) =
* Added: Keywords: Table: Display Delimiter and Columns
* Added: Keywords: Screen Options: Choose table columns to display.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#define-table-columns-to-display
* Fix: Keywords: Uncaught Error: Class 'League\Csv\Reader' not found
* Fix: Spintax: SpinnerChief: Timeout when attempting to spin HTML content 
* Fix: Logs: Lighter success/error row background colors to make text easier to read

= 2.8.0 (2020-08-27) =
* Added: Settings: Generate: Conditionally display settings based on other settings
* Added: Generate Content: Visual Editor: Generate Spintax from Selected Text: Show progress and improved confirmation/error message
* Added: Logs: Screen Options: Choose table columns to display.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/logs/#define-table-columns-to-display
* Added: Logs: Screen Options: Choose number of logs per page to display.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/logs/#define-number-of-logs-per-page
* Added: Logs: Filter by Result (success or failure).  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/logs/#filtering-logs
* Added: Logs: Display confirmation dialog when clicking Clear Log button
* Fix: Logs: When searching by Group Name, don't require an exact match 
* Fix: Logs: Preserve Filter selections (Filter by Group, System, Date) after clicking Filter
* Fix: Logs: When filtering by date, include results matching the date, not just results between the dates
* Fix: Logs: Ordering by Generated Column would not return any results
* Fix: Logs: Set Clear Log button to red
* Fix: Settings: Use <label> for field names for accessibility
* Fix: Keywords: Import CSV: Improved validation testing when importing comma deliniated data that also contains commas in the data itself
* Fix: Keywords: Import CSV: Encapsulate data in quotation marks if the delimiter is included in CSV row data cell(s)
* Fix: Keywords: Import CSV: Retain backslashes if included in CSV row data cell(s)
* Fix: Keywords: Import CSV: Convert newlines to HTML break lines if newlines are included in CSV row data cell(s), to ensure validity of imported data
* Fix: Keywords: Import CSV: Import Columns / Rows as multiple Keywords: Sanitize row/column names to be compatible with Keyword Name

= 2.7.9 (2020-08-20) =
* Added: Generate: Content: Avada Live: Buttons to add Dynamic Elements / Shortcodes into Text Block. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-avada-fusion-builder/#dynamic-elements
* Added: Generate: Content: Resume Index: Use Last Generated Index option, which will set the Resume Index = Last Generated Index.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generation--resume-index
* Added: Generate: Terms: Resume Index: Use Last Generated Index option, which will set the Resume Index = Last Generated Index.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-terms/#fields--generation--resume-index
* Added: Settings: Generate: Stop on Error: Option to stop, attempt to regenerate the same item or generate the next item on a generation, server or connection error.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-generate/#stop-on-error 
* Fix: Licensing: Support Meta Box styling incorrect in WordPress 5.5+
* Fix: Generate: Content: Overwrite: Don't delete Media Library attachments on existing generated Pages when re-generating and not overwriting the Content
* Fix: Generate: Content: Overwrite: Don't delete Featured Images on existing generated Pages when re-generating and not overwriting the Featured Image
* Fix: Generate: Content: Remove View Generated Content link after clicking Trash Generated Content or Delete Generated Content in the table
* Fix: Generate: Content: Reset Generated Items count after clicking Trash Generated Content or Delete Generated Content in the table
* Fix: Generate: Content: Allow Trash Generated Content and Delete Generated Content notifications to be dismissed when clicking the cross icon
* Fix: Generate: Content: Apply Synonms: Fallback to non-spun content if spinning fails, instead of returning db_insert_error
* Fix: Generate: Content: Generate Spintax: WordAI: Send correct request to WordAI to prevent 403 Forbidden error
* Fix: WP-CLI: Generate Content: Removed unused code for measuring performance
* Fix: Logs: Set Generated At Date and Time to honor WordPress timezone 
* Fix: Don't load Performance class and throw a fatal error if Plugin isn't licensed or has exceeded permitted number of sites
* Fix: Don't load Shortcode class and throw a fatal error if Plugin isn't licensed or has exceeded permitted number of sites

= 2.7.8 (2020-08-13) =
* Added: Settings: General: Change Page Parent Dropdown to either ID Field or Search Dropdown: Supports WooCommerce Terms and Condition / Privacy Policy Page Dropdowns in Customizer
* Fix: Keywords: Generate Phone Area Codes: Removed unused Javascript
* Fix: Keywords: Typo on table when no Keywords exist
* Fix: Generate: Content: Gutenberg: Don't remove Permalink field on non-Content Group Post Types
* Fix: Generate: Content: Update Last Index Generated when not in Test Mode and Whitelabelling is available 
* Fix: Generate: Terms: Update Last Index Generated when not in Test Mode and Whitelabelling is available 
* Fix: Generate: Terms: CSS styles for compatibility with WordPress 5.5

= 2.7.7 (2020-08-10) =
* Added: Dynamic Elements: Wikipedia: Option to specify source URL in output.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-wikipedia-content/#configuration--output
* Added: Keyword Transformations: Convert to Permalink style slug with underscores.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#transforming-keywords
* Added: Generate: Content: Import any Page, Post or Custom Post Type as a new Content Group (e.g. if your page layout is already built in a Page).  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#import-a-page--post-or-custom-post-type-into-a-new-content-group
* Fix: Generate: Content: Undefined variable group_id when adding/editing in Gutenberg
* Fix: Settings: General: Change Page Dropdown Fields = Search Dropdown Field would fail on Customize and Settings > Reading screens
* Fix: Autocomplete: Keyword suggestions would incorrectly display on Pages, Posts and Custom Post Types
* Fix: Autocomplete: Ensure Keyword suggestions visible above modal windows when using in Related Links on a non-Content Group 
* Fix: Generate: Content: Generate Spintax: Don't display TinyMCE / Classic Editor button outside of Content Groups

= 2.7.6 (2020-08-06) =
* Added: Keywords: Import CSV: Options to import CSV data into a single Keyword with Column Names.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-import-csv/
* Added: Keywords: Import CSV: Improved options to import different CSV data formats.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-import-csv/
* Added: Keywords: Generate Locations: Added remove button to each value specified in Output Type, Restrict by Regions / Counties / Cities and Exclusions
* Added: Generate: Content: Store Keywords will be enabled by default on new Content Groups.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--custom-fields
* Added: Generate: Content: Trash and Delete Generated Content will Trash / Delete in batches to avoid timeouts
* Added: Export: Option to export specific Keywords, Content Groups, Term Groups and Settings
* Fix: Import: Update Group ID references in Dynamic Elements e.g. Related Links to reflect each imported Group's new Group ID
* Fix: WPBakery Page Builder: Only add required capabilities/permissions to WordPress User Roles that exist, to avoid errors on activation

= 2.7.5 (2020-07-23) =
* Added: Generate: Content: Menu: Assign Generated Page Menu Item to Menu Parent.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--menu
* Fix: Generate: Content: Spin Content: Show error in Test mode if third party spintax generation service fails
* Fix: Generate: Content: Avia / Enfold: Encode single quotation marks in Keyword Terms to prevent blank Content Elements where Keyword(s) are specified
* Fix: Generate: Content: Set Author as current logged in WordPress User if none is specified

= 2.7.4 (2020-07-16) =
* Fix: Generate: Content: Ensure Author or Random Author specified prior to Generation.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--author

= 2.7.3 (2020-07-09) =
* Added: Keywords: Add/Edit: Don't wrap a single Term onto multiple lines
* Fix: Keywords: Generate Locations: Don't allow non-valid Output Types
* Fix: Keywords: Generate Phone Area Codes: Don't allow non-valid Output Types
* Fix: Dynamic Elements: Related Links: Don't allow non-valid Groups
* Fix: Dynamic Elements: Wikipedia: Don't allow non-valid Elements
* Fix: Dynamic Elements: Wikipedia: 500 error would occur when a child node could not be removed

= 2.7.2 (2020-07-02) =
* Added: Keyword Transformations: Output Different Random Term.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#output-different-random-term
* Added: Keyword Transformations: First Word and Last Word Transformations.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#transforming-keywords
* Fix: Generate: Content: Duplicate: Settings and some third party Plugin Settings wouldn't copy to duplicated Content Group
* Fix: Generate: Terms: Duplicate: Settings wouldn't copy to duplicated Content Group
* Fix: Dynamic Elements: Oxygen Builder: Render shortcodes on Generation
* Fix: Updated Contextual Link to reflect new Documentation structure
* Fix: Whitelabelling: Don't display Review Request notification if whitelabelling is available

= 2.7.1 (2020-06-25) =
* Added: Dynamic Elements: Wikipedia: Option to specify elements to return (paragraphs, lists, headings, tables).  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-wikipedia-content/
* Added: Dynamic Elements: Wikipedia: Option to retain or remove links in imported Wikipedia content.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-wikipedia-content/
* Added: Generate: Content: Menu: Add Generated Page to a WordPress Menu.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--menu 
* Fix: Dynamic Elements: Wikipedia: Retain article formatting (bold, italic etc)
* Fix: Dynamic Elements: Wikipedia: Undefined offset error when specifying the last section of a Wikipedia Article
* Fix: Keywords: Add/Edit: Validation: Columns: Ensure comma is used to separate Column Names
* Fix: Keywords: Add/Edit: Validation: Improved error messages when validating field values
* Fix: Keywords: Add/Edit: Use <label> for field names for accessibility
* Fix: Keywords: Edit: Form field values wouldn't display immediately after correcting a validation error and successfully saving
* Fix: ACF: Uncaught ArgumentCountError: Too few arguments to function Page_Generator_Pro_ACF::match_term_group_location_rule() when activating Themes or Plugins that bundle older versions of ACF

= 2.7.0 (2020-06-18) =
* Added: Import: Support for Zipped JSON file
* Added: Export: Export as JSON, Zipped

= 2.6.9 (2020-06-11) =
* Added: Generate Content: WooCommerce Products: Display Product Data and Gallery Meta boxes, providing native support for generating Products.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-woocommerce-products/
* Added: Generate: Content: Dynamic Elements: Creative Commons Images.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-creative-commons-image/
* Fix: Dynamic Elements: Gutenberg: Dynamic Elements can be used inside other blocks, such as columns
* Fix: Dynamic Elements: Yelp: Honor Language / locale instead of always using en, which would then fail

= 2.6.8 (2020-06-04) =
* Added: Dynamic Elements: Yelp: Include precise Yelp error response in Test mode when fetching listings fails
* Fix: Dynamic Elements: Yelp: Default locale to en_US instead of get_locale() to avoid HTTP 400 request errors 
* Fix: Import & Export: Improved importing and exporting to catch edge cases where imports and exports might fail

= 2.6.7 (2020-05-28) =
* Added: Keywords: Generate Locations: Cities: Added Population and Median Household Income data for Canada
* Added: Dynamic Elements: Related Links: Option to specify Link Anchor Title when Output Type = List of Links or List of Links, Comma Separated.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/
* Fix: Dynamic Elements: Media Library: Don't link image to '_self' when no Link specified
* Fix: Keywords: Generate Locations: Some UK Cities had incorrect population numbers 

= 2.6.6 (2020-05-23) =
* Fix: Activation: Could not load class Page_Generator_Pro_Common
* Fix: Generate: Content: SiteOrigins Page Builder: Display Buttons to add Dynamic Elements / Shortcodes
* Fix: Generate: Terms: Removed debugging output on Term Meta
* Removed: Keywords: Generate Locations: Cities: Removed Population Ethnicity Data

= 2.6.5 (2020-05-21) =
* Added: Keywords: Generate Locations: Region Code returns ISO3166 two-letter Region Code
* Added: Generate: Content: Publish draft Content Group immediately before Test, Generate or Generate via Browser to ensure generation works in Gutenberg
* Added: Generate: Content: Prevent Preview of Content Group. Use Test functionality to test output
* Added: WP-CLI: List Term Groups Command.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/#list-term-groups
* Added: Keywords: Screen Options to define Keywords per Page.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#define-number-of-keywords-per-page
* Added: Dynamic Elements: Related Links: List of Links, Comma Separated Output Type.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/
* Fix: WP-CLI: List Content Groups Command would only list first Group.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/#list-content-groups
* Fix: Keywords: Retain Search, Order and Order By parameters when using Pagination
* Fix: Performance Addon: Load Cornerstone, Polylang and WPML
* Fix: Generate: Content: Generate Spintax: TinyMCE / Classic Editor button was missing
* Fix: Generate: Content: Generate Spintax: ChimpRewriter: Strip slashes on quotation marks

= 2.6.4 (2020-05-07) =
* Fix: Generate via Server: Reset Searches and Replacements to prevent same Keyword Term being used for each Generated Page
* Fix: Generate via CLI: Reset Searches and Replacements to prevent same Keyword Term being used for each Generated Page

= 2.6.3 (2020-05-07) =
* Added: Checks to ensure server configuration for correct working functionality, showing an error notice where failing
* Added: Generate: Content: Register Metaboxes on Content Groups where Metaboxes reigstered by Themes using Metabox.io
* Added: Dynamic Elements: Apply sensible default values to new Dynamic Elements
* Added: ACF: Specify Field Group to display on specific Content Groups.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-custom-field-plugins/#advanced-custom-fields--content-groups
* Added: ACF: Specify Field Group to display on specific Term Groups.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-custom-field-plugins/#advanced-custom-fields--term-groups
* Fix: Keywords: Generate Keyword Term Ideas.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#generate-keyword-term-ideas
* Fix: Generate: Content: Wizelaw Theme: Preserve Metaboxes on non-Content Groups
* Fix: Generate: Terms: Copy Term Meta (ACF, Yoast) to Generated Terms
* Fix: Dynamic Elements: Media Library: Regression where Operator option was removed
* Fix: Dynamic Elements: Yelp: Display Sort By option on TinyMCE instances
* Fix: Generate: Multilingual Content: WPML: Prevent 404 on Generated Content when WPML not enabled on Content Groups
* Fix: Generate: Content: Keyword Transformations: Detect mb_* functions for transforming accented and special characters, falling back to less reliable methods if mb_* functions unavailable

= 2.6.2 (2020-04-30) =
* Added: Generate: Content: Construction Theme: General, Header, Sidebar and Footer Options available in Content Groups
* Added: Generate: Content: Medicenter Theme: Post and Sidebar Options available in Content Groups 
* Added: Dynamic Elements: Related Links: Classic Editor / TinyMCE Shortcode available in all Post Types (Posts, Pages etc)
* Added: Dynamic Elements: Related Links: Gutenberg Block available in all Post Types (Posts, Pages etc)
* Added: Dynamic Elements: Yelp Business Listings: Option to specify Image Alt Tag.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-yelp-business-listings/
* Fix: Dynamic Elements: Gutenberg: Numeric Fields would should blank instead of saved value
* Fix: Generate: Support Numeric Keywords with Columns
* Fix: Generate: Support Keywords with Columns regardless of Column Name being upper/lower/mixed case
* Fix: Generate: Content: Store Keywords functionality not working
* Fix: Generate: Terms: Keywords not being replaced by Terms
* Fix: Generate: Don't attempt to replace Keywords that don't exist
* Fix: Export: PHP Warning: count(): Parameter must be an array or an object that implements Countable when no Term Groups specified
* Fix: CSS: Renamed option class to wpzinc-option to avoid CSS conflicts with third party Plugins

= 2.6.1 (2020-04-23) =
* Added: Generate: Logs.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/logs/
* Added: Generate: Content: Generate via Server: Option to enable logging.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-run/#generate-via-server
* Fix: Generate: Improved Performance by ~80% when using ~10,000+ Keyword Terms and/or Keyword Transformations, Columns and nth Terms.
* Fix: Generate: Content: Generate via Server: Generation would fail when Number of Posts and Resume Index were zero
* Fix: Generate: Content: Elementor: Removed unused tooltip classes to prevent Menu and Element Icons from not displaying
* Fix: Generate: Content: Visual Composer: Show Generated Page's Content when manually editing an existing Generated Page
* Fix: Generate: Content: Cornerstone (Pro / X Theme): Only attempt to convert Elements when Cornerstone is active
* Fix: Generate: Content: Cornerstone (Pro / X Theme): Honor Whitelabelling Setting on Agency Licenses for Dynamic Element Names

= 2.6.0 (2020-04-16) =
* Added: Licensing: Verbose error message when unable to connect to Licensing API
* Added: Keywords: Generate Locations: Verbose error message when unable to connect to Georocket API
* Added: Generate: Content: Flatsome Theme: Dynamic Elements / Shortcodes available in Text Element. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-flatsome/#dynamic-elements
* Added: Generate: Content: Pro Theme: Dynamic Elements / Shortcodes available as Elements. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-pro-theme/#dynamic-elements
* Added: Generate: Content: X Theme: Dynamic Elements / Shortcodes available as Elements. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-x-theme/#dynamic-elements
* Fix: Keywords: Generate Locations: Uncaught ReferenceError: page_generator_pro_show_error_message_and_exit is not defined Javascript error
* Fix: Related Links: Default Output Type = List of Links when no Output Type specified
* Fix: Licensing: Don't repetitively check the validity of a license that's invalid or exceeds the number of sites permitted, unless we're on the Licensing screen
* Fix: Dashboard > Updates: Show link to Changelog on View version details link

= 2.5.9 (2020-04-09) =
* Added: Generate: Content: Porto2 Theme: Layout and Sidebar settings compatibility. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-porto2-theme/
* Fix: Menu: Don't display Generate sub menu at WordPress Admin > Page Generator Pro
* Fix: Generate: Content: Gutenberg: Use WordPress native serialize_blocks() function to prevent columns and Classic Blocks being stripped from Generated Pages 
* Fix: Dynamic Elements: YouTube: Gutenberg: Parse oEmbed URL to output video instead of YouTube URL

= 2.5.8 (2020-04-02) =
* Added: Keywords: Generate Locations: Only fetch Output Types when sending API request for performance
* Added: Generate: Content: SiteOrigins Page Builder: Buttons to add Dynamic Elements / Shortcodes into Backend Editor Module. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-siteorigin-page-builder/#dynamic-elements
* Added: Generate: Content: Thrive Architect: Buttons to add Dynamic Elements / Shortcodes into WordPress Content Element. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-thrive-architect/#dynamic-elements
* Added: Generate: Content: Visual Composer: Buttons to add Dynamic Elements / Shortcodes into Frontend Text Block. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-visual-composer/#dynamic-elements
* Added: Generate: Content: WPBakery Page Builder: Buttons to add Dynamic Elements / Shortcodes into Frontend Text Block. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-wpbakery-page-builder/#dynamic-elements
* Added: Generate: Content: Dynamic Elements: Media Library: Option to link image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-media-library-image/#configuration--link
* Added: Generate: Content: Dynamic Elements: Pexels: Option to link image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pexels/#configuration--link
* Added: Generate: Content: Dynamic Elements: Pixabay: Option to link image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pixabay/#configuration--link
* Fix: Keywords: Generate Locations: Number of columns does not match deliniated Terms error would occur when using a City, County or Region Wikipedia URL containing a comma

= 2.5.7 (2020-03-26) =
* Added: Generate: Content: Beaver Builder: Buttons to add Dynamic Elements / Shortcodes into Text Editor Module. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-beaver-builder/#dynamic-elements
* Added: Generate: Content: BeTheme / Muffin Page Builder: Buttons to add Dynamic Elements / Shortcodes into Visual Editor. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-betheme-muffin-page-builder-integration/
* Added: Generate: Content: Bold Builder: Buttons to add Dynamic Elements / Shortcodes into Text Element. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-bold-builder/
* Added: Generate: Content: Divi: Buttons to add Dynamic Elements / Shortcodes into Text Module. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-divi/#dynamic-elements
* Added: Generate: Content: Elementor: Buttons to add Dynamic Elements / Shortcodes into Text Editor. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-elementor/#dynamic-elements
* Added: Generate: Content: Enfold / Avia Layout Builder: Buttons to add Dynamic Elements / Shortcodes into Text Block. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-enfold-avia-layout-builder/#dynamic-elements
* Added: Generate: Content: Live Composer: Buttons to add Dynamic Elements / Shortcodes into Text Element. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-live-composer/#dynamic-elements
* Added: Generate: Content: Oxygen Builder: Buttons to add Dynamic Elements / Shortcodes into Rich Text Module. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-oxygen-builder/#dynamic-elements
* Added: Generate: Content: WPBakery Page Builder: Buttons to add Dynamic Elements / Shortcodes into Backend Text Block. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration-wpbakery-page-builder/#dynamic-elements
* Added: Generate: Content: Generate via Server: Show error if DISABLE_WP_CRON is enabled in wp-config.php
* Added: Generate: Terms: Generate via Server: Show error if DISABLE_WP_CRON is enabled in wp-config.php
* Fix: Activation: Prevent DB character set / collation errors on table creation by using WordPress' native get_charset_collate()

= 2.5.6 (2020-03-23) =
* Added: Spintax: Improved performance of spintax for larger spins
* Fix: BeTheme / Muffin Page Builder: No such file or directory error

= 2.5.5 (2020-03-21) =
* Fix: Shortcode: OpenWeatherMap: array_merge() error

= 2.5.4 (2020-03-19) =
* Added: Generate: Content: Shortcodes are now available as Gutenberg Blocks.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-shortcodes/
* Fix: Generate: Content: Keyword Dropdown: Ensure width does not exceed meta box
* Fix: Generate: Content: Keyword Dropdown: Ensure height does not exceed 120px and is scrollable
* Fix: Generate: Multilingual Content: WPML would wrongly be detected as active when using Polylang
* Fix: Generate: Content: Divi: Honor Content Group's Featured Image setting when using Divi

= 2.5.3 (2020-03-13) =
* Fix: Shortcodes: Prevent errors when using frontend Page Builders

= 2.5.2 (2020-03-12) =
* Added: Performance: Only load required Plugin classes depending on the request type
* Added: Generate: Multlingual Content: WPML Integration.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-multilingual-content-wpml/
* Added: Shortcode: Related Links: List of Links: Link Description and Featured Image options.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/#output

= 2.5.1 (2020-03-05) =
* Added: Generate: Multlingual Content: Polylang Integration.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-multilingual-content-polylang/
* Added: Settings: General: Country Code: The default country to select for any Country Code dropdowns within the Plugin.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/general-settings/#country-code
* Fix: Shortcodes: TinyMCE Modal input sizing for smaller screen resolution compatibility
* Fix: Shortcode: Related Links: Remove Distance Tags if no distance is available

= 2.5.0 (2020-02-27) =
* Added: Generate: Content: Featured Image: Option to specify EXIF Latitude, Longitude, Description and Comment in image file..  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Fix: Generate: Content: Featured Image: Tabbed UI to match Media Library, Pexels and Pixabay shortcodes.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Fix: EXIF: Write EXIF metadata if specified in a Shortcode or Featured Image, where the image supports EXIF but does not have existing EXIF metadata

= 2.4.9 (2020-02-20) =
* Added: Settings: OpenWeatherMaps: Option to use own API key.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-integrations/#openweathermap
* Fix: Generate: Content: Generate via Server: Permit unfiltered HTML so e.g. iframes are not stripped by WordPress on generation

= 2.4.8 (2020-02-17) =
* Added: Shortcode: OpenWeatherMaps.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-openweathermap/
* Added: Generate: Terms: Overwrite: Options to skip or overwrite if a Term exists, whether created by a Page Generator Pro Group or manually in WordPress.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-terms/#fields--generation
* Fix: Keywords: Generate Locations: Locations would not generate
* Fix: Generate: Terms: UI Output for Sidebar

= 2.4.7 (2020-02-14) =
* Fix: Generate: Content: Shortcodes would not insert into content when pressing Insert button

= 2.4.6 (2020-02-13) =
* Added: Generate: Content: Improved modal UI
* Added: Deactivation: Remove the Must-Use Performance Addon Plugin automatically, if not a Multisite environment

= 2.4.5 (2020-02-06) =
* Added: Keywords: Generate Locations: Cities: Added Population Male/Female, Children/Adults/Elderly, Ethnicity and Median Household Income Output Types for the USA.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Keywords: Generate Locations: Restrict by Min / Max Median Household Income Option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Shortcode: Related Links: Option to display distance in km or miles.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/
* Added: Install/Update: Copy Must-Use Plugin: Developer Actions.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/developers/
* Fix: Keywords: Could not Generate Locations / Save Keyword when defining several columns that would exceed 200 characters in total length 

= 2.4.4 (2020-01-30) =
* Added: Generate: Content: KuteThemes compatibility (Stuno, Ovic Addons Toolkit Plugin)

= 2.4.3 (2020-01-23) =
* Added: Generate: Content: {keyword:random} transformation to output random Keyword Term.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#output-random-term
* Added: Whitelabelling and Access Control: Agency Licenses can control settings via https://www.wpzinc.com/account.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/whitelabelling-access/

= 2.4.2 (2020-01-09) =
* Fix: Shortcode: Wikipedia: Undefined variable $headings_keys, which would prevent some shortcodes from fetching Wikipedia content
* Fix: Shortcode: Wikipedia: Set User-Agent to ensure full HTML is fetched from Wikipedia prior to parsing, to minimise "no paragraphs could be found" errors

= 2.4.1 (2020-01-02) =
* Added: Generate: Content: Developer Actions.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/developers/
* Added: Generate: Terms: Developer Actions.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/developers/
* Added: Shortcode: Media Library: Assign Image to Generated Post when Create as Copy enabled
* Added: Shortcode: Pexels: Assign Image to Generated Post, not just the Group
* Added: Shortcode: Pixabay: Assign Image to Generated Post, not just the Group
* Added: Generate: Content: Overwrite: Delete existing Media Library attachments belonging to the existing Post and Group
* Fix: Generate: Content: Delete Generate Content: Only delete Media Library attachments belonging to the Deleted Post and Group
* Fix: Generate: Content: Block Spinning: Ensure #p# and #s# blocks outside of a #section# are spun when using #section# elsewhere
* Fix: Generate: Content: Generate Spintax: WordAI: Ensure response is not URL encoded

= 2.4.0 (2019-12-26) =
* Added: Shortcode: Media Library: Output: Create as Copy and Image Attribute options.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-media-library-image/#output
* Fix: EXIF: Uncaught Error: Call to a member function getIfd() on null

= 2.3.9 (2019-12-19) =
* Added: Keywords: Generate Locations: Cities: Added Wikipedia URL and Wikipedia Sumamry Output Types.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Generate: Content: Renamed Generate via CRON to Generate via Server.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-run/
* Added: Generate: Content: Option to Generate via Server when editing a Content Group.
* Added: Shortcode: Wikipedia: Exact Wikipedia URL can be specified as a Term.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-wikipedia-content/
* Added: Forms: Accessibility: Replaced Titles with <label> elements that focus the given input element on click
* Added: Generate: Content: Featured Image: EXIF Latitude, Longitude, Description and Comment (Caption) automatically written to image if specified.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Added: Shortcode: Media Library: Option to specify EXIF Latitude, Longitude, Description and Comment in image file.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-media-library-image/#exif
* Added: Shortcode: Pexels: Option to specify EXIF Latitude, Longitude, Description and Comment in image file.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pexels/#exif
* Added: Shortcode: Pixabay: Option to specify EXIF Latitude, Longitude, Description and Comment in image file.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pixabay/#exif
* Fix: Shortcode: Wikipedia: Return all text if no Table of Contents exist on Wikipedia Page, to ensure smaller Wikipedia Pages return content
* Fix: Shortcode: Pexels/Pixabay: Caption and Description were stored the wrong way around
* Fix: Generate: Content: Featured Image: Image URL/Pexels/Pixabay: Caption and Description were stored the wrong way around
* Fix: Generate: Content: Keywords: Check Term exists when using Keyword Transformation with Column Name
* Fix: Generate: Content: Keyword Transformations: Check Term exists when using Keyword Transformation with Column Name
* Fix: Generate: Content: Keyword Transformations: Support accented and special characters

= 2.3.8 (2019-12-12) =
* Fix: New Installations / Plugin Activation: Could not load class geo

= 2.3.7 (2019-12-12) =
* Added: Keywords: Generate Locations: ZIP Codes: Added Latitude and Longitude Output Types.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Keywords: Generate Locations: Cities: Added Latitude and Longitude Output Types.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Keywords: Generate Locations: Counties: Added County Code, Wikipedia URL and Wikipedia Summary Output Types.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Keywords: Generate Locations: Regions: Added Region Code, Wikipedia URL and Wikipedia Summary Output Types.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Generate: Content: Geolocation Data.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--geolocation-data
* Added: Shortcode: Related Links: Radius Option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/#radius-conditions
* Added: Generate: Content: {keyword:all} transformation to output all Keyword Terms.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#output-all-terms
* Added: Keywords: Delimiters can be ignored within Terms by using quotes.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/#using-the-delimiter-character-within-terms 
* Fix: Keywords: Database error: Field 'columns doesn't have a default value

= 2.3.6 (2019-11-28) =
* Added: Generate: Content: The7 Theme Meta Box Support
* Added: Generate: Content: TheBuilt Theme Page and Post Settings Meta Boxes Support
* Added Shortcodes: Related Links: Reset margin and padding on links to improve Theme compatibility
* Fix: Shortcodes: Don't attempt to load JS if Post Content isn't available

= 2.3.5 (2019-11-21) =
* Added: Settings: General: Enable Revisions on Content Groups. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/general-settings/
* Added: Generate: Content: Include Description when searching Content Groups
* Added: Generate: Content: Choose Sections of Content Group to overwrite.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generate
* Notice: Generate: Content: Overwrite with Preserve Date option is deprecated; use Overwrite Sections to not overwrite existing Page published dates.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generate
* Fix: Licensing: Obscure License Key if valid
* Fix: Settings: Display confirmation notification that settings have saved
* Fix: Settings: Change Page Parent Dropdown Field renamed to Change Page Dropdown Fields, and applied wherever WordPress attempts to list Pages (e.g. Appearance > Customize, Settings > Reading)
* Fix: Generate: Content: Don't show Group Filter Dropdown above WP_List_Table
* Fix: Shortcodes: Don't attempt to load CSS if Post Content isn't available
* Fix: Shortcodes: TinyMCE Modal input styling and sizing for WordPress 5.3 compatibility

= 2.3.4 (2019-11-14) =
* Added: Generate: Content: Enfold / Avia Builder: Display 'Advanced Layout Editor' button when Gutenberg enabled to toggle between Gutenberg and Avia
* Added: Shortcode: OpenStreetMap: Load CSS inline
* Added: Shortcodes: Only load JS and CSS when required
* Added: Licensing: Clear WordPress options cache when updating or deleting license validity information, to prevent aggressive third party caching solutions from storing stale data
* Fix: Spintax: SpinnerChief authentication would fail due to incorrect apikey parameter

= 2.3.3 (2019-10-24) =
* Fix: Shortcode: OpenStreetMap: Honor CSS Prefix change in leaflet.css
* Fix: Shortcode: Related Links: Honor CSS Prefix change in HTML
* Fix: Shortcode: Related Links: Don't attempt to trim multi select inputs (Group), which prevented Insert button working
* Fix: Shortcode: Wikipedia: Don't attempt to trim multi select inputs (Terms, Sections), which prevented Insert button working

= 2.3.2 (2019-10-17) =
* Fix: Licensing: Don't show license expired notice on Plugins screen, for performance
* Fix: Keywords: Import CSV: Attempt to UTF-8 encode strings in CSV files containing mixed UTF-8 and non-UTF-8 content

= 2.3.1 (2019-10-10) =
* Added: Generate: Content: Generate Spintax: Support for ChimpRewriter and SpinnerChief.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/spintax-settings/#service--spinnerchief
* Added: Shortcodes: Autocomplete Keyword Suggestions displayed when typing in supported fields
* Fix: Shortcode: Related Links: Honor Group ID when limiting links by Custom Field Key / Value pairs
* Fix: Unexpected 'return' (T_RETURN) on PHP 5.x.  However, please note minimum supported PHP version of 7.1: https://www.wpzinc.com/documentation/installation-licensing-updates/hosting-requirements/#php-version

= 2.3.0 (2019-10-03) =
* Added: Shortcode: Related Links: Link, Previous and Next Titles support outputting Custom Field values.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/
* Added: Generate: Content: Block Spinning: Support for randomising order of paragraphs within sections.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-spintax/#block-spinning
* Added: Licensing: Show licensing server response on HTTP or server error
* Fix: Shortcode: Yelp: Ensure Radius cannot exceed the maximum supported 20 miles 
* Fix: Generate: Spintax: Support for larger spintax lengths and greater levels of nesting
* Fix: Licensing: Updated endpoint URL
* Fix: Licensing: Use options cache instead of transients to reduce license key and update failures

= 2.2.9 (2019-09-26) =
* Added: Keywords: Keyword Names can include any language
* Added: Generate: Content: Generate Spintax: Support for Spin Rewriter and WordAI.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/spintax-settings/
* Added: Settings: Spintax: Options to not spin capitalized words and define protected words.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/spintax-settings/
* Added: Shortcode: Pexels.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pexels/
* Added: Shortcode: Pixabay.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-pixabay/
* Added: Generate: Content: Featured Image: Specify Title, Caption, Description and Filename when Image Source is Image URL, Pexels or Pixabay. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--featured-image
* Fix: Keywords: Terms: Remove empty newlines
* Fix: Generate: Content: Generate Spintax: Preserve line breaks and paragraphs
* Fix: Shortcode: Media Library: Tabbed UI so fields are not cut off on smaller screens
* Fix: Shortcode: Remove leading and trailing whitespace on any shortcode parameters
* Fix: Shortcode: Related Links: Insert button would fail when no Group (or 'This Group') specified
* Fix: Shortcode: Wikipedia: Improve Table of Contents detection to ensure content is returned
* Fix: Shortcode: Wikipedia: Improve Disambiguation Page detection when use_similar_page is enabled
* Fix: Shortcode: Wikipedia: Iterate through multiple Terms when specified in Generate mode
* Fix: Shortcode: Wikipedia: Support for multiple shortcode instances of the same term and different languages in a single Content Group
* Removed: Shortcode: Unsplash.  Use Pexels or Pixabay Shortcodes above

= 2.2.8 (2019-09-19) =
* Added: Generate: Content: Custom Fields: Option to automatically store the used Keyword(s) and Term(s) on generated Pages as Custom Fields / Post Meta data.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--custom-fields
* Added: Shortcode: Related Links: Limit links by Custom Field Key / Value pairs.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/#custom-fields
* Added: Shortcode: Wikipedia: Support for specifying one or more Terms to use, in order, when finding Wikipedia content. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-wikipedia-content/
* Added: Shortcode: Wikipedia: Option to fetch first similar page when Term could not be found and Wikipedia provides alternate Articles.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-wikipedia-content/
* Added: Shortcode: Wikipedia: Verbose error logging when Wikipedia shortcode fails in Test mode, output on the generated Test Page
* Fix: Shortcode: Related Links: Tabbed UI so fields are not cut off on smaller screens
* Fix: Shortcode: Wikipedia: Return blank content if content could not be fetched
* Removed: CLI: Method and Overwrite override options.  Settings always taken from Group.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/

= 2.2.7 (2019-09-12) =
* Added: Generate: Content: Overwrite: Options to skip or overwrite if a Page exists, whether created by a Page Generator Pro Group or manually in WordPress.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generate
* Added: Generate: Content: Verbose logging on whether Generation created, updated or skipped.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-run/#understanding-the-output-log
* Added: Shortcode: Wikipedia: Options to choose sections to output, maximum number of paragraphs, apply synonms and process spintax.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-shortcodes/#wikipedia-content
* Fix: Shortcode: Wikipedia: Remove footnote references from output text
* Fix: Generate: Content: Only attempt to UTF-8 Post Excerpt when Page Generation fails and the Post Type supports Excerpts
* Fix: Elementor: invalid_page_template error on Generation when overwriting existing generated Pages

= 2.2.6 (2019-08-29) =
* Added: Keywords: Generate Locations: Restrict by Min / Max City Population Option available when using Radius.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Generate: Content: Group ID displayed on Group Lists Table
* Added: Generate: Content: Last Index Generated displayed on Group Lists Table
* Added: Generate: Terms: Group ID displayed on Group Lists Table
* Added: Generate: Terms: Last Index Generated displayed on Group Lists Table
* Added: Shortcodes: Open Street Map.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-shortcodes/#openstreetmap
* Added: Shortcodes: Related Links: Option to specify multiple Group IDs.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/
* Fix: Generate: Content: Convert Post Parent string to sanitized Permalink to ensure Post Parent can be found when using non alpha-numeric characters

= 2.2.5 (2019-08-19) =
* Fix: TinyMCE Editor: Return registered TinyMCE Plugins when not registering Page Generator Pro TinyMCE Plugins

= 2.2.4 (2019-08-15) =
* Added: Shortcodes: Yelp: Radius, Minimum Rating, Language, Price Level and Sort options.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-shortcodes/#yelp-business-listings
* Added: Generate: Terms: Visual Editor for Description
* Fix: Shortcodes: Media Library Image: Adjusted layout to work on smaller screens, so fields are not cut off
* Fix: Shortcodes: Related Links: Adjusted layout to work on smaller screens, so fields are not cut off
* Fix: Generate: Terms: Don't remove HTML tags from Description
* Fix: Generate: Terms: Support for Block Spinning
* Fix: Generate: Terms: Align Action Buttons to the left

= 2.2.3 (2019-08-08) =
* Added: Settings: Google: Option to specify Google Maps API Key for Google Maps Shortcode embeds that are billable by Google (i.e. Street View, Driving Directions).
* Added: Keywords: Generate Locations: City Population option in Output Type
* Added: Keywords: Generate Locations: Restrict by Min / Max City Population.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Generate: Content: Support for using 1 or 2 Keyword Transformations.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-keywords/#apply-multiple-keyword-transformations
* Added: Shortcodes: Related Links: Added Columns option for List of Links.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/
* Added: Import: Import Post Meta from third party Plugins for Content Groups (e.g. Yoast)

= 2.2.2 (2019-07-25) =
* Added: Whitelabelling: Plugin Name, Author and URL on WordPress Admin > Plugins is now whitelabelled.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/whitelabelling-access/
* Added: Shortcodes: Media Library: Operator option to define whether image must contain any or all of the given Title, Alt, Caption and Description value(s)
* Added: Generate: Content: Featured Image: Operator option to define whether image must contain any or all of the given Title, Alt, Caption and Description value(s)
* Fix: Generate: Content: Trim setting values to avoid failures in e.g. Featured Image searches, Overwriting by Title failing etc.
* Fix: Generate: Terms: Trim setting values to avoid failures in e.g. Overwriting by Title failing etc.
* Fix: Generate: Content: Block Spinning: remove blank lines in #s blocks, to avoid possibly selecting a blank sentence during Generation
* Fix: Import: Added support for UTF8 BOM sequenced / encoded JSON exported files

= 2.2.1 (2019-07-18) =
* Added: Keywords: Import CSV: Added support for UTF8 BOM sequenced / encoded CSV files
* Added: Shortcodes: Related Links: Specify Link Title format for each Related Link
* Added: Shortcodes: Related Links: Limit Related Links matching a given slug
* Fix: Shortcodes: Media Library: Honor search settings for Alt Tag, Caption and Description

= 2.2.0 (2019-07-11) =
* Added: Keywords: Generate Locations: Added Street Names and Zipcode Districts for the UK.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Shortcodes: Yelp: Only display the Yelp! logo once, regardless of how many times the shortcode is used in a content section
* Fix: Keywords: Generate Locations through browser would fail when whitelabelling enabled

= 2.1.9 (2019-07-08) =
* Fix: Generate: Content: "A name is required for this term." error when attempting to generate Post Types that have Taxonomies registered to them, and no Terms specified.

= 2.1.8 (2019-07-04) =
* Added: Generate: Content: Overwrite: Skip if Exists: Don't create or update a Page if already generated by the same Group with the same Permalink.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#fields--generate
* Added: Settings: General: Change Page Parent Dropdown to either ID Field or Search Dropdown.  Improves performance on WordPress sites with a large number of Pages.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/general-settings/#change-page-parent-dropdown-field
* Fix: Settings: General: CSS Prefix: Only allow CSS and shortcode compliant characters
* Fix: Keywords: Import CSV: Correct identify screen to avoid loading unused Javascript
* Fix: Generate: Generate through browser would fail when whitelabelling enabled
* Fix: Generate: Terms: Uncaught TypeError: Cannot read property 'category' of undefined

= 2.1.7 (2019-06-27) =
* Added: Shortcodes: Related Links: Page Parent option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/ 
* Added: Access Control: Option to limit Plugin access (requires Agency License).  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/whitelabelling-access/
* Added: Shortcodes: Related Links: Option to display Parent, Previous and/or Next Post / Page Links.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-dynamic-elements-related-links/
* Added: Generate: Content: Separator between Plugin TinyMCE Buttons and WordPress TinyMCE Buttons
* Added: Generate: Content: Standardised TinyMCE Button Icons
* Fix: Generate: Content: When overwrite enabled, only overwrite if an existing Page exists by Slug AND Parent.  Prevents the same page being overwritten every time in a generation routine

= 2.1.6 (2019-06-20) =
* Added: Settings: General: Change Page Parent Dropdown to ID Field.  Improves performance on WordPress sites with a large number of Pages.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/general-settings/#change-page-parent-dropdown-to-id-field
* Fix: Generate Content: Use the Divi Builder / Use Frontend Builder would not work in some instances for newly created Content Groups
* Fix: Settings: Google: Removed Google Maps API key, as usage for embedded maps is free with no limit
* Fix: Generate: Content: Block Spinning: don't insert break / newlines for each sentence in a paragraph

= 2.1.5 (2019-06-13) =
* Added: Generate: Content: Make Theme Page Builder compatibility
* Added: Whitelabelling: Option to whitelabel Plugin (requires Agency License).  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/whitelabelling-access/

= 2.1.4 (2019-06-06) =
* Added: Generate: Content: Display warning when saving a Group, or attempting to generate content from a Group, when the Group isn't saved (prevents other errors such as keyword missing errors)

= 2.1.3 (2019-05-31) =
* Fix: Shortcodes: Related Links: Don't attempt to process shortcode on Generation, resulting in its removal

= 2.1.2 (2019-05-30) =
* Fix: Generate: Content: BeTheme compatibility for 21.1.1+
* Fix: Keywords: Prevent success / error notices displaying twice in Keyword Table list

= 2.1.1 (2019-05-23) =
* Added: Generate: Content: Metaboxes are no longer filtered out or removed, ensuring better third party Theme / Plugin compatibility
* Fix: Generate: Content: Renamed Remove Trackbacks and Pingbacks to Remove Track / Pingbacks, to avoid text overflowing in the UI

= 2.1.0 (2019-05-16) =
* Added: Shortcodes: Yelp: Options to choose whether to display Image, Rating, Categories, Phone Number and/or Address
* Fix: Generate: Content: Scheduled Specific Date with Increment honors the increment

= 2.0.9 (2019-05-09) =
* Added: Generate: Content: Smartcrawl SEO Meta Box Support

= 2.0.8 (2019-05-06) =
* Fix: Don't load Gutenberg scripts when Avada Fusion Builder is used

= 2.0.7 (2019-05-02) =
* Added: Settings: General: Option to Disable Custom Fields Dropdown on Pages, for performance.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/general-settings/
* Added: Settings: General: Option to Limit Depth on Page Parent Dropdown on Pages, for performance.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/general-settings/
* Fix: Generate: Content: Number of Generated Items count now includes scheduled Pages
* Fix: Generate: Content: Trash and Delete Generated Content will Trash / Delete scheduled, draft, private and published Pages

= 2.0.6 (2019-04-25) =
* Added: Shortcode: Unsplash: Option to specify Title and Caption to use
* Fix: Shortcodes: Related Links: Ensure Related Links display when Settings > General > CSS Prefix is defined
* Fix: Generate: Ensure progress bar styles don't override other styles in the WordPress Admin UI

= 2.0.5 (2019-04-18) =
* Added: Shortcode: Media Library: Output Alt Tag option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-shortcodes/#media-library-image
* Fix: Shortcodes: Don't include blank parameters in shortcode output, as they're not needed.
* Fix: Keywords: Require both delimiter and column name if either field is specified.
* Fix: Generate: Content: Prevent PHP warnings displaying when a Keyword is specified with column names, but no delimiter.
* Fix: Generate: Content: Publish: Don't allow Generate to generate Posts in the Content Groups section.
* Fix: Generate: Content: Gutenberg: Don't display Gutenberg's Permalink Panel in the sidebar, as it's not used.

= 2.0.4 (2019-04-11) =
* Added: Settings: General: Option to specify unique CSS Prefix.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/general-settings/
* Added: Shortcode: Google Maps: Map Types for Road Map, Satellite, Directions and Street View.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-shortcodes/
* Fix: Settings: Generate: Corrected Meta Box Title
* Fix: Generate: Terms: Save Settings in Sidebar

= 2.0.3 (2019-04-03) =
* Fix: Keywords: Generate Locations: Include license key in requests for compatibility with location API, preventing errors

= 2.0.2 (2019-03-28) =
* Added: Groups: Content: Keywords can specify any combination of column name, transformation and index. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/#using-keywords--transforming-keywords
* Added: Groups: Content: TinyMCE: Autocomplete Keyword Suggestions displayed when typing
* Added: Groups: Content: Gutenberg Blocks: Autocomplete Keyword Suggestions displayed when typing
* Added: Groups: Split functionality into separate class files for performance across Groups Table, Groups Add/Edit and Groups
* Added: Groups: Terms: Split functionality into separate class files for performance across Groups Table, Groups Add/Edit and Groups
* Added: Groups: Terms: Parent Term and Taxonomy Fields on Add New Taxonomy Group form
* Fix: Groups: Terms: Show error message when attempting to delete generated content from a Term Group that has no generated content

= 2.0.1 (2019-03-21) =
* Added: Generate: Content: Block Spinning.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-spintax/
* Added: Generate: Content: Warning when specifying a static Permalink.
* Added: Generate: Content: Project Supremacy v3 Meta Box Support
* Added: Page Builders: Automatically register Page Generator Pro with supported Page Builders, instead of manually changing settings.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-page-builders-integration/
* Fix: Fatal error: Call to undefined function wp_doing_cron()
* Fix: Generate: Terms: Not all Terms would generate when Parent Term was specified and the Child Term exists as a Parent Term

= 2.0.0 (2019-03-07) =
* Added: Generate: Content: SEOPress and SEOPress Pro Meta Box Support

= 1.9.9 (2019-02-28) =
* Added: Generate: Content: Bulk Actions to Duplicate, Generate via CRON, Trash and Delete Generated Content
* Added: Generate: Content: Added Status Column to Groups Table
* Added: Generate: Content: Lock Group when it is generating content, to prevent editing part way through content generation
* Added: Generate: Content: Generate via WordPress Cron
* Added: Generate: Content: Moved Table Row Actions to respective columns for easier access and improved UI
* Added: Generate: Terms: Bulk Actions to Duplicate, Generate via CRON and Delete Generated Content
* Added: Generate: Terms: Added Status Column to Groups Table
* Added: Generate: Terms: Lock Group when it is generating terms, to prevent editing part way through term generation
* Added: Generate: Terms: Generate via WordPress Cron
* Added: Generate: Terms: Moved Table Row Actions to respective columns for easier access and improved UI
* Added: Generate: WP-CLI: Trash Generated Content Command
* Fix: Generate: Content: Custom Fields: Fix Meta Key / Value Field Alignment
* Fix: Generate: Content: Only display Trash / Delete Generated Content options if Generated Content exists
* Fix: Generate: Content: Fusion Builder 1.8.x not working with WordPress 5.1+
* Fix: Generate: Terms: Only display Trash / Delete Generated Content options if Generated Terms exists
* Fix: Generate: Terms: Copy Term Meta (e.g. Yoast data, ACF data etc) to Generated Terms
* Fix: Generate: WP-CLI: Generation would silently fail on some instances

= 1.9.8 (2019-02-21) =
* Added: Code refactoring for better performance
* Added: Keywords: Generate Locations: Add Exclusions options, to exclude Cities / Counties / Regions from results.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Generate: Terms: Autocomplete Keyword Suggestions displayed when typing in applicable fields that support Keywords
* Fix: Generate: Terms: Don't strip keyword characters, ensuring keywords are saved and replaced correctly
* Fix: Keywords: Generate Phone Area Codes: Javascript errors resulting in Output Type not displaying correctly
* Fix: Removed unused logging from Javascript
* Fix: Installation / Upgrade: PHP error when mu-plugin file failed to copy to mu-plugins folder
* Fix: Keywords: Aligned "Search results for" label correctly when searching for Keywords
* Fix: Content Groups: Aligned "Search results for" label correctly when searching for Content Groups
* Developers: get_instance() calls are deprecated in favour of Page_Generator_Pro()->get_class( 'class_name' ).  WordPress standard deprecated notices will display.

= 1.9.7 (2019-02-14) =
* Added: Settings: Generate: Option to enable Performance Addon.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/settings-generate/
* Added: Generate: Content: Test: Verbose errors displayed on generated test Page / Post.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-run/
* Added: Generate: Content: Excerpt: Only display Excerpt field if the Post Type being generated supports Excerpts
* Added: Generate: Content: Keywords Dropdown: List Column Subsets when a keyword's columns are defined
* Added: Generate: Content: Autocomplete Keyword Suggestions displayed when typing in applicable fields that support Keywords 
* Added: Generate: Content: Use GD Image Library instead of Imagick, if GD is available to WordPress.  Improves Performance and reduces server errors
* Added: Generate: Content: Delete associated Media Library attachments when Delete Generated Content is used
* Added: Shortcodes: Keywords Dropdown to applicable fields
* Added: Shortcode: Related Links: Options to define Author and Taxonomies
* Added: Shortcode: Related Links: Output title attribute on links
* Fix: Generate: Content: Only fetch Group Settings once, to improve performance
* Fix: Generate: Content: Only fetch Keywords once, to improve performance
* Fix: Generate: Content: Process Shortcodes after all keyword replacements have been completed
* Fix: Shortcode: Wikipedia: Better verbose error message when failing to fetch Wikipedia content
* Fix: Shortcode: Related Links: Only show publish and draft Content Groups in the dropdown
* Fix: Export: Export Keywords
* Fix: Export: Export Generate: Terms
* Fix: Export: Don't include auto-draft Content Groups

= 1.9.6 (2019-02-08) =
* Fix: Generate: Content: Replace keywords with column / term subsets defined

= 1.9.5 (2019-02-07) =
* Added: Generate: Content: Optimized performance for generation
* Added: Generate: Terms: Optimized performance for generation
* Added: Generate: Terms: Test, Generate and Delete actions from table view
* Added: Generate: Terms: Ensure actions behave in the same way as Generate: Content, with confirmation alerts
* Fix: Only load JS when required for performance
* Fix: Activation: Fix ‘Specified key was too long; max key length is 767 bytes’ error on Phone Area Code Table creation for MySQL 5.6 and lower 
* Fix: Generate: Content: Alignment of Deselect All button on Taxonomies
* Fix: Generate: Content: Undefined index: group_id Javascript errors
* Fix: Generate: Content: Don't show Trash and Delete Options in table if no content has been generated by the Group
* Fix: Generate: Terms: Don't require Parent Term field
* Fix: Elementor: Improve Generation Performance by not processing shortcodes in the Post Content, as Post Content is not used by Elementor.
* Fix: Elementor: Prevent duplicate processing of the same shortcodes for performance (prevents duplicate Unsplash image imports).
* Fix: Keywords: Generate Locations: Ensure multiple Regions, Counties and/or Cities are all honored as restrictions, not just the last entered Region / County / City

= 1.9.4 (2019-02-02) =
* Fix: Activation: Fix ‘Specified key was too long; max key length is 767 bytes’ error on Keyword Table creation for MySQL 5.6 and lower 

= 1.9.3 (2019-01-31) =
* Added: Developers: Docblock comments on all Plugin specific filters and actions.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/developers/
* Added: Generate: Content: Clear Elementor Cache once Generation has completed, to ensure compilation of CSS etc.
* Fix: Generate: Content: Elementor: Override Page Template filter resulting in non-Group Post/Page Templates not displaying
* Fix: Generate: Content: Page Builders: Process Page Generator Pro Shortcodes on Test / Generate for all Page Builders
* Fix: Generate: Content: Ensure Trash link allows deletion of Group
* Fix: Generate: Content: Author field search failing
* Fix: Licensing and Updates: Improved mechanism for WP-CLI support
* Fix: Minified all CSS and JS for performance

= 1.9.2 (2019-01-24) =
* Added: Shortcodes: Unsplash: Alt Tag option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-shortcodes/
* Fix: Activation: Don't specify ENGINE on CREATE TABLE syntax
* Fix: Multisite: Network Activation: Ensure database tables are automatically created on all existing sites
* Fix: Multisite: Network Activation: Ensure database tables are automatically created on new sites created after Network Activation of Plugin
* Fix: Multisite: Site Activation: Ensure database tables are created
* Fix: Keywords: Allow Keywords to be sorted ascending and descending when clicking Keywords column in table

= 1.9.1 (2019-01-17) =
* Added: Generate: Content: Option to Trash or Delete Generated Content
* Added: Success and Error Notices can be dismissed
* Fix: Keywords: Avoid HTTP API 200 error when creating a keyword with no Keyword or Terms specified
* Fix: Keywords: Validate that column names exist when a delimiter exists
* Fix: Keywords: Validate that terms contain the matching delimiter when a delimiter exists
* Fix: Keywords: Validate that the number of column names specified matches the number of deliniated items in a term
* Fix: Keywords: Ensure that Sorting Keywords in the table doesn't re-trigger a duplicate or delete event
* Fix: Keywords: Generate Phone Area Codes: Populate Delimiter and Column fields
* Fix: Generate: Content: PHP warnings when duplicating Content Group

= 1.9.0 (2019-01-10) =
* Added: Generate: Content: Added Internal Description Field
* Fix: Generate: Content: Force priority of Actions in Sidebar to display top and bottom of meta boxes list
* Fix: Keywords: Import CSV: PHP warning on using continue instead of break

= 1.8.9 (2019-01-03) =
* Fix: Keywords: Generate Locations: Allow multiple Counties and Cities of the same name, in different areas, to display in search results for selection
* Fix: UI Enhancements for mobile compatibility

= 1.8.8 (2018-12-28) =
* Fix: ACF and Divi compatibility

= 1.8.7 (2018-12-27) =
* Added: Generate: Content: Salient Page Meta Box Support
* Fix: Generate: Content: Action Buttons CSS to ensure buttons aren't cut off
* Fix: Generate: Content: Enfold / Avia Builder: Ensure Plugin Shortcodes are rendered and stored in post meta
* Fix: Generate: Content: Table: Ensure that Test Generation generates content from selected Group ID
* Fix: Generate: Content: Table: Ensure that Delete Generated Content deletes content from selected Group ID

= 1.8.6 (2018-12-21) =
* Fix: Keywords: Generate Locations: Modal not dismissing on completion
* Fix: Related Links Shortcode: Force Group ID if not specified to ensure results display
* Fix: Related Links Shortcode: Force Post Type if not specified to ensure results display

= 1.8.5 (2018-12-20) =
* Fix: Removed all select2 references, as select2 is no longer used 

= 1.8.4 (2018-12-13) =
* Added: Generate Content: Test, Generate and Delete Generated Content Actions in Sidebar for Gutenberg Editor
* Fix: Generate Content: Gutenberg: Save all Settings 
* Fix: Keywords: Generate Locations: Prefetch Restrict by Counties and Regions for the selected Country, so the user can search and/or select from the dropdown list
* Fix: Keywords: Generate Locations: Some missing data for Restrict by Counties and Regions
* Fix: Keywords: Generate Locations: Use Restrict by Counties and Regions when searching for Restrict by City
* Fix: Keywords: Generate Locations: Report errors on screen if searching Restrictions fails
* Fix: Shortcodes: Google Maps: Remove sensor=false parameter, as it's no longer needed

= 1.8.3 (2018-11-29) =
* Added: Keywords: Generate Locations: Ability to fetch large datasets of ZIP Codes, Cities etc asynchronously. See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Settings: Generate Locations: Option to specify default Radius, in miles.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-locations-settings/
* Fix: Activation: Fatal error on unlicensed new installations
* Fix: Keywords: Validate the columns field, ensuring no spaces are used
* Fix: Generate: Content: Correctly replace keywords when using PHP versions older than 5.5.x (please upgrade to PHP 7 - PHP 5.x is end of life January 1st 2019: http://php.net/supported-versions.php)
* Fix: Generate: Content: Author field now uses selectize asynchronous search for better performance on sites with a large number of WordPress Users
* Fix: Generate: Terms: Correctly replace keywords when using PHP versions older than 5.5.x (please upgrade to PHP 7 - PHP 5.x is end of life January 1st 2019: http://php.net/supported-versions.php)
* Removed: Keywords: Generate Nearby Cities.  Replaced by Generate Locations.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/

= 1.8.2 (2018-11-22) =
* Added: Generate Terms: Add option to specify parent Term on hierarchical (e.g. Category) based Taxonomies
* Fix: Keywords: Generate Locations: Restrict by City / County / Region results populate when searching
* Fix: Keywords: Generate Locations: Improved performance / response time for searching Restrictions
* Fix: Generate Content: Hide Actions Meta Box compatible when using Gutenberg 4.4+
* Fix: Generate Content: Hide Attributes Meta Box if no Attributes apply to the generated Post Type
* Fix: Generate Terms: keyword_error when using Keywords, resulting in no generated Terms

= 1.8.1 (2018-11-15) =
* Added: Shortcode: Unsplash: Image Orientation option
* Fix: Shortcode: Unsplash: Image could not always be fetched
* Fix: Shortcode: Media Library Image: Image could not always be fetched
* Fix: Keywords: Term Indicies (e.g. {city:2}) were not working

= 1.8.0 (2018-11-08) =
* Added: Settings: Generate Locations Tab: Define default choices for Area and Country.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-locations-settings/
* Added: Generate Content: Gutenberg Compatibility
* Added: Generate Content: Test option for each Content Group in the list of Content Groups.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/
* Added: Generate Content: Delete Generated Content option for each Content Group in the list of Content Groups.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/
* Added: Generate Content: Confirmation Dialogs for actions in the list of Content Groups.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/
* Added: Generate Content: Apply Synonyms to Content automatically.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/
* Added: Generate Content: Featured Image: Option to choose Media Library Image at random, with optional filters for Title, Caption, Alt, Description and ID constraints.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/
* Fix: Generate Content: Confirmation Dialogs localized for translation
* Fix: Keywords: Typo on example usage of Keyword Term Subsets

= 1.7.9 (2018-11-01) =
* Added: Generate Content: Visual Editor: Automatically Generate Spintax from Selected Text.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-using-spintax/
* Added: Shortcode: Unsplash: Add image size option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-shortcodes/
* Added: Shortcode: Media Library Image.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-shortcodes/
* Fix: Exclude Content Groups from Yoast SEO Sitemaps, regardless of Yoast settings
* Fix: Generate Content: Don't strip Keyword Term Subset brackets in Permalink field
* Fix: Shortcode: Wikipedia: Better content detection, ignoring empty paragraphs

= 1.7.8 (2018-10-25) =
* Added: Keywords: Delimiter and Column options, to allow Term Subset data to be accessed (such as the City Name from a full location).  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords/
* Added: Keywords: Generate Nearby Cities: Renamed to Generate Locations.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Keywords: Generate Locations: Replaced Geonames and Google Geocoding APIs with Georocket.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Keywords: Generate Locations: Restrict Results by Radius or Area (City, County or Region).  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Keywords: Generate Locations: Maximum Radius restriction removed.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-generate-locations/
* Added: Keywords: Use native wpdb class insert(), update() and delete() functions when creating, updating and deleting Keywords
* Added: Generate Content: Unsplash Featured Image Option
* Added: Generate Content: Unsplash Shortcode
* Fix: Generate Content: Don't process shortcodes when saving a Group (improves load times and performance)

= 1.7.7 (2018-09-13) =
* Fix: Generate: Content: Initialize array in a PHP 5+ compatible manner
* Fix: WP-CLI: Honor resume_index option
* Fix: Google Maps: Ensure custom height is honored and not overridden by CSS
* Removed: 500px support (500px no longer grant access to their API to fetch photos. Please note this is outside of our control: https://support.500px.com/hc/en-us/articles/360002435653-API- )

= 1.7.6 (2018-08-30) =
* Added: Generate: Content: Option to force specific Keyword Term when using a Keyword, using e.g. {city:2} to always output the second Term.
* Added: Generate: Term: Option to force specific Keyword Term when using a Keyword, using e.g. {city:2} to always output the second Term.
* Added: WP-CLI: Delete Generated Content (see Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/)
* Added: WP-CLI: Delete Generated Terms (see Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/)

= 1.7.5 (2018-08-23) =
* Fix: Generate: Content: Improved error message in Test and Generate mode when the total number of possible keyword term combinations exceeds PHP's floating point limit.
* Fix: Generate: Terms: Improved error message in Test and Generate mode when the total number of possible keyword term combinations exceeds PHP's floating point limit.

= 1.7.4 (2018-08-18) =
* Fix: Generate: Content: Scheduled functionality missing on some upgrades from 1.7.2 to 1.7.3

= 1.7.3 (2018-08-16) =
* Added: Keywords: Import CSV option.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/keywords-import-csv/

= 1.7.2 (2018-08-09) =
* Fix: Generate: Content: Ignore _wp_page_template if supplied in Post Meta; this ensures the Content Group's Page Template is always honored.

= 1.7.1 (2018-07-26) =
* Added: Keywords: Automatically generate Terms based on Keyword if no Terms are supplied
* Added: Generate: Content: Confirmation dialog when deleting Generated Content
* Added: Generate: Content: Honor Number of Posts settings for Random generating (noting a value must be specified, otherwise 10 Posts generated.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/)

= 1.7.0 (2018-07-19) =
* Fix: Yelp: Serve logo and link over HTTPS
* Fix: Elementor: Spin and replace keywords (note: keyword tags MUST be complete, and NOT broken up by HTML.  See Docs: https://www.wpzinc.com/documentation/page-generator-pro/generate-content/)

= 1.6.9 (2018-07-12) =
* Fix: Elementor: Using existing Templates will be honored in generated content

= 1.6.8 (2018-06-28) =
* Added: Generate: Content: Support for Live Composer
* Fix: Improved licensing mechanism

= 1.6.7 (2018-06-08) =
* Added: Generate: Content: Yoast SEO: Prevent Yoast SEO stripping curly braces from Canonical URL
* Fix: Yelp: Use correct data when reporting errors from Yelp
* Fix: Activation: Better method of deactivating free version of the plugin if it's still active

= 1.6.6 (2018-05-10) =
* Fix: Licensing: Improved performance
* Fix: Activation: Deactivate free version of the plugin if it's still active
* Fix: Generate: Wikipedia Shortcode: Better importing of Wikipedia content

= 1.6.5 (2018-04-26) =
* Added: Generate Content: Support for using Taxonomies as Keywords (e.g. {taxonomy_category})

= 1.6.4 (2018-04-12) =
* Fix: Generate Content: Divi Settings: Ensure that correct Divi Settings can be customised for Posts and Pages
* Fix: Generate Content: Elementor: Display Page Template in frontend preview

= 1.6.3 (2018-04-02) =
* Added: Generate: Test: Honor Resume Index Setting, so a specific starting index can be tested
* Added: Generate: Output: Display Keywords + Term Replacements used in each Page, Post + Term Generation (both wp-admin and wp-cli)

= 1.6.2 (2018-03-22) =
* Added: show_in_rest = false for Content Groups, until we're happy that the Gutenberg editor is stable in WordPress 
* Fix: Shortcodes: 500px: Don't attempt to choose an image index outside of the resultset
* Fix: Shortcodes: YouTube: Don't attempt to choose a video index outside of the resultset
* Fix: Call wp_enqueue_media() on Plugin screens, because Plugins which register Meta Boxes and Yoast SEO wrongly assume that there is always a Visual Editor and Featured Image on a Post Type
* Fix: Generate Content: Permalink: Allow keyword transformations

= 1.6.1 (2018-03-13) =
* Added: Generate Terms
* Fix: Keywords: Prevent spaces in Keywords
* Fix: Generate: Prevent spaces in Permalink
* Fix: Code formatting

= 1.6.0 (2018-03-02) =
* Fix: Class 'Page_Generator_Pro_Geo' not found in includes/admin/install.php on line 58

= 1.5.9 (2018-03-01) =
* Added: Generate Nearby Cities / ZIP Codes: Output format can be any one or more of City, County and/or Zip Code, in any order
* Added: Generate: Generation: Overwrite: Added option to overwrite existing Pages, preserving their existing Published date
* Added: Generate Phone Area Codes
* Added: Shortcode: Filters to all shortcode outputs
* Added: Shortcode: Related Links
* Fix: Generate: 500px: Errors importing 500px images into Media Library

= 1.5.8 (2018-02-01) =
* Added: Generate: Support for X and Pro Themes by ThemeCo
* Fix: Generate: Attributes: Only display Template option if the Post Type has registered templates available
* Fix: Generate: Prevent Preview / View of Group on frontend, which results in errors (use 'Test' method instead)

= 1.5.7 (2018-01-18) =
* Fix: Generate: Use date_i18n() instead of date() to ensure that published Posts honor WordPress' locale

= 1.5.6 (2018-01-10) =
* Added: Generate: Support for Avia Layout Builder (Enfold Theme)

= 1.5.5 (2017-12-14) =
* Added: Generate: WPBakery Visual Composer Backend Editor Support

= 1.5.4 (2017-11-22) =
* Fix: 404 errors on generated Pages when Page Parent was previous set and then removed

= 1.5.3 (2017-11-09) =
* Added: Generate: Support for Page Slug and Keyword in Attributes > Parent
* Added: Generate: Native support for AIOSEO Pack, Yoast SEO and Yoast SEO Premium (see Documentation: https://www.wpzinc.com/documentation/page-generator-pro/generate-seo-integration/)
* Added: Generate: WP-CLI Arguments (see Documentation: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/)
* Added: Generate: WP-CLI: Support for multiple Group IDs (see Documentation: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/)
* Added: Generate: WP-CLI: page-generator-pro-groups-list command (see Documentation: https://www.wpzinc.com/documentation/page-generator-pro/generate-wp-cli/)

= 1.5.2 (2017-10-02) =
* Added: Settings: GeoNames Username option
* Added: Post Type Template Support (WordPress 4.7+)
* Added: Generate: Support for large keyword term combinations in All mode (e.g. 100 million+ pages). Requires PHP 5.5+

= 1.5.1 (2017-09-25) =
* Added: Improved UI
* Added: Generate Nearby Cities / ZIP Codes: Ability to generate list of ZIP Codes, with formatting options (City, County, ZIP Code)
* Fix: Uncaught TypeError: Illegal constructor in admin-min.js for clipboard.js functionality

= 1.5.0 (2017-08-10) =
* Fix: Generate: Wikipedia: Detect mb_convert_encoding() function before attempting to parse Wikipedia HTML
* Fix: Google Maps: Use HTTPS and return more accurate latitude and longitude for Cities

= 1.4.9 (2017-07-10) =
* Added: Generate: Overwrite Existing Pages (generated by this Plugin)
* Added: Generate: Featured Image: Alt Tag (for Image URLs and 500px)
* Added: Generate: Custom Fields: Move option
* Fix: Generate: Parent: Added description explaining how to determine the Parent Page ID
* Fix: Keywords: Prevent slashes from displaying / added on double quotation marks

= 1.4.8 (2017-07-05) =
* Fix: Settings: Google: Click here links go to valid Documentation URL
* Fix: Generate: Wikipedia: Improved content building method to avoid blank results from Wikipedia in some cases

= 1.4.7 (2017-06-22) =
* Added: Settings: Google: Google Maps API Disable JS Library option, for installations where another Plugin or Theme might load Google Map's API library already

= 1.4.6 (2017-05-28) =
* Fix: Use utf8_encode on Title, Excerpt and Content if wp_insert_post() fails on generation / testing

= 1.4.5 (2017-05-26) =
* Added: Yelp API v3 Support (no need to define keys or tokens)

= 1.4.4 (2017-04-26) =
* Added: Generate: ACF Support
* Added: All Group Post Metadata has keyword replacement and spintax operations performed on them before being copied to the generated Page/Post/CPT.
* Fix: Improved Generate performance by not duplicating spintax process

= 1.4.3 (2017-04-20) =
* Fix: UTF-8 encoding on Wikipedia content to avoid corrupt character output

= 1.4.2 (2017-04-13) =
* Fix: Undefined property Page_Generator_Pro_PostType::$post_type
* Fix: Muffin Builder: Replace keywords in SEO fields

= 1.4.1 (2017-03-16) =
* Added: Generate: Divi Page and Post Layouts are now available in Page Generator Pro when using Divi > Load from Library

= 1.4.0 (2017-02-27) =
* Fix: Only display Review Helper for Super Admin and Admin

= 1.3.9 (2017-02-20) =
* Added: Review Helper to check if the user needs help
* Fix: Ensure first keyword within spintax at the very start of the content (or a Page Builder module) is replaced with a keyword
* Updated: Dashboard and Licensing Submodules

= 1.3.8 (2017-02-14) =
* Added: Generate: Spintax all fields, including Page Builders
* Added: Post Type: Use variable for Post Type Name for better abstraction
* Fix: Generate: Don't attempt to test for permitted meta boxes if none exist
* Fix: Generate: Check Custom Fields are set before running checks on them
* Fix: Use Plugin Name variable for better abstraction
* Fix: Improved Installation and Upgrade routines

= 1.3.7 (2017-02-09) =
* Added: Generate: Support for Beaver Builder
* Added: Generate: Support for Visual Composer
* Added: Page Builders: Moved integration code and associated functions to frontend facing class for better compatibility
* Fix: Yelp: Fallback to cURL with User-Agent string, if wp_remote_get() fails

= 1.3.6 (2017-01-30) =
* Fix: Changed branding from WP Cube to WP Zinc
* Fix: Updated licensing endpoint to reflect brand change

= 1.3.5 (2017-01-23) =
* Fix: Generate: Parent Page is now an ID field, to prevent memory errors when trying to use wp_dropdown_pages() to list 3,000+ Pages
* Fix: Generate: Improve performance when fetching Number of Generated Pages for a given group, to prevent memory errors

= 1.3.4 (2016-12-30) =
* Fix: Generate: Page = Draft when using Test mode
* Fix: Generate: Copy Divi Post Meta to generated Page(s) to honor Divi settings

= 1.3.3 (2016-12-14) =
* Fix: Generate: Attributes > Parent displays the chosen / saved Parent Page
* Fix: Generate: Spintax: More accurate process for returning correct inline CSS, JSON or general text in curly braces when running spintax routine, rather than stripping it entirely
* Fix: Generate: Prevent "Do you want to leave this site" message when using Action buttons at the bottom of the screen

= 1.3.2 (2016-12-09) =
* Fix: Generate: Handle Google latitude/longitude lookup errors better, instead of returning a 500 server error
* Fix: Generate: Spintax: Return inline CSS or general text in curly braces when running spintax routine, rather than stripping it entirely

= 1.3.1 (2016-12-05) =
* Added: Generate: Support for BeTheme
* Added: Generate: Support for Muffin Page Builder
* Added: Generate: Google Maps: Zoom Option
* Fix: Generate > Nearby Cities: Country dropdown option preserved on form submit error
* Fix: Generate: Improved search/replace method for Custom Fields

= 1.3.0 (2016-11-15) =
* Fix: When upgrading from < 1.2.1 to 1.2.3+, don't try to create a Groups table - just migrate the single Group settings into the new Groups CPT.
* Fix: Only set a Post Name (slug) if one is defined in the Group settings.

= 1.2.9 (2016-11-14) =
* Fix: Undefined variable $notices error on groups.php

= 1.2.8 (2016-11-03) =
* Added: Generate: Support for Avada Theme
* Added: Generate: Support for Fusion Builder

= 1.2.7 (2016-10-24) =
* Added: Generate: Support for Divi 3.0+ Theme
* Added: Generate: Support for Divi Builder Plugin

= 1.2.6 (2016-10-07) =
* Added: Generate: Option to stop Generation part way through the process.
* Added: Generate: Generation will now stop if a server side error is encountered when generating a Page.

= 1.2.5 (2016-10-01) =
* Fix: Keywords: Generate Nearby Cities: Use cURL instead of wp_remote_get() so that the User-Agent header is set correctly (wp_remote_get() would be better, however it results in a 403 Error from the API)
* Fix: Generate: Generating Pages with no Parent would result in Pages not truly Publishing until Updated.
* Fix: CLI: Call to undefined method Page_Generator_Pro_Groups::get_by_id()

= 1.2.4 (2016-09-27) =
* Added: Generate: Hierarchical Taxonomies can have new Taxonomy Term(s) specified, instead of just choosing existing Taxonomy Term(s).
* Fix: Generate: Google Maps, Wikipedia, Yelp, 500px and YouTube buttons reinstated to Groups content editor.
* Fix: Generate: Don't throw a 500 error when an undefined {keyword} is used in a Group.
* Fix: Import/Export: Added support to import JSON configurations generated in 1.2.2 and older.

= 1.2.3 (2016-09-22) =
* Added: Generate: Support for SiteOrigin Page Builder
* Added: Generate: Delete Generated Pages / Posts / CPTs (only for content generated since version 1.2.3)
* Added: Generate: Custom Fields: Meta values use textarea to support multiline text, formatting and HTML / JS markup
* Added: Generate: Duplicate Generation Set
* Fix: Generate: Honor 'Allow Comments' setting
* Fix: Generate: Honor 'Allow trackbacks and pingbacks' setting
* Fix: Generate: Allow Author selection when 'Rotate' is not enabled

= 1.2.2 (2016-07-12) =
* Added: Enable database debugging output if WP_DEBUG enabled
* Fix: Fatal error on installation for Page_Generator_Pro_Groups

= 1.2.1 (2016-07-06) =
* Added: Create, edit, run, delete, import and export multiple generation sets.
* Added: Shortcode: Wikipedia: Support for multiple languages
* Added: Shortcode: Google Maps API Key option (for users who exceed API limits, you can now specify your own Google Maps API key)
* Added: Shortcode: YouTube API Key option (for users who exceed API limits, you can now specify your own Youtube Data API key)
* Added: Generate: Show Page Parent option if Custom Post Type supports parent items
* Added: Generate: Reset Button to deselect taxonomy term(s)
* Added: Generate: Search field on taxonomies
* Added: Generate: Save / Test / Generate options at top and bottom of screen
* Added: Spintax support on custom / meta field values
* Fix: Generate: Improved TinyMCE / Visual Editor shortcode options for Google Maps, Wikipedia, Yelp, 500px and YouTube

= 1.2.0 (2016-06-24) =
* Added: Shortcode: YouTube Video
* Fix: Keyword search / replace on Page Generation is now case insensitive (e.g. {city} and {City} will both be replaced with a term)
* Fix: Out of memory errors when using case variations of a keyword (e.g. {city} and {City})
* Fix: Keyword replacements now fully work in Custom Fields and Taxonomy Terms

= 1.1.9 (2016-06-20) =
* Fix: Use same fallback method on map shortcode as Keywords > Generate Nearby Cities, to ensure lat/lng is always returned where possible

= 1.1.8 (2016-06-16) =
* Added: Keywords: Uppercase flag e.g. {keyword:uppercase_all}
* Added: Keywords: Lowercase flag e.g. {keyword:lowercase_all}
* Added: Keywords: Capitalise first letter flag e.g. {keyword:uppercase_first_character}
* Added: Keywords: Capitalise first letter of each word flag e.g. {keyword:uppercase_first_character_words}
* Added: Keywords: Capitalise first letter of each word flag e.g. {keyword:url}
* Added: Featured Image: 500px option
* Added: Shortcode: 500px Image
* Fix: Generate Nearby Cities: OVER_QUERY_LIMIT will now automatically trigger using OpenStreetMap to fetch latitude/longitude as a fallback

= 1.1.7 (2016-06-09) =
* Added: Spintax support on Tags
* Added: Featured Image option
* Added: Generate Nearby Cities: Include original city in results option
* Added: Generate Nearby Cities: Country is now a dropdown field to avoid ambiguity in guessing a country's code
* Fix: Generate Nearby Cities: Don't allow a radius of greater than 100 miles to be specified, as the API will not support this
* Fix: Generate Nearby Cities: More meaningful error messages are returned when something goes wrong
* Fix: Increased size of keyword terms database field from TEXT to MEDIUMTEXT, to support larger keyword imports (~ 16 million characters / 16MB ) 

= 1.1.6 =
* Added: Keywords can be included in spins
* Added: Option to choose specific publish / scheduled date
* Added: Option to choose random publish date with min/max date parameters
* Added: Contextual help to Generate screen
* Fix: Only parse Page Generator Pro shortcodes. Provides compatibility with page builders and other plugins / themes that use shortcodes for content
* Fix: Keep spinning content, even when the final spin has been reached and there are more pages to generate
* Fix: Spins would fail if certain characters existed
* Fix: Licensing mechanism works correctly with W3 Total Cache and memcache

= 1.1.5 =
* Added: Page Generation Methods (All, Sequential and Random)
* Fix: Replace spaces in slug with hyphens

= 1.1.4 =
* Fix: Don't display a division by zero error when keyword does not exist.
* Fix: Changed Yelp oAuth class names to avoid conflicts with other plugins.

= 1.1.3 =
* Added: Singleton Instances for better performance
* Fix: Use do_shortcode() instead of apply_filters( 'the_content' ) so we only parse necessary shortcodes in the content

= 1.1.2 =
* Fix: License check takes place outside of admin if required
* Fix: Activation on new multisite activation

= 1.1.1 =
* Fix: Activation routines for installation
* Fix: Yelp button not displaying on Visual Editor

= 1.1.0 =
* Added: Plugin structure changes and code optimisation for better performance
* Added: Google Maps Shortcode: Zoom attribute
* Added: Wikipedia Shortcode: Number of sections attribute
* Added: Generate: Removed 999 Limit when generating Pages
* Added: Generate: Page Parent Option
* Added: Generate: Schedule Option

= 1.0.9 =
* Fix: Faster Page Generation routine
* Fix: Warnings when not rotating authors 

= 1.0.8 =
* Fix: Fatal error when an error occurs during keyword saving.

= 1.0.7 =
* Added: Generate: Custom Fields (Meta Key/Value Pairs)
* Added: Generate: Progress Bar + Log with AJAX / JS support to prevent timeouts and support larger (~ 1000+) page generations
* Added: Minified JS and CSS
* Fix: Yelp OAuth errors

= 1.0.6 =
* Fix: Use $wpdb->prepare() in place of mysql_real_escape_string()
* Fix: Multisite Activation

= 1.0.5 =
* Added: Support for HTML elements in keyword data

= 1.0.4 =
* Added: Import + Export Settings, allowing users to copy settings to other plugin installations
* Added: Support Panel

= 1.0.3 =
* Fix: Transients for license key validation

= 1.0.2 =
* Fix: Force license key check method to beat aggressive server caching
* Added: Support menu with debug information

= 1.0.1 =
* Added translation support and .pot file

= 1.0 =
* First release.

== Upgrade Notice ==
