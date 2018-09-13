# Dokan Dev Tools

Developer tools for Dokan plugin

## Dependency

This plugin requires [wc-smooth-generator](https://github.com/woocommerce/wc-smooth-generator) plugin to be activated to generate products.

## Installation

* Clone the repository inside `/wp-content/plugins/`
* cd into folder `cd dokan-dev-tools` and run `composer install`

## Available CLI Commands

### Vendors
Generate Dokan Vendors
```
wp dokan vendor generate --count=<no_of_vendors>
```

Delete all vendors
```
wp dokan vendor delete
```

### Products
Generate products and randomly distributes to Dokan Vendors
```
wp dokan product generate --count=<no_of_vendors>
```

Delete all products
```
wp dokan product delete
```

### Modules
Activate a Dokan module
```
wp dokan module activate <module_name>
```

Dectivate a Dokan module
```
wp dokan module deactivate <module_name>
```

Toggles a module's activation state
```
wp dokan module toggle <module_name>
```

Gets a list of Dokan modules
```
wp dokan module list
```

### Show available commands
```
wp dokan cli commands 
```
