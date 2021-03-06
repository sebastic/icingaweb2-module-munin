# Munin module for Icinga Web 2

#### Table of Contents

1. [About](#about)
2. [License](#license)
3. [Requirements](#requirements)
4. [Installation](#installation)
5. [Configuration](#configuration)

## About

This module integrates [Munin](https://github.com/munin-monitoring/munin) into [Icinga Web 2](https://github.com/Icinga/icingaweb2).

## License

Munin and this Icinga Web 2 module are licensed under the terms of the GNU General Public License Version 2, you will find a copy of this license in the LICENSE file included in the source package.

The module adopts the same license as Munin, having copied some CSS from Munin.

## Requirements

This module glues Munin and Icinga Web 2 together. Both of them are required
to be installed and configured:

* [Icinga Web 2](https://github.com/Icinga/icingaweb2)
* [Munin](https://github.com/munin-monitoring/munin)

## Installation

Extract this module to your Icinga Web 2 modules directory in the `munin` directory.

```
mkdir -p /usr/local/share/icingaweb2/modules
cd /usr/local/share/icingaweb2/modules
git clone https://github.com/sebastic/icingaweb2-module-munin.git munin
```

Ensure that the `module_path` in the `global` section of the [Icinga Web 2 `config.ini`](https://icinga.com/docs/icingaweb2/latest/doc/03-Configuration/#configuration-general) includes the path for custom modules, e.g.:

```
module_path = "/usr/share/icingaweb2/modules:/usr/local/share/icingaweb2/modules"
```

### Enable Icinga Web 2 module

Enable the module in the Icinga Web 2 frontend in `Configuration -> Modules -> munin -> State`.
You can also enable the module by using the `icingacli` command:

```
icingacli module enable munin
```

## Configuration

### Munin Configuration

The base URL for Munin (e.g. `/munin`) must be accessible on the webserver.
When using `graph_strategy cgi` the base URL for `munin-cgi-graph` (e.g. `/munin-cgi/munin-cgi-graph`) must also be accessible.

### Module Configuration

Install the `config.ini` file in the module configuration directory.

```
cd /usr/local/share/icingaweb2/modules/munin
mkdir -p /etc/icingaweb2/modules/munin
cp etc/config.ini /etc/icingaweb2/modules/munin/
```

Adjust the content of the configuration file to match your Munin setup.

When not using cron to generate the graphs, make sure to change `graph_strategy` to `cgi`.

### Custom Pages

In addition to the standard pages provided by Munin, this module supports adding custom pages.

Custom pages are configured in a JSON configuration file.

Have a look at [icingaweb2-module-munin_custom-pages.json](etc/icingaweb2-module-munin_custom-pages.json) for an example.

To enable custom pages, add the `custom_pages` section to module `config.ini` and specify the path to the configuration file.

```
[custom_pages]
config_file = "/usr/local/etc/icingaweb2-module-munin_custom-pages.json"
```

