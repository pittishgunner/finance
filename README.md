# Alex M Finance

## Setup

**Download Composer dependencies**

Make sure you have [Composer installed](https://getcomposer.org/download/)
and then run:

```
composer install
```
**Database Setup**

Next, build the database, execute the migrations and load the fixtures with:

```
# "symfony console" is equivalent to "bin/console"
# but its aware of your database container
symfony console doctrine:database:create --if-not-exists
symfony console doctrine:schema:update --force
symfony console doctrine:fixtures:load 

symfony console doctrine:schema:drop --force --full-database && symfony console --no-interaction doctrine:migrations:migrate && symfony console doctrine:fixtures:load -n


symfony server:start --allow-http -d
```

The `symfony` binary can be downloaded from https://symfony.com/download.
Make sure to start your own
database server and update the `DATABASE_URL` environment variable in
`.env` or `.env.local` before running the commands above.

**Webpack Encore Assets**

This app uses Webpack Encore for the CSS, JS and image files.
To build the Webpack Encore assets, make sure you have
[Yarn](https://yarnpkg.com/lang/en/) installed and then run:

```
yarn install
yarn encore dev --watch
```

**Start the Symfony web server**

You can use Nginx or Apache, but Symfony's local web server
works even better.

To install the Symfony local web server, follow
"Downloading the Symfony client" instructions found
here: https://symfony.com/download - you only need to do this
once on your system.

Then, to start the web server, open a terminal, move into the
project, and run:

```
symfony serve
```

(If this is your first time using this command, you may see an
error that you need to run `symfony server:ca:install` first).

Now check out the site at `https://localhost:8000`

Have fun!
