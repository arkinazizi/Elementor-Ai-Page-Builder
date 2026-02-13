# Elementor AI Page Builder

A WordPress plugin that generates Elementor pages with AI-written content based on selected templates. This plugin seamlessly integrates with Elementor Page Builder and uses AI providers (Google Gemini or OpenAI ChatGPT) to automatically generate contextual content for your pages.

## 🎯 Overview

Elementor AI Page Builder automates the process of creating WordPress pages by:
- Using existing Elementor templates as a foundation
- Automatically generating AI-powered content based on your page titles
- Replacing template placeholder text with contextually relevant content
- Supporting batch page generation
- Optionally adding generated pages to WordPress menus

## ✨ Features

- **AI-Powered Content Generation**: Automatically rewrites template content to match your page context
- **Multiple AI Provider Support**: 
  - Google Gemini (2.0, 2.5, and 3.0 models)
  - OpenAI ChatGPT (GPT-3.5-turbo)
- **Batch Page Creation**: Generate multiple pages at once from a list of titles
- **Template-Based**: Use any Elementor template as a starting point
- **Menu Integration**: Automatically add generated pages to WordPress menus with support for parent/child items
- **Toggle AI Content**: Option to create pages with or without AI content generation
- **Rate Limiting**: Built-in request delay to avoid API quota issues
- **User-Friendly Interface**: Clean admin dashboard for easy configuration

## 📋 Requirements

- WordPress 5.0 or higher
- Elementor Page Builder plugin installed and activated
- PHP 7.0 or higher
- Active API key from either:
  - Google Gemini API (get it from [Google AI Studio](https://makersuite.google.com/app/apikey))
  - OpenAI API (get it from [OpenAI Platform](https://platform.openai.com/api-keys))

## 🚀 Installation

1. Download the plugin files or clone this repository
2. Upload the `elementor-ai-page-builder` folder to `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to **AI Page Builder** in the WordPress admin menu
5. Configure your AI provider and API key in the Settings section

## ⚙️ Configuration

### 1. Access Settings

Go to **WordPress Admin Dashboard** → **AI Page Builder**

### 2. Configure AI Provider

Choose between:
- **OpenAI (ChatGPT)**: Enter your OpenAI API key
- **Google Gemini**: Enter your Gemini API key and select a model

#### Available Gemini Models:
- **Gemini 3 Pro Preview** (Latest preview)
- **Gemini 2.5 Flash** (Balanced performance)
- **Gemini 2.5 Flash-Lite** (Fastest)
- **Gemini 2.5 Pro** (High performance)
- **Gemini 2.0 Flash** (Stable, recommended for most use cases)
- **Gemini 2.0 Flash-Lite** (Fast and efficient)

### 3. Set Request Delay

Configure the delay between API requests to avoid quota limits:
- **Gemini Free Tier**: Recommended 4+ seconds (15 requests/minute limit)
- **Paid Tiers**: Can be reduced based on your quota

### 4. Toggle AI Content

Enable or disable AI content generation:
- **Enabled**: AI will rewrite template content to match your page context
- **Disabled**: Pages will be created using the template's original content

## 📖 Usage

### Generating Pages

1. **Enter Page Titles**: Add one or more page titles (one per line) in the text area
2. **Select Template**: Choose an Elementor template to use as the base
3. **Menu Options** (Optional):
   - Check "Add to Menu" to automatically add pages to a WordPress menu
   - Select target menu
   - Choose parent menu item for sub-pages (optional)
4. **Click "Generate Pages"**: The plugin will create all pages sequentially

### How It Works

1. Plugin creates a new WordPress page with your specified title
2. Retrieves the Elementor data from the selected template
3. Processes each Elementor widget:
   - **Heading widgets**: Rewrites headings contextually
   - **Text Editor widgets**: Generates relevant paragraph content
   - **Button widgets**: Updates button text appropriately
4. Sends content to the AI provider with contextual prompts
5. Replaces template content with AI-generated text
6. Saves the new page with updated Elementor data
7. Optionally adds the page to your selected menu

### Example Use Case

**Scenario**: You need to create 10 product pages using the same template

1. Enter product names (one per line):
   ```
   Premium Coffee Beans
   Organic Green Tea
   Artisan Chocolate
   ...
   ```
2. Select your "Product Template"
3. The plugin generates 10 unique pages with product-specific content

## 🏗️ Plugin Architecture

### Core Components

- **`elementor-ai-page-builder.php`**: Main plugin file, handles initialization and AJAX requests
- **`includes/class-admin-page.php`**: Admin interface with settings and page generation UI
- **`includes/class-content-generator.php`**: Core logic for page creation and AI content generation
- **`includes/interfaces/interface-ai-provider.php`**: AI provider interface for extensibility
- **`includes/providers/class-provider-openai.php`**: OpenAI/ChatGPT implementation
- **`includes/providers/class-provider-gemini.php`**: Google Gemini implementation

### Supported Elementor Widgets

Currently processes:
- Heading widgets (`heading`)
- Text Editor widgets (`text-editor`)
- Button widgets (`button`)

Additional widgets can be easily added by extending the `process_element()` method.

## 🔧 Advanced Configuration

### Custom AI Prompts

The plugin uses carefully crafted prompts to ensure clean, professional output. You can modify prompts in:
- `includes/providers/class-provider-gemini.php` (line 30-37)
- `includes/providers/class-provider-openai.php` (line 29)

### Extending AI Providers

To add a new AI provider:

1. Create a new class in `includes/providers/`
2. Implement the `EAPB_AI_Provider` interface
3. Add the provider to `class-content-generator.php` initialization

## ⚠️ Troubleshooting

### "Quota Exceeded" Errors

**Solution**: Increase the "API Request Delay" in settings
- Gemini Free Tier: Use 4+ seconds
- Check your API dashboard for rate limits

### Pages Created Without AI Content

**Possible Causes**:
- AI content generation is disabled in settings
- Invalid API key
- API quota exceeded

**Solution**: Check the API key and enable AI content generation in settings

### Template Has No Content

**Solution**: Ensure the selected Elementor template has actual widgets with text content

## 📝 License

This plugin is created by Arkin Azizi. Feel free to use and modify for your projects.

## 👨‍💻 Author

**Arkin Azizi**  
Website: [https://arkin.bio/](https://arkin.bio/)

## 🤝 Support

For issues, questions, or feature requests, please contact the plugin author or submit an issue on the project repository.

## 🔄 Version History

**Version 1.4** (Current)
- Multiple AI provider support (OpenAI & Gemini)
- Batch page generation
- Menu integration with parent/child support
- AI content toggle option
- Rate limiting configuration
- Multiple Gemini model support

---

**Made with ❤️ for the WordPress and Elementor community**
