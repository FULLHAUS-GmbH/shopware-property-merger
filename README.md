# FULLHAUS Property Merger Plugin for Shopware 6

## Overview

The **FULLHAUS Property Merger** plugin for Shopware 6 simplifies the process of merging property fields across your product catalog. Whether you’re handling property groups, options, or configurators, this tool is designed to streamline and centralize your workflows.

**Current Status:** ⚠️  
This plugin is under active development and includes a known issue with UUID conversion that may block certain merge commands. Please consult the logs for more details when encountering issues. Contributions or suggestions are welcome to help resolve this!

## Features

- Merge property fields with ease.
- Extended command list to display **property IDs** in addition to **group IDs**.
- Supports the merging of:
  - Property Groups
  - Property Options
  - Product Configurators
  - Product Properties

## Requirements

- **Shopware Version**: 6.5.* or higher
- PHP 7.4 or 8.0+
- Composer for dependency management

## Installation

### Via Composer

1. Add the repository to your project using Composer:
   ```bash
   composer require fullhaus/property-merger
   
2. Update the autoload configuration:
      ```bash
     composer dump-autoload
      
3. Install the plugin in Shopware:
      ```bash
      bin/console plugin:refresh
      bin/console plugin:install --activate FhPropertyMerger

## Usage

The plugin includes console commands to perform various operations. Use the following commands to list available options and execute merges:

### List Property IDs
   ```bash
      bin/console fullhaus:property-list
```

### Merge Property Options
   ```bash
      bin/console fullhaus:property-merge -s <SOURCE_ID> -d <DESTINATION_ID>
```

## Known Issues
- UUID Conversion Issue: The merge command may fail in certain cases due to unresolved UUID conversion errors. Logs have been added to assist in debugging. If you encounter issues, please share your findings through a GitHub issue or pull request.
- Compatibility Testing: The plugin has been tested primarily with Shopware 6.5.x. Please report any incompatibility with other versions.
