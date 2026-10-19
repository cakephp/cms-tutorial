# CakePHP CMS Tutorial

The completed CMS tutorial application created during
https://book.cakephp.org/5/en/tutorials-and-examples/cms/installation.html

## Requirements

* PHP 8.2 or higher (tested on 8.2, 8.3, 8.4 and 8.5), with the `intl`, `mbstring` and `pdo_sqlite` extensions.
* [Composer](https://getcomposer.org/doc/00-intro.md).

## Installation

1. Download [Composer](https://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
2. Download this source code. `git clone https://github.com/cakephp/cms-tutorial.git`
3. Install dependencies with composer

```bash
cd cms-tutorial
composer install
```

Composer also runs the installer, which creates `config/app_local.php` from `config/app_local.example.php`
and sets the `Security.salt` value.

## Configuration

You can use the default configuration, using a sqlite3 file based database. In that case, no configuration
is required.
If you want to use your own database, update the `'Datasources'` configuration in `config/app.php`.
You'll also need to create a database and run the SQL located in the tutorial.

* Update the `'Datasources'` configuration by:
  * editing `config/app.php` or `config/app_local.php` for your local database or
  * edit `config/bootstrap.php` to enable use of a `.env` file (uncomment the section that loads the `.env` file) (and provide the `config/.env` file.
* Create a database if needed.
* Run `bin/cake migrations migrate` to create the database tables.


## Running the project

```bash
bin/cake server
```

Then point your browser to http://localhost:8765

You will see the list of articles. Adding or editing articles requires a logged in user, so you will be
redirected to the `/users/login` page, where you can also sign up. The CMS Tutorial uses Authentication and
Authorization to identify the users and to decide what they can do.

## Production

Set the `APP_FULL_BASE_URL` environment variable (for example `APP_FULL_BASE_URL=https://example.com`)
or `App.fullBaseUrl` in `config/app_local.php`. With `debug` off, the `HostHeaderMiddleware` rejects the
requests whose `Host` header does not match it, to prevent Host header injection.

## Permissions

* Anyone can read the articles and the tags, and sign up as a user.
* Logged in users can add articles, and edit or delete their own articles.
* Logged in users can add and edit tags, but nobody can delete them.
* Users can only see, edit or delete their own user.

## Running the tests and the code checks

```bash
composer test      # phpunit
composer cs-check  # phpcs, CakePHP coding standard
composer stan      # phpstan
```
