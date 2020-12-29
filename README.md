# Monitoring Station 📡

The monitoring station collects the data from the Monitoring Satellites and displays it.

## Monitoring Satellites
* [Monitoring Satellite for Symfony (Symfony Bundle)](https://github.com/marcosimbuerger/symfony-monitoring-satellite-bundle)
* [Monitoring Satellite for Contao (Contao Bundle)](https://github.com/marcosimbuerger/contao-monitoring-satellite)
* Drupal (soon)
* Shopware (soon)

## Installation
```bash
$ composer create-project marcosimbuerger/monitoring-station .
```

## Configuration

### Create your backend login
Copy `.env.local.example` to `.env.local` and add your backend username and password. Use `bin/console security:encode-password` to generate the password hash.

### Add the Monitoring Satellites
Copy the `config/example.monitoring_satellite.websites.yaml` file to `config/monitoring_satellite.websites.yaml` and add your Monitoring Satellites endpoints.

## Login
Open root in your browser. Insert your backend login credentials to log in.

## Test
You can call `/example/monitoring-satellite/v1/get` to get an example of the result of a Monitoring Satellite. Basic auth: `foo` | `bar`

## License
This project is released under the MIT license. See the included [LICENSE](LICENSE) file for more information.
