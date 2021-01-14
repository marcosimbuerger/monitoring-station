# Monitoring Station 📡

The monitoring station collects the data from the Monitoring Satellites and displays it.

## Monitoring Satellites
* [Monitoring Satellite for Symfony (Symfony Bundle)](https://github.com/marcosimbuerger/symfony-monitoring-satellite-bundle)
* [Monitoring Satellite for Drupal](https://www.drupal.org/project/monitoring_satellite)
* [Monitoring Satellite for Contao](https://github.com/marcosimbuerger/contao-monitoring-satellite)
* Magento (soon)
* Shopware (soon)

## Installation
```bash
$ composer create-project marcosimbuerger/monitoring-station .
```

## Configuration

### Create .env.local
Copy `.env.local.example` to `.env.local`.

#### Create your backend login 
Add your backend username and password to `.env.local`.

Use `bin/console security:encode-password` to generate the password hash.

#### Environment
Define the environment with the APP_ENV variable. (`APP_ENV=dev` / `APP_ENV=prod`)

Do not forget to set it to `prod` before using the Monitoring Station in production.

### Cache lifetime
Define the cache lifetime of the fetched website data in `app_config.yaml`.

### Add the Monitoring Satellites
Copy the `config/example.monitoring_satellite.websites.yaml` file to `config/monitoring_satellite.websites.yaml` and add your Monitoring Satellites endpoints.

## Login
Open root in your browser. Insert your backend login credentials to log in.

## Test
You can call `/example/monitoring-satellite/v1/get` to get an example of the result of a Monitoring Satellite. Basic auth: `foo` | `bar`

## Commands
Available console commands.

```
website-data:fetch          Fetches the website data.
website-data:clear-cache    Clears the website data cache.
website-data:prune-cache    Prunes the website data cache.
```

## License
This project is released under the MIT license. See the included [LICENSE](LICENSE) file for more information.
