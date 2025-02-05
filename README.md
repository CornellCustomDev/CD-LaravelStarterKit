# Laravel Starter Kit

A Cornell University CIT Custom Development starter kit and library for Laravel.

## Goals
- Reduce time to build Laravel apps
- Increase consistency in configuration, third-party packages, and architecture
- Increase code visibility, code quality, and team collaboration
- Continuously improve code and practices
- Lower barriers for support and reduce support time

## Usage

The Starter Kit can be used [as a starter kit for a new site](#as-a-starter-kit-for-a-new-site) or [as a library for an existing site](#as-a-library-for-an-existing-site).

### As a Starter Kit for a New Site

Used as a starter kit, this package deploys the cwd_framework_lite infrastructure and standard configuration files. The
steps below get from a fresh Laravel install to a working site.

1. Follow [standard Laravel project creation](https://laravel.com/docs/9.x/installation#your-first-laravel-project), namely
   ```shell
   composer create-project --no-dev laravel/laravel your-app-name
   ```
   This is done with the `--no-dev` option, because we will be committing the vendor dir and don't need that extra baggage.
   >**NOTE**: If you have GitHub CLI installed, you can immediately add this to GitHub as a repo with the following commands (be sure to replace the "your-app-name" references with your project info):
   > ```shell
   > cd your-app-name
   > git init
   > git add . && git commit -m "Initial commit"
   > git branch -m main
   > gh repo create --private CU-CommunityApps/CD-your-app-name
   > git push --set-upstream origin main
   > ```
   
2. Composer require the LaravelStarterKit
   ```shell
   composer require --update-no-dev cornell-custom-dev/laravel-starter-kit
   ```
   Similar to the `create-project` option, the `--update-no-dev` keeps us from adding baggage to the vendor dir.

3. Install the Starter Kit
   ```shell
   php artisan starterkit:install
   ```
   The `starterkit:install` command prompts for a set of install options:
   - publish configured [project files](project) to the base directory and update the `composer.json` file to match
   - publish HTML/CSS/JS theme assets from [cwd_framework_lite](https://github.com/CU-CommunityApps/cwd_framework_lite) 
   - publish a set of [view components](https://laravel.com/docs/10.x/blade#layouts-using-components) that can be used to begin a layout 
   - publish [example blade files](resources/views/examples) to see usage of the layout
   >    **NOTE**: The "project files" option updates `.gitignore` so that the vendor directory is no longer excluded. The next commit will be large because it includes everything in the vendor directory.
   >   ```shell
   >    git add . && git commit -m "Starter Kit install"
   >    git push
   >   ```

4. Testing the site<br>
   You can confirm the site is working with Lando, since the Starter Kit install process adds a `.lando.yml` file.
   ```shell
   lando start
   ```
   Then visit https://your-app-name.lndo.site and you should see the default Laravel page. To see the Laravel Starter Kit example page, edit `/resources/views/welcome.blade.php` to be:
    ```blade
    @include('cd-index')
    ```

### As a Library for an Existing Site

For an existing Laravel site, this package can be composer-required to provide the library of classes and optionally install some components.

1. Composer require the LaravelStarterKit
    ```shell
    composer require cornell-custom-dev/laravel-starter-kit
    ```
   
2. Install the Starter Kit
    ```shell
    php artisan starterkit:install
    ```
   Note: When using as a library or updating an installation, you will not want to install the project files. You may still want to install the theme assets, view components, and possibly example files. Be aware that these will overwrite existing files.

## Libraries

The libraries included in the Starter Kit are documented in their respective README files:

- [Contact/PhoneNumber](src/Contact/README.md): A library for parsing and formatting a phone number.
- [CUAuth](src/CUAuth/README.md): A middleware for authorizing Laravel users, mostly for Apache mod_shib authentication.

## Deploying a site
Once a Media3 site has been created, you have confirmed you can reach the default site via a web browser, and you have access to the site login by command line, the code can be deployed.

You will likely need to map the `php` command to the correct version by editing `~/.bashrc` to include this alias (for this to take effect, run `source ~/.bashrc` or just log in again):
```shell
# User specific aliases and functions
alias php="/usr/local/bin/ea-php81"
```

Since `www/your-site/public` will already exist, you need to do a little moving things around to git clone your site repo from GitHub:
```shell
cd www/your-site
mv public public.default
git clone --bare https://github.com/CU-CommunityApps/CD-your-app-name.git .git
git init && git checkout main
```

At this point you can configure the `www/your-site/.env` file:
```shell
cp .env.example .env
php artisan key:generate
nano .env
```

Be sure to set your `APP_*` values to appropriate values, based on whether it is production:
```dotenv
APP_NAME="Your Site"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-site.edu
```
```dotenv
APP_NAME="Your Site - TEST"
APP_ENV=testing
APP_DEBUG=true
APP_URL=https://test.your-site.edu
```
If you visit your site now, you should see the Laravel site working.

## Contributing

Anyone on the Custom Development team should be welcome and able to contribute. See [CONTRIBUTING](CONTRIBUTING.md) for details on how be involved and provide quality contributions.
