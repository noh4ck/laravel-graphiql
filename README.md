# GraphiQL for Laravel

This package provides an easy way to include the [GraphiQL UI](https://github.com/graphql/graphiql) within your laravel project.

Current included version: `0.11.11` 

## Installation

Add this to your `composer.json` file:

```json
"noh4ck/graphiql": "@dev"
```

or run `composer require "noh4ck/graphiql:@dev"`

If you have Laravel 5.5 or above, automatic package discovery will register the provider automatically.
If not, open your `config/app.php` and add this line in the `providers` section:

```php
Graphiql\GraphiqlServiceProvider::class
```

Then run `artisan graphiql:publish` to publish the assets and the configuration file.

## Usage

Per default, the UI is reachable at `/graphql-ui`

It points to a GraphQL endpoint at the same host under `/graphql`

For further configuration, look at `config/graphiql.php` 
